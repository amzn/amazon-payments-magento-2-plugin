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
namespace Amazon\PayV2\Controller\Payment;

use Magento\Framework\App\ObjectManager;
use Aws\Sns\Message;
use Aws\Sns\MessageValidator;

/**
 * Class Ipn
 *
 * IPN endpoint for Amazon Simple Notification Service
 * @link https://docs.aws.amazon.com/sns/latest/dg/sns-http-https-endpoint-as-subscriber.html
 */
class Ipn extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Amazon\PayV2\Model\AmazonConfig
     */
    private $amazonConfig;

    /**
     * @var \Amazon\PayV2\Model\AsyncManagement\ChargeFactory
     */
    private $chargeFactory;

    /**
     * @var \Amazon\PayV2\Model\AsyncManagement\RefundFactory
     */
    private $refundFactory;

    /**
     * @var \Amazon\PayV2\Logger\AsyncIpnLogger
     */
    private $ipnLogger;

    /**
     * Ipn constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Amazon\PayV2\Model\AmazonConfig $amazonConfig
     * @param \Amazon\PayV2\Model\AsyncManagement\ChargeFactory $chargeFactory
     * @param \Amazon\PayV2\Model\AsyncManagement\RefundFactory $refundFactory
     * @param \Amazon\PayV2\Logger\AsyncIpnLogger $ipnLogger
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Amazon\PayV2\Model\AmazonConfig $amazonConfig,
        \Amazon\PayV2\Model\AsyncManagement\ChargeFactory $chargeFactory,
        \Amazon\PayV2\Model\AsyncManagement\RefundFactory $refundFactory,
        \Amazon\PayV2\Logger\AsyncIpnLogger $ipnLogger
    ) {
        // Bypass Magento's CsrfValidator (which rejects POST) and use Amazon SNS Message Validator instead
        $context->getRequest()->setMethod('PUT');
        parent::__construct($context);

        $this->amazonConfig = $amazonConfig;
        $this->chargeFactory = $chargeFactory;
        $this->refundFactory = $refundFactory;
        $this->ipnLogger = $ipnLogger;
    }

    public function execute()
    {
        if (!$this->amazonConfig->isEnabled()) {
            return;
        }

        try {
            if ($this->amazonConfig->isLoggingEnabled()) {
                $this->ipnLogger->info(print_r($this->getRequest()->getHeaders()->toArray(), 1));
                $this->ipnLogger->info($this->getRequest()->getContent());
            }

            // Amazon SNS Message Validator
            $snsMessage = Message::fromRawPostData();
            $validator  = new MessageValidator();

            // Message Validator checks SigningCertURL, SignatureVersion, and Signature
            if ($validator->isValid($snsMessage)) {
                $message = json_decode($snsMessage['Message'], true);

                // Process message
                if (isset($message['ObjectType'])) {
                    switch ($message['ObjectType']) {
                        case 'CHARGE':
                            $this->chargeFactory->create()->processStateChange($message['ObjectId']);
                            break;
                        case 'REFUND':
                            $this->refundFactory->create()->processRefund($message['ObjectId']);
                            break;
                    }
                }
            } else {
                $this->ipnLogger->warning('Invalid SNS Message');
            }

        } catch (\Exception $e) {
            $this->ipnLogger->error($e);
            throw $e;
        }
    }
}
