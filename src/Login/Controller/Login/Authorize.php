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
use Amazon\Login\Api\Customer\CompositeMatcherInterface;
use Amazon\Login\Api\CustomerManagerInterface;
use Amazon\Login\Domain\ValidationCredentials;
use Amazon\Login\Helper\Session;
use Amazon\Login\Model\Validator\AccessTokenRequestValidator;
use Amazon\Login\Model\Customer\Account\Redirect as AccountRedirect;
use Magento\Customer\Model\Url;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Exception\ValidatorException;
use Psr\Log\LoggerInterface;
use Zend_Validate;

class Authorize extends Action
{
    /**
     * @var ClientFactoryInterface
     */
    protected $clientFactory;

    /**
     * @var CompositeMatcherInterface
     */
    protected $matcher;

    /**
     * @var CustomerManagerInterface
     */
    protected $customerManager;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var AccountRedirect
     */
    protected $accountRedirect;

    /**
     * @var AmazonCustomerFactory
     */
    protected $amazonCustomerFactory;

    /**
     * @var LoggerInterface
     */
    protected $logger;

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
     * @param Context                     $context
     * @param ClientFactoryInterface      $clientFactory
     * @param CompositeMatcherInterface   $matcher
     * @param CustomerManagerInterface    $customerManager
     * @param Session                     $session
     * @param AccountRedirect             $accountRedirect
     * @param AmazonCustomerFactory       $amazonCustomerFactory
     * @param LoggerInterface             $logger
     * @param AmazonCoreHelper            $amazonCoreHelper
     * @param Url                         $customerUrl
     * @param AccessTokenRequestValidator $accessTokenRequestValidator
     */
    public function __construct(
        Context $context,
        ClientFactoryInterface $clientFactory,
        CompositeMatcherInterface $matcher,
        CustomerManagerInterface $customerManager,
        Session $session,
        AccountRedirect $accountRedirect,
        AmazonCustomerFactory $amazonCustomerFactory,
        LoggerInterface $logger,
        AmazonCoreHelper $amazonCoreHelper,
        Url $customerUrl,
        AccessTokenRequestValidator $accessTokenRequestValidator
    ) {
        parent::__construct($context);

        $this->clientFactory               = $clientFactory;
        $this->matcher                     = $matcher;
        $this->customerManager             = $customerManager;
        $this->session                     = $session;
        $this->accountRedirect             = $accountRedirect;
        $this->amazonCustomerFactory       = $amazonCustomerFactory;
        $this->logger                      = $logger;
        $this->amazonCoreHelper            = $amazonCoreHelper;
        $this->customerUrl                 = $customerUrl;
        $this->accessTokenRequestValidator = $accessTokenRequestValidator;
    }

    public function execute()
    {
        if (! $this->amazonCoreHelper->isLwaEnabled()) {
            throw new NotFoundException(__('Action is not available'));
        }

        if (! $this->accessTokenRequestValidator->isValid($this->getRequest())) {
            return $this->_redirect($this->customerUrl->getLoginUrl());
        }

        try {
            $userInfo = $this->clientFactory->create()->getUserInfo($this->getRequest()->getParam('access_token'));

            if (is_array($userInfo) && isset($userInfo['user_id'])) {
                $amazonCustomer = $this->amazonCustomerFactory->create([
                    'id'    => $userInfo['user_id'],
                    'email' => $userInfo['email'],
                    'name'  => $userInfo['name'],
                ]);

                $processed = $this->processAmazonCustomer($amazonCustomer);

                if ($processed instanceof ValidationCredentials) {
                    $this->session->setValidationCredentials($processed);
                    $this->session->setAmazonCustomer($amazonCustomer);
                    return $this->_redirect($this->_url->getRouteUrl('*/*/validate'));
                } else {
                    $this->session->login($processed);
                }
            }
        } catch (ValidatorException $e) {
            $this->logger->error($e);
            $this->messageManager->addErrorMessage($e->getMessage());
            $this->_eventManager->dispatch('amazon_login_authorize_validation_error', ['exception' => $e]);
            $this->_eventManager->dispatch('amazon_login_authorize_error', ['exception' => $e]);
        } catch (\Exception $e) {
            $this->logger->error($e);
            $this->messageManager->addErrorMessage(__('Error processing Amazon Login'));
            $this->_eventManager->dispatch('amazon_login_authorize_error', ['exception' => $e]);
        }

        return $this->accountRedirect->getRedirect();
    }

    protected function processAmazonCustomer(AmazonCustomer $amazonCustomer)
    {
        $customerData = $this->matcher->match($amazonCustomer);

        if (null === $customerData) {
            return $this->createCustomer($amazonCustomer);
        }

        if ($amazonCustomer->getId() != $customerData->getExtensionAttributes()->getAmazonId()) {
            if (! $this->session->isLoggedIn()) {
                return new ValidationCredentials($customerData->getId(), $amazonCustomer->getId());
            }

            $this->customerManager->updateLink($customerData->getId(), $amazonCustomer->getId());
        }

        return $customerData;
    }

    protected function createCustomer(AmazonCustomer $amazonCustomer)
    {
        if (! Zend_Validate::is($amazonCustomer->getEmail(), 'EmailAddress')) {
            throw new ValidatorException(__('the email address for your Amazon account is invalid'));
        }

        $customerData = $this->customerManager->create($amazonCustomer);
        $this->customerManager->updateLink($customerData->getId(), $amazonCustomer->getId());

        return $customerData;
    }
}
