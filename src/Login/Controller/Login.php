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
    private $amazonCustomerFactory;

    /**
     * @var ClientFactoryInterface
     */
    private $clientFactory;

    /**
     * @var AmazonCoreHelper
     */
    private $amazonCoreHelper;

    /**
     * @var Url
     */
    private $customerUrl;

    /**
     * @var AccessTokenRequestValidator
     */
    private $accessTokenRequestValidator;

    /**
     * @var AccountRedirect
     */
    private $accountRedirect;

    /**
     * @var MatcherInterface
     */
    private $matcher;

    /**
     * @var CustomerLinkManagementInterface
     */
    private $customerLinkManagement;

    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Login constructor.
     * @param \Magento\Framework\App\Action\Context                     $context
     * @param \Amazon\Core\Domain\AmazonCustomerFactory                 $amazonCustomerFactory
     * @param \Amazon\Core\Client\ClientFactoryInterface                $clientFactory
     * @param \Amazon\Core\Helper\Data                                  $amazonCoreHelper
     * @param \Magento\Customer\Model\Url                               $customerUrl
     * @param \Amazon\Login\Model\Validator\AccessTokenRequestValidator $accessTokenRequestValidator
     * @param \Amazon\Login\Model\Customer\Account\Redirect             $accountRedirect
     * @param \Amazon\Login\Model\Customer\MatcherInterface             $matcher
     * @param \Amazon\Login\Api\CustomerLinkManagementInterface         $customerLinkManagement
     * @param \Magento\Customer\Model\Session                           $customerSession
     * @param \Amazon\Login\Helper\Session                              $session
     * @param \Psr\Log\LoggerInterface                                  $logger
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
        $this->amazonCustomerFactory = $amazonCustomerFactory;
        $this->clientFactory = $clientFactory;
        $this->amazonCoreHelper = $amazonCoreHelper;
        $this->customerUrl = $customerUrl;
        $this->accessTokenRequestValidator = $accessTokenRequestValidator;
        $this->accountRedirect = $accountRedirect;
        $this->matcher = $matcher;
        $this->customerLinkManagement = $customerLinkManagement;
        $this->customerSession = $customerSession;
        $this->session = $session;
        $this->logger = $logger;
        parent::__construct($context);
    }

    /**
     * Load userinfo from access token
     *
     * @return \Amazon\Core\Domain\AmazonCustomer|bool
     */
    public function getAmazonCustomer()
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
     * @throws \Zend_Validate_Exception
     */
    public function isValidToken()
    {
        return $this->accessTokenRequestValidator->isValid($this->getRequest());
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function getRedirectLogin()
    {
        return $this->_redirect($this->customerUrl->getLoginUrl());
    }

    /**
     * @return \Magento\Framework\Controller\Result\Forward|\Magento\Framework\Controller\Result\Redirect
     * |\Magento\Framework\Controller\ResultInterface
     */
    public function getRedirectAccount()
    {
        return $this->accountRedirect->getRedirect();
    }
}
