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
use Magento\Framework\App\RequestInterface;
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
     * @var RequestInterface
     */
    protected $request;

    /**
     * PlaceOrder constructor.
     *
     * @param CheckoutSessionManagement $checkoutSessionManagement
     * @param JsonFactory $jsonFactory
     * @param ManagerInterface $messageManager
     * @param RequestInterface $request
     * @param ExceptionLogger|null $exceptionLogger
     */
    public function __construct(
        CheckoutSessionManagement $checkoutSessionManagement,
        JsonFactory               $jsonFactory,
        ManagerInterface          $messageManager,
        RequestInterface          $request,
        ExceptionLogger           $exceptionLogger = null
    ) {
        $this->amazonCheckoutSessionManagement = $checkoutSessionManagement;
        $this->exceptionLogger = $exceptionLogger ?: ObjectManager::getInstance()->get(ExceptionLogger::class);
        $this->messageManager = $messageManager;
        $this->request = $request;
        $this->jsonFactory = $jsonFactory;
    }

    /**
     * Execute PlaceOrder Controller
     *
     * @inheirtdoc
     */
    public function execute()
    {
        $result = ['success' => false];
        try {
            // Bypass cache check in \Magento\PageCache\Model\DepersonalizeChecker
            $this->request->setParams(['ajax' => 1]);
            $amazonCheckoutSessionId = $this->request->getParam('amazonCheckoutSessionId');

            $result = $this->amazonCheckoutSessionManagement->placeOrder($amazonCheckoutSessionId);

            if ($result['success']) {
                // for orders placed before payment authorization (Express checkout)
                $this->amazonCheckoutSessionManagement->setOrderPendingPaymentReview($result['order_id'] ?? null);
            } else {
                $this->messageManager->addErrorMessage($result['responseText']);
            }

        } catch (\Exception $e) {
            $this->exceptionLogger->logException($e);
            $this->messageManager->addErrorMessage($e->getMessage());
        }
        $jsonResult = $this->jsonFactory->create();
        return $jsonResult->setData($result);
    }
}
