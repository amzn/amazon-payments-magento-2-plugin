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

namespace Amazon\Pay\Controller\Adminhtml\Pay;

use Amazon\Pay\Model\Config\AutoKeyExchange;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Controller\Adminhtml\System;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Amazon\Pay\Logger\ExceptionLogger;
use Magento\Framework\Controller\Result\JsonFactory;

class AutoKeyExchangePoll extends System
{

    /**
     * @var AutoKeyExchange
     */
    private $autoKeyExchange;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var JsonFactory
     */
    private $jsonResultFactory;

    /**
     * @var ExceptionLogger
     */
    private $exceptionLogger;

    /**
     * AutoKeyExchangePoll constructor
     *
     * @param Context $context
     * @param AutoKeyExchange $autoKeyExchange
     * @param ScopeConfigInterface $scopeConfig
     * @param JsonFactory $jsonResultFactory
     * @param ExceptionLogger|null $exceptionLogger
     */
    public function __construct(
        Context $context,
        AutoKeyExchange $autoKeyExchange,
        ScopeConfigInterface $scopeConfig,
        JsonFactory $jsonResultFactory,
        ?ExceptionLogger $exceptionLogger = null
    ) {
        parent::__construct($context);
        $this->autoKeyExchange = $autoKeyExchange;
        $this->scopeConfig = $scopeConfig;
        $this->jsonResultFactory = $jsonResultFactory;
        $this->exceptionLogger = $exceptionLogger ?: ObjectManager::getInstance()->get(ExceptionLogger::class);
    }

    /**
     * Detect whether Amazon credentials are set (polled by Ajax)
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     * @throws \Exception
     */
    public function execute()
    {
        try {
            // Keypair is destroyed when credentials are saved, so is indication that process is complete
            $key = $this->scopeConfig->getValue(
                AutoKeyExchange::CONFIG_XML_PATH_PUBLIC_KEY,
                ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
                0
            );

            if (empty($key)) {
                $this->autoKeyExchange->autoEnable();
            }

            $result = $this->jsonResultFactory->create();
            $result->setData((int)empty($key));
            return $result;
        } catch (\Exception $e) {
            $this->exceptionLogger->logException($e);
            throw $e;
        }
    }
}
