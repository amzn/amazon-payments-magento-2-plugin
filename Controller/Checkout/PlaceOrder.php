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

use Amazon\Pay\Model\CheckoutSessionManagement;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\ObjectManager;
use Amazon\Pay\Logger\ExceptionLogger;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Message\ManagerInterface;

class PlaceOrder implements HttpPostActionInterface
{
    /**
     * @var CheckoutSessionManagement
     */
    private $amazonCheckoutSessionManagement;

    /**
     * @var JsonFactory
     */
    private $jsonFactory;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @var ExceptionLogger
     */
    private $exceptionLogger;

    /**
     * CompleteCheckout constructor.
     * @param CheckoutSessionManagement $checkoutSessionManagement
     * @param JsonFactory $jsonFactory
     * @param ExceptionLogger|null $exceptionLogger
     */
    public function __construct(
        CheckoutSessionManagement $checkoutSessionManagement,
        JsonFactory               $jsonFactory,
        ManagerInterface          $messageManager,
        ExceptionLogger           $exceptionLogger = null
    )
    {
        $this->amazonCheckoutSessionManagement = $checkoutSessionManagement;
        $this->exceptionLogger = $exceptionLogger ?: ObjectManager::getInstance()->get(ExceptionLogger::class);
        $this->messageManager = $messageManager;
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
