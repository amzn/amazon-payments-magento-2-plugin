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

use Magento\Backend\App\Action\Context;
use Magento\Backend\Controller\Adminhtml\System;
use Magento\Framework\App\Cache\Manager;
use Magento\Framework\App\Cache\Type\Config as CacheTypeConfig;
use Magento\Framework\App\Config\ConfigResource\ConfigInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Amazon\Pay\Model\Config\KeyUpgrade;
use Magento\Framework\Controller\ResultInterface;

/**
 * Manually hits the Key Upgrade endpoint to retrieve a CV2 Public Key ID
 */
class ManualKeyUpgrade extends System
{
    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var Manager
     */
    private $cacheManager;

    /**
     * @var JsonFactory
     */
    private $jsonResultFactory;

    /**
     * @var KeyUpgrade
     */
    private $keyUpgrade;

    /**
     * ManualKeyUpgrade constructor
     *
     * @param Context $context
     * @param ConfigInterface $config
     * @param JsonFactory $jsonResultFactory
     * @param Manager $cacheManager
     * @param KeyUpgrade $keyUpgrade
     */
    public function __construct(
        Context $context,
        ConfigInterface $config,
        JsonFactory $jsonResultFactory,
        Manager $cacheManager,
        KeyUpgrade $keyUpgrade
    ) {
        parent::__construct($context);
        $this->config = $config;
        $this->jsonResultFactory = $jsonResultFactory;
        $this->cacheManager = $cacheManager;
        $this->keyUpgrade = $keyUpgrade;
    }

    /**
     * Execute controller
     *
     * @return ResponseInterface|Json|ResultInterface
     */
    public function execute()
    {
        $scopeType = $this->_request->getParam('scope');
        $scopeCode = (int) $this->_request->getParam('scopeCode');

        $publicKeyId = $this->keyUpgrade->getPublicKeyId(
            $scopeType,
            $scopeCode,
            $this->_request->getParam('accessKey')
        );

        $result = $this->jsonResultFactory->create();
        if (!empty($publicKeyId)) {
            $this->keyUpgrade->updateKeysInConfig(
                $publicKeyId,
                $scopeType,
                $scopeCode
            );

            $this->cacheManager->clean([CacheTypeConfig::TYPE_IDENTIFIER]);
            $result->setData(['result' => 'success']);
            $this->messageManager->addSuccessMessage('Amazon Pay keys upgraded successfully.');
        } else {
            $result->setData(['result' => 'error']);
            $this->messageManager->addErrorMessage('Amazon Pay keys could not be upgraded. '
                . 'See the paywithamazon.log for more details');
        }

        return $result;
    }
}
