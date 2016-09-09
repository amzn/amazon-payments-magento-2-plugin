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
namespace Amazon\Login\Controller\Login;

use Amazon\Core\Client\ClientFactoryInterface;
use Amazon\Core\Domain\AmazonCustomer;
use Amazon\Core\Domain\AmazonCustomerFactory;
use Amazon\Core\Helper\Data as AmazonCoreHelper;
use Amazon\Login\Model\Validator\AccessTokenRequestValidator;
use Amazon\Login\Model\Customer\Account\Redirect as AccountRedirect;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\Url;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Exception\NotFoundException;
use Psr\Log\LoggerInterface;

class Guest extends Action
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
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var Session
     */
    protected $customerSession;

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
     * @param Context                     $context
     * @param AmazonCustomerFactory       $amazonCustomerFactory
     * @param ClientFactoryInterface      $clientFactory
     * @param LoggerInterface             $logger
     * @param Session                     $customerSession
     * @param AmazonCoreHelper            $amazonCoreHelper
     * @param Url                         $customerUrl
     * @param AccessTokenRequestValidator $accessTokenRequestValidator
     * @param AccountRedirect             $accountRedirect
     */
    public function __construct(
        Context $context,
        AmazonCustomerFactory $amazonCustomerFactory,
        ClientFactoryInterface $clientFactory,
        LoggerInterface $logger,
        Session $customerSession,
        AmazonCoreHelper $amazonCoreHelper,
        Url $customerUrl,
        AccessTokenRequestValidator $accessTokenRequestValidator,
        AccountRedirect $accountRedirect
    ) {
        parent::__construct($context);
        $this->amazonCustomerFactory = $amazonCustomerFactory;
        $this->clientFactory = $clientFactory;
        $this->logger = $logger;
        $this->customerSession = $customerSession;
        $this->amazonCoreHelper = $amazonCoreHelper;
        $this->customerUrl = $customerUrl;
        $this->accessTokenRequestValidator = $accessTokenRequestValidator;
        $this->accountRedirect = $accountRedirect;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        if ($this->amazonCoreHelper->isLwaEnabled()) {
            throw new NotFoundException(__('Action is not available'));
        }

        if (! $this->accessTokenRequestValidator->isValid($this->getRequest())) {
            return $this->_redirect($this->customerUrl->getLoginUrl());
        }

        try {
            $userInfo = $this->clientFactory
                             ->create()
                             ->getUserInfo($this->getRequest()->getParam('access_token'));

            if (is_array($userInfo) && isset($userInfo['user_id'])) {
                $amazonCustomer = $this->amazonCustomerFactory->create([
                    'id'    => $userInfo['user_id'],
                    'email' => $userInfo['email'],
                    'name'  => $userInfo['name'],
                ]);

                $this->storeUserInfoToSession($amazonCustomer);
            }
        } catch (\Exception $e) {
            $this->logger->error($e);
            $this->messageManager->addErrorMessage(__('Error processing Amazon Login'));
        }

        return $this->accountRedirect->getRedirect();
    }

    /**
     * @param AmazonCustomer $amazonCustomer
     * @return void
     */
    protected function storeUserInfoToSession(AmazonCustomer $amazonCustomer)
    {
        $this->customerSession->setAmazonCustomer($amazonCustomer);
    }
}
