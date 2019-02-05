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
namespace Amazon\Core\Observer;

use Amazon\Core\Helper\Data;
use Amazon\Core\Model\Validation\ApiCredentialsValidatorFactory;
use Amazon\Core\Model\Config\Credentials\Json;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\App\Config\ReinitableConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;

class PaymentConfigSaveAfter implements ObserverInterface
{
    /**
     * @var ApiCredentialsValidatorFactory
     */
    private $apiCredentialsValidatorFactory;

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * @var Json
     */
    private $jsonCredentials;

    /**
     * @var Data
     */
    private $amazonCoreHelper;

    /**
     * Application config
     *
     * @var ReinitableConfigInterface
     */
    private $appConfig;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * PaymentConfigSaveAfter constructor.
     *
     * @param ApiCredentialsValidatorFactory $apiCredentialsValidatorFactory
     * @param ManagerInterface $messageManager
     * @param Json $jsonCredentials
     * @param Data $amazonCoreHelper
     * @param ReinitableConfigInterface $config
     * @param RequestInterface $request
     */
    public function __construct(
        ApiCredentialsValidatorFactory $apiCredentialsValidatorFactory,
        ManagerInterface $messageManager,
        Json $jsonCredentials,
        Data $amazonCoreHelper,
        ReinitableConfigInterface $config,
        RequestInterface $request
    ) {
        $this->apiCredentialsValidatorFactory = $apiCredentialsValidatorFactory;
        $this->messageManager                 = $messageManager;
        $this->amazonCoreHelper               = $amazonCoreHelper;
        $this->jsonCredentials                = $jsonCredentials;
        $this->appConfig                      = $config;
        $this->request                        = $request;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(Observer $observer)
    {
        if (!$this->request->getParam('amazon_test_creds')) {
            return;
        }

        $scopeData = $this->getScopeData($observer);
        $jsonCredentials = $this->amazonCoreHelper->getCredentialsJson($scopeData['scope'], $scopeData['scope_id']);

        if (!empty($jsonCredentials)) {
            $this->appConfig->reinit();
            $this->jsonCredentials->processCredentialsJson($jsonCredentials, $scopeData);
        }

        /** @see \Magento\Config\Model\Config::save() */
        $validator = $this->apiCredentialsValidatorFactory->create();

        $messageManagerMethod = 'addErrorMessage';

        if ($validator->isValid($scopeData['scope_id'], $scopeData['scope'])) {
            $messageManagerMethod = 'addSuccessMessage';
        }

        foreach ($validator->getMessages() as $message) {
            $this->messageManager->$messageManagerMethod($message);
        }
    }

    protected function getScopeData($observer)
    {
        $scopeData = [];

        $scopeData['scope']    = 'default';
        $scopeData['scope_id'] = null;

        $website = $observer->getWebsite();
        $store   = $observer->getStore();

        if ($website) {
             $scopeData['scope']    = ScopeInterface::SCOPE_WEBSITES;
             $scopeData['scope_id'] = $website;
        }

        if ($store) {
             $scopeData['scope']    = ScopeInterface::SCOPE_STORES;
             $scopeData['scope_id'] = $store;
        }

        return $scopeData;
    }
}
