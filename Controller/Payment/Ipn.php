<?php
/**
 * Copyright © Amazon.com, Inc. or its affiliates. All Rights Reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the "License").
 * You may not use this file except in compliance with the License.
 * A copy of the License is located at
 *
 *  http://aws.amazon.com/apache2.0
 *
 * or in the "license" file accompanying this file. This file is distributed
 * on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either
 * express or implied. See the License for the specific language governing
 * permissions and limitations under the License.
 */
namespace Amazon\Pay\Controller\Payment;

use Amazon\Pay\Api\Data\AsyncInterface;
use Amazon\Pay\Model\Async;
use Magento\Framework\App\ObjectManager;
use Aws\Sns\Message;
use Aws\Sns\MessageValidator;
use Magento\Framework\Data\Collection;

/**
 * Class Ipn
 *
 * IPN endpoint for Amazon Simple Notification Service
 * @link https://docs.aws.amazon.com/sns/latest/dg/sns-http-https-endpoint-as-subscriber.html
 */
class Ipn extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Amazon\Pay\Model\AmazonConfig
     */
    private $amazonConfig;

    /**
     * @var \Amazon\Pay\Model\AsyncManagement\ChargeFactory
     */
    private $chargeFactory;

    /**
     * @var \Amazon\Pay\Model\AsyncManagement\RefundFactory
     */
    private $refundFactory;

    /**
     * @var \Amazon\Pay\Logger\AsyncIpnLogger
     */
    private $ipnLogger;

    /**
     * @var \Amazon\Pay\Model\ResourceModel\Async\CollectionFactory
     */
    private $asyncCollectionFactory;

    /**
     * Ipn constructor.
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Amazon\Pay\Model\AmazonConfig $amazonConfig
     * @param \Amazon\Pay\Model\AsyncManagement\ChargeFactory $chargeFactory
     * @param \Amazon\Pay\Model\AsyncManagement\RefundFactory $refundFactory
     * @param \Amazon\Pay\Logger\AsyncIpnLogger $ipnLogger
     * @param \Amazon\Pay\Model\ResourceModel\Async\CollectionFactory $asyncCollectionFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Amazon\Pay\Model\AmazonConfig $amazonConfig,
        \Amazon\Pay\Model\AsyncManagement\ChargeFactory $chargeFactory,
        \Amazon\Pay\Model\AsyncManagement\RefundFactory $refundFactory,
        \Amazon\Pay\Logger\AsyncIpnLogger $ipnLogger,
        \Amazon\Pay\Model\ResourceModel\Async\CollectionFactory $asyncCollectionFactory
    ) {
        // Bypass Magento's CsrfValidator (which rejects POST) and use Amazon SNS Message Validator instead
        $context->getRequest()->setMethod('PUT');
        parent::__construct($context);

        $this->amazonConfig = $amazonConfig;
        $this->chargeFactory = $chargeFactory;
        $this->refundFactory = $refundFactory;
        $this->ipnLogger = $ipnLogger;
        $this->asyncCollectionFactory = $asyncCollectionFactory;
    }

    /**
     * Handle incoming IPN messages
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     * @throws \Exception
     */
    public function execute()
    {
        if (!$this->amazonConfig->isEnabled()) {
            return;
        }

        try {
            if ($this->amazonConfig->isLoggingEnabled()) {
                // phpcs:ignore Magento2.Functions.DiscouragedFunction
                $this->ipnLogger->info(print_r($this->getRequest()->getHeaders()->toArray(), 1));
                $this->ipnLogger->info($this->getRequest()->getContent());
            }

            // Amazon SNS Message Validator
            $snsMessage = Message::fromRawPostData();
            $validator  = new MessageValidator();

            // Message Validator checks SigningCertURL, SignatureVersion, and Signature
            if ($validator->isValid($snsMessage)) {
                $message = json_decode($snsMessage['Message'], true);
                $asyncComplete = false;

                // Process message
                if (isset($message['ObjectType'])) {
                    switch ($message['ObjectType']) {
                        case 'CHARGE':
                            $asyncComplete = $this->chargeFactory->create()->processStateChange($message['ObjectId']);
                            break;
                        case 'REFUND':
                            $asyncComplete = $this->refundFactory->create()->processRefund($message['ObjectId']);
                            break;
                    }
                }

                if ($asyncComplete) {
                    $this->completePending($message['ObjectId']);
                }
            } else {
                $this->ipnLogger->warning('Invalid SNS Message');
            }

        } catch (\Exception $e) {
            $this->ipnLogger->error($e);
            throw $e;
        }
    }

    /**
     * Complete successful async pending action
     *
     * @param string $asyncId
     */
    protected function completePending($asyncId)
    {
        $collection = $this->asyncCollectionFactory
            ->create()
            ->addFilter(AsyncInterface::PENDING_ID, $asyncId)
            ->setPageSize(1);

        foreach ($collection as $async) {
            $async->setIsPending(false)->save();
        }
    }
}
