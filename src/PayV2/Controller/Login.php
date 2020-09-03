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
namespace Amazon\PayV2\Controller;

use Amazon\PayV2\Client\ClientFactoryInterface;
use Amazon\PayV2\Api\Data\AmazonCustomerInterface;
use Amazon\PayV2\Domain\AmazonCustomerFactory;
use Amazon\PayV2\Model\AmazonConfig;
use Amazon\PayV2\Model\Validator\AccessTokenRequestValidator;
use Amazon\PayV2\Model\Customer\Account\Redirect as AccountRedirect;
use Amazon\PayV2\Helper\Session;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Customer\Model\Url;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Store\Model\StoreManager;
use Psr\Log\LoggerInterface;
use Amazon\PayV2\Model\Customer\MatcherInterface;
use Amazon\PayV2\Api\CustomerLinkManagementInterface;
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
     * @var Adapter\AmazonPayV2Adapter
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
     * Login constructor.
     * @param Context $context
     * @param AmazonCustomerFactory $amazonCustomerFactory
     * @param \Amazon\PayV2\Model\Adapter\AmazonPayV2Adapter $amazonAdapter
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
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        AmazonCustomerFactory $amazonCustomerFactory,
        \Amazon\PayV2\Model\Adapter\AmazonPayV2Adapter $amazonAdapter,
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
        AccountManagementInterface $accountManagement
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
        parent::__construct($context);
    }

    /**
     * Load userinfo from access token
     *
     * @return AmazonCustomerInterface|false
     */
    protected function getAmazonCustomer($token)
    {
        try {
            $userInfo = $this->amazonAdapter
                ->getBuyer($token);

            if (is_array($userInfo) && isset($userInfo['buyerId'])) {
                $data = [
                    'id'      => $userInfo['buyerId'],
                    'email'   => $userInfo['email'],
                    'name'    => $userInfo['name'],
                    'country' => $this->amazonConfig->getRegion(),
                ];
                $amazonCustomer = $this->amazonCustomerFactory->create($data);

                return $amazonCustomer;
            }
        } catch (\Exception $e) {
            $this->logger->error($e);
            $this->messageManager->addErrorMessage(__('Error processing Amazon Login'));
        }

        return false;
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
}
