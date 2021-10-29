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
namespace Amazon\Pay\Controller;

use Amazon\Pay\Api\CheckoutSessionManagementInterface;
use Amazon\Pay\Client\ClientFactoryInterface;
use Amazon\Pay\Api\Data\AmazonCustomerInterface;
use Amazon\Pay\Domain\AmazonCustomerFactory;
use Amazon\Pay\Model\AmazonConfig;
use Amazon\Pay\Model\Validator\AccessTokenRequestValidator;
use Magento\Customer\Model\Account\Redirect as AccountRedirect;
use Amazon\Pay\Helper\Session;
use Amazon\Pay\Helper\Customer as CustomerHelper;
use Amazon\Pay\Model\Adapter\AmazonPayAdapter;
use Amazon\Pay\Domain\ValidationCredentials;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Customer\Model\Url;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Store\Model\StoreManager;
use Psr\Log\LoggerInterface;
use Amazon\Pay\Model\Customer\MatcherInterface;
use Amazon\Pay\Api\CustomerLinkManagementInterface;
use Magento\Framework\UrlInterface;

/**
 * Login with token controller
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
abstract class Login extends Action
{
    /**
     * @var AmazonCustomerFactory
     */
    protected $amazonCustomerFactory;

    /**
     * @var Adapter\AmazonPayAdapter
     */
    protected $amazonAdapter;

    /**
     * @var AmazonConfig
     */
    protected $amazonConfig;

    /**
     * @var Url
     */
    protected $customerUrl;

    /**
     * @var AccessTokenRequestValidator
     */
    protected $accessTokenRequestValidator;

    /**
     * @var AccountRedirect
     */
    protected $accountRedirect;

    /**
     * @var MatcherInterface
     */
    protected $matcher;

    /**
     * @var CustomerLinkManagementInterface
     */
    protected $customerLinkManagement;

    /**
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var UrlInterface
     */
    protected $url;

    /**
     * @var AccountManagementInterface
     */
    protected $accountManagement;

    /**
     * @var CheckoutSessionManagement
     */
    protected $checkoutSessionManagement;

    /**
     * @var CustomerHelper
     */
    protected $customerHelper;

    /**
     * Login constructor.
     * @param Context $context
     * @param AmazonCustomerFactory $amazonCustomerFactory
     * @param \Amazon\Pay\Model\Adapter\AmazonPayAdapter $amazonAdapter
     * @param AmazonConfig $amazonConfig
     * @param Url $customerUrl
     * @param AccessTokenRequestValidator $accessTokenRequestValidator
     * @param AccountRedirect $accountRedirect
     * @param MatcherInterface $matcher
     * @param CustomerLinkManagementInterface $customerLinkManagement
     * @param CustomerSession $customerSession
     * @param Session $session
     * @param LoggerInterface $logger
     * @param StoreManager $storeManager
     * @param UrlInterface $url
     * @param AccountManagementInterface $accountManagement
     * @param CheckoutSessionManagementInterface $checkoutSessionManagement
     * @param customerHelper $customerHelper
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        AmazonCustomerFactory $amazonCustomerFactory,
        AmazonPayAdapter $amazonAdapter,
        AmazonConfig $amazonConfig,
        Url $customerUrl,
        AccessTokenRequestValidator $accessTokenRequestValidator,
        AccountRedirect $accountRedirect,
        MatcherInterface $matcher,
        CustomerLinkManagementInterface $customerLinkManagement,
        CustomerSession $customerSession,
        Session $session,
        LoggerInterface $logger,
        StoreManager $storeManager,
        UrlInterface $url,
        AccountManagementInterface $accountManagement,
        CheckoutSessionManagementInterface $checkoutSessionManagement,
        CustomerHelper $customerHelper
    ) {
        $this->amazonCustomerFactory       = $amazonCustomerFactory;
        $this->amazonAdapter               = $amazonAdapter;
        $this->amazonConfig                = $amazonConfig;
        $this->customerUrl                 = $customerUrl;
        $this->accessTokenRequestValidator = $accessTokenRequestValidator;
        $this->accountRedirect             = $accountRedirect;
        $this->matcher                     = $matcher;
        $this->customerLinkManagement      = $customerLinkManagement;
        $this->customerSession             = $customerSession;
        $this->session                     = $session;
        $this->logger                      = $logger;
        $this->storeManager                = $storeManager;
        $this->url                         = $url;
        $this->accountManagement           = $accountManagement;
        $this->checkoutSessionManagement   = $checkoutSessionManagement;
        $this->customerHelper              = $customerHelper;
        parent::__construct($context);
    }

    /**
     * @return bool
     */
    protected function isValidToken()
    {
        return $this->accessTokenRequestValidator->isValid($this->getRequest());
    }

    /**
     * @return string
     */
    protected function getRedirectLogin()
    {
        return $this->_redirect($this->customerUrl->getLoginUrl());
    }

    /**
     * @return string
     */
    protected function getRedirectAccount()
    {
        return $this->accountRedirect->getRedirect();
    }

    protected function getAmazonCustomer($buyerInfo)
    {
        return $this->customerHelper->getAmazonCustomer($buyerInfo);
    }

    protected function processAmazonCustomer(AmazonCustomerInterface $amazonCustomer)
    {
        $customerData = $this->matcher->match($amazonCustomer);

        if (null === $customerData) {
            return $this->createCustomer($amazonCustomer);
        }

        if ($amazonCustomer->getId() != $customerData->getExtensionAttributes()->getAmazonId()) {
            if (! $this->session->isLoggedIn()) {
                return new ValidationCredentials($customerData->getId(), $amazonCustomer->getId());
            }

            $this->customerLinkManagement->updateLink($customerData->getId(), $amazonCustomer->getId());
        }

        return $customerData;
    }

    protected function createCustomer(AmazonCustomerInterface $amazonCustomer)
    {
        return $this->customerHelper->createCustomer($amazonCustomer);
    }
}
