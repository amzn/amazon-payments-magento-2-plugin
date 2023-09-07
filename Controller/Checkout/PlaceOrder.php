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
namespace Amazon\Pay\Controller\Checkout;

use Magento\Framework\App\ObjectManager;
use Amazon\Pay\Logger\ExceptionLogger;
use Magento\Framework\Controller\Result\JsonFactory;

class PlaceOrder extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Amazon\Pay\Model\CheckoutSessionManagement
     */
    private $amazonCheckoutSessionManagement;

    /**
     * @var ExceptionLogger
     */
    private $exceptionLogger;

    /**
     * @var JsonFactory
     */
    private $jsonFactory;

    /**
     * CompleteCheckout constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Amazon\Pay\Model\CheckoutSessionManagement $checkoutSessionManagement
     * @param \Magento\Framework\Controller\Result\JsonFactory $jsonFactory
     * @param ExceptionLogger|null $exceptionLogger
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Amazon\Pay\Model\CheckoutSessionManagement $checkoutSessionManagement,
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
        ExceptionLogger $exceptionLogger = null
    ) {
        parent::__construct($context);
        $this->amazonCheckoutSessionManagement = $checkoutSessionManagement;
        $this->exceptionLogger = $exceptionLogger ?: ObjectManager::getInstance()->get(ExceptionLogger::class);
        $this->jsonFactory = $jsonFactory;
    }

    /*
     * @inheritdoc
     */
    public function execute()
    {
        try {
            // Bypass cache check in \Magento\PageCache\Model\DepersonalizeChecker
            $this->getRequest()->setParams(['ajax' => 1]);
            $amazonCheckoutSessionId = $this->getRequest()->getParam('amazonCheckoutSessionId');

            $result = $this->amazonCheckoutSessionManagement->placeOrder($amazonCheckoutSessionId);
            if (!$result['success']) {
                $this->messageManager->addErrorMessage($result['message']);
                return $result;
            }
        } catch (\Exception $e) {
            $this->exceptionLogger->logException($e);
            $this->messageManager->addErrorMessage($e->getMessage());
        }
        $jsonResult = $this->jsonFactory->create();
        return $jsonResult->setData($result);
    }

}
