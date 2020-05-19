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
namespace Amazon\PayV2\Controller\Checkout;

use Magento\Framework\App\ObjectManager;
use Magento\Quote\Api\CartManagementInterface;
use Amazon\PayV2\Logger\ExceptionLogger;
use Magento\Framework\App\PageCache\Version;

/**
 * Class CompleteCheckout
 *
 * @package Amazon\PayV2\Controller\Payment
 */
class CompleteSession extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Amazon\PayV2\CustomerData\CheckoutSession
     */
    private $amazonCheckoutSession;

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
     * CompleteCheckout constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Amazon\PayV2\CustomerData\CheckoutSession $amazonCheckoutSession
     * @param \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager
     * @param \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory
     * @param ExceptionLogger|null $exceptionLogger
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Amazon\PayV2\CustomerData\CheckoutSession $amazonCheckoutSession,
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
        \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory,
        ExceptionLogger $exceptionLogger = null
    ) {
        parent::__construct($context);
        $this->amazonCheckoutSession = $amazonCheckoutSession;
        $this->exceptionLogger = $exceptionLogger ?: ObjectManager::getInstance()->get(ExceptionLogger::class);
        $this->cookieManager = $cookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
    }

    /*
     * @inheritdoc
     */
    public function execute()
    {
        try {
            $orderId = $this->amazonCheckoutSession->completeCheckoutSession();
            if (!$orderId) {
                throw new \Exception(__('Something went wrong. Please try again.'));
            }
            $this->updateVersionCookie();
            return $this->_redirect('checkout/onepage/success');
        } catch (\Exception $e) {
            $this->exceptionLogger->logException($e);
            $this->amazonCheckoutSession->clearCheckoutSessionId();
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        return $this->_redirect('checkout/cart');
    }

    /**
     * Generate unique version identifier
     *
     * @return string
     */
    protected function generateValue()
    {
        return md5(rand() . time());
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
