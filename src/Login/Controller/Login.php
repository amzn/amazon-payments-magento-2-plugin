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
namespace Amazon\Login\Controller;

use Amazon\Core\Client\ClientFactoryInterface;
use Amazon\Core\Api\Data\AmazonCustomerInterface;
use Amazon\Core\Domain\AmazonCustomerFactory;
use Amazon\Core\Helper\Data as AmazonCoreHelper;
use Amazon\Login\Model\Validator\AccessTokenRequestValidator;
use Amazon\Login\Model\Customer\Account\Redirect as AccountRedirect;
use Amazon\Login\Helper\Session;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Customer\Model\Url;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Psr\Log\LoggerInterface;
use Amazon\Login\Model\Customer\MatcherInterface;
use Amazon\Login\Api\CustomerLinkManagementInterface;

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
     * @var ClientFactoryInterface
     */
    protected $clientFactory;

    /**
     * @var AmazonCoreHelper
     */
    protected $amazonCoreHelper;

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
     * Login constructor.
     * @param Context $context
     * @param AmazonCustomerFactory $amazonCustomerFactory
     * @param ClientFactoryInterface $clientFactory
     * @param LoggerInterface             $logger
     * @param AmazonCoreHelper            $amazonCoreHelper
     * @param Url                         $customerUrl
     * @param AccessTokenRequestValidator $accessTokenRequestValidator
     * @param AccountRedirect $accountRedirect
     * @param MatcherInterface $matcher
     * @param CustomerLinkManagementInterface $customerLinkManagement
     * @param CustomerSession $customerSession
     * @param Session $session
     * @param LoggerInterface $logger
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        AmazonCustomerFactory $amazonCustomerFactory,
        ClientFactoryInterface $clientFactory,
        AmazonCoreHelper $amazonCoreHelper,
        Url $customerUrl,
        AccessTokenRequestValidator $accessTokenRequestValidator,
        AccountRedirect $accountRedirect,
        MatcherInterface $matcher,
        CustomerLinkManagementInterface $customerLinkManagement,
        CustomerSession $customerSession,
        Session $session,
        LoggerInterface $logger
    ) {
        $this->amazonCustomerFactory       = $amazonCustomerFactory;
        $this->clientFactory               = $clientFactory;
        $this->amazonCoreHelper            = $amazonCoreHelper;
        $this->customerUrl                 = $customerUrl;
        $this->accessTokenRequestValidator = $accessTokenRequestValidator;
        $this->accountRedirect             = $accountRedirect;
        $this->matcher                     = $matcher;
        $this->customerLinkManagement      = $customerLinkManagement;
        $this->customerSession             = $customerSession;
        $this->session                     = $session;
        $this->logger                      = $logger;
        parent::__construct($context);
    }

    /**
     * Load userinfo from access token
     *
     * @return AmazonCustomerInterface
     */
    protected function getAmazonCustomer()
    {
        try {
            $userInfo = $this->clientFactory
                ->create()
                ->getUserInfo($this->getRequest()->getParam('access_token'));

            if (is_array($userInfo) && isset($userInfo['user_id'])) {
                $data = [
                    'id'      => $userInfo['user_id'],
                    'email'   => $userInfo['email'],
                    'name'    => $userInfo['name'],
                    'country' => $this->amazonCoreHelper->getRegion(),
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
