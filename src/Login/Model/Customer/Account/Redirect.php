<?php
/**
 * Copyright 2016 Amazon.com, Inc. or its affiliates. All Rights Reserved.
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
namespace Amazon\Login\Model\Customer\Account;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\Account\Redirect as BaseRedirect;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Customer\Model\Url as CustomerUrl;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Url\DecoderInterface;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * @deprecated As of February 2021, this Legacy Amazon Pay plugin has been
 * deprecated, in favor of a newer Amazon Pay version available through GitHub
 * and Magento Marketplace. Please download the new plugin for automatic
 * updates and to continue providing your customers with a seamless checkout
 * experience. Please see https://pay.amazon.com/help/E32AAQBC2FY42HS for details
 * and installation instructions.
 */
class Redirect extends BaseRedirect
{
    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var CustomerSession
     */
    private $customerSession;

    public function __construct(
        RequestInterface $request,
        CustomerSession $customerSession,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        UrlInterface $url,
        DecoderInterface $urlDecoder,
        CustomerUrl $customerUrl,
        ResultFactory $resultFactory,
        CookieMetadataFactory $cookieMetadataFactory,
        CheckoutSession $checkoutSession
    ) {
        parent::__construct(
            $request,
            $customerSession,
            $scopeConfig,
            $storeManager,
            $url,
            $urlDecoder,
            $customerUrl,
            $resultFactory,
            $cookieMetadataFactory
        );

        $this->customerSession = $customerSession;
        $this->checkoutSession = $checkoutSession;
    }

    public function getRedirect()
    {
        $this->updateLastCustomerId();

        $result = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        $afterAmazonAuthUrl = $this->customerUrl->getAccountUrl();

        if ($this->checkoutSession->getQuote() && (int)$this->checkoutSession->getQuote()->getItemsCount() > 0) {
            $afterAmazonAuthUrl = $this->url->getUrl('checkout');
        } elseif ($this->customerSession->getAfterAmazonAuthUrl()) {
            $afterAmazonAuthUrl = $this->customerSession->getAfterAmazonAuthUrl();
        }

        $result->setUrl($afterAmazonAuthUrl);

        return $result;
    }
}
