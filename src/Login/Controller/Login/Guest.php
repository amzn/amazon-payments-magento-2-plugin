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
use Amazon\Core\Helper\Data as AmazonCoreHelper;
use Amazon\Login\Model\Validator\AccessTokenRequestValidator;
use Magento\Checkout\Model\Session;
use Magento\Customer\Model\Url;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Psr\Log\LoggerInterface;

class Guest extends Action
{
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
     * @var Session
     */
    private $session;

    /**
     * @var ClientFactoryInterface
     */
    private $clientFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    private $quoteRepository;

    /**
     * Guest constructor.
     * @param Context $context
     * @param AmazonCoreHelper $amazonCoreHelper
     * @param Url $customerUrl
     * @param AccessTokenRequestValidator $accessTokenRequestValidator
     * @param Session $session
     * @param ClientFactoryInterface $clientFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        AmazonCoreHelper $amazonCoreHelper,
        Url $customerUrl,
        AccessTokenRequestValidator $accessTokenRequestValidator,
        Session $session,
        ClientFactoryInterface $clientFactory,
        LoggerInterface $logger
    )
    {
        $this->amazonCoreHelper = $amazonCoreHelper;
        $this->customerUrl = $customerUrl;
        $this->accessTokenRequestValidator = $accessTokenRequestValidator;
        $this->session = $session;
        $this->clientFactory = $clientFactory;
        $this->logger = $logger;
        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        if (!$this->isValidToken()) {
            return $this->getRedirectLogin();
        }

        $customerData = $this->getAmazonCustomer();
        if ($customerData && isset($customerData['email'])) {
            $quote = $this->session->getQuote();

            if ($quote) {
                $quote->setCustomerEmail($customerData['email']);
                $quote->save();
            }
        }

        return $this->_redirect('checkout');
    }

    /**
     * @return string
     */
    private function getRedirectLogin()
    {
        return $this->_redirect($this->customerUrl->getLoginUrl());
    }

    /**
     * @return bool
     */
    private function isValidToken()
    {
        $isValid = false;
        try {
            $isValid = $this->accessTokenRequestValidator->isValid($this->getRequest());
        } catch (\Zend_Validate_Exception $e) {
            $this->logger->error($e);
        }

        return $isValid;
    }

    /**
     * @return array
     */
    private function getAmazonCustomer()
    {
        try {
            $userInfo = $this->clientFactory
                ->create()
                ->getUserInfo($this->getRequest()->getParam('access_token'));

            if (is_array($userInfo) && isset($userInfo['user_id'])) {
                $data = [
                    'id' => $userInfo['user_id'],
                    'email' => $userInfo['email'],
                    'name' => $userInfo['name'],
                    'country' => $this->amazonCoreHelper->getRegion(),
                ];

                return $data;
            }
        } catch (\Exception $e) {
            $this->logger->error($e);
        }

        return [];
    }
}
