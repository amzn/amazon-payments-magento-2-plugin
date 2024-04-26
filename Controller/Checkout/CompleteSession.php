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
use Magento\Quote\Api\CartManagementInterface;
use Amazon\Pay\Logger\ExceptionLogger;
use Magento\Framework\App\PageCache\Version;

class CompleteSession extends \Magento\Framework\App\Action\Action
{
    protected const GENERIC_COMPLETE_CHECKOUT_ERROR_MESSAGE = 'Unable to complete Amazon Pay checkout.';

    /**
     * @var \Amazon\Pay\Model\CheckoutSessionManagement
     */
    private $amazonCheckoutSessionManagement;

    /**
     * @var \Amazon\Pay\Model\AmazonConfig
     */
    private $amazonConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ExceptionLogger
     */
    private $exceptionLogger;

    /**
     * @var \Magento\Framework\Stdlib\CookieManagerInterface
     */
    private $cookieManager;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory
     */
    private $cookieMetadataFactory;

    /**
     * CompleteSession constructor
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Amazon\Pay\Model\CheckoutSessionManagement $checkoutSessionManagement
     * @param \Amazon\Pay\Model\AmazonConfig $amazonConfig
     * @param \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager
     * @param \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param ExceptionLogger|null $exceptionLogger
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Amazon\Pay\Model\CheckoutSessionManagement $checkoutSessionManagement,
        \Amazon\Pay\Model\AmazonConfig $amazonConfig,
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
        \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        ExceptionLogger $exceptionLogger = null
    ) {
        parent::__construct($context);
        $this->amazonCheckoutSessionManagement = $checkoutSessionManagement;
        $this->amazonConfig = $amazonConfig;
        $this->exceptionLogger = $exceptionLogger ?: ObjectManager::getInstance()->get(ExceptionLogger::class);
        $this->cookieManager = $cookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->storeManager = $storeManager;
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        $scope = $this->storeManager->getStore()->getId();
        try {
            // Bypass cache check in \Magento\PageCache\Model\DepersonalizeChecker
            $this->getRequest()->setParams(['ajax' => 1]);
            $amazonCheckoutSessionId = $this->getRequest()->getParam('amazonCheckoutSessionId');
            $result = $this->amazonCheckoutSessionManagement->completeCheckoutSession($amazonCheckoutSessionId);
            if (!$result['success']) {
                $this->messageManager->addErrorMessage($result['message']);

                return $this->_redirect('checkout/cart', ['_scope' => $scope]);
            } elseif (!$result['order_id']) {
                throw new \Magento\Framework\Exception\NotFoundException(__('Something went wrong. Choose another ' .
                    'payment method for checkout and try again.'));
            }
            $this->updateVersionCookie();
            $successUrl = $this->amazonConfig->getCheckoutResultUrlPath();
            return $this->_redirect($successUrl, [
                '_scope' => $scope,
            ]);
        } catch (\Exception $e) {
            $this->exceptionLogger->logException($e);
            $this->messageManager->addErrorMessage(self::GENERIC_COMPLETE_CHECKOUT_ERROR_MESSAGE);
        }

        return $this->_redirect('checkout/cart', [
            '_scope' => $scope,
        ]);
    }

    /**
     * Generate unique version identifier
     *
     * @return string
     */
    protected function generateValue()
    {
        return hash('sha256', rand() . time());
    }

    /**
     * Update version cookie to clear cart and checkout data on success page
     *
     * @return void
     */
    protected function updateVersionCookie()
    {
        $publicCookieMetadata = $this->cookieMetadataFactory->createPublicCookieMetadata()
            ->setDuration(Version::COOKIE_PERIOD)
            ->setPath('/')
            ->setSecure($this->getRequest()->isSecure())
            ->setHttpOnly(false);
        $this->cookieManager->setPublicCookie(Version::COOKIE_NAME, $this->generateValue(), $publicCookieMetadata);
    }
}
