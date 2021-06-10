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
use Magento\Framework\App\Cache\Manager;
use Magento\Framework\App\Cache\Type\Config as CacheTypeConfig;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Backend\Controller\Adminhtml\System;
use Magento\Framework\App\Config\ConfigResource\ConfigInterface;

/**
 * Resets the public/private key pair used in Auto Key Exchange so that you can start the process over
 */
class ResetAutoKey extends System
{
    private ConfigInterface $config;
    private Manager $cacheManager;
    private JsonFactory $jsonResultFactory;

    /**
     * @param Context $context
     */
    public function __construct(
        Context $context,
        ConfigInterface $config,
        JsonFactory $jsonResultFactory,
        Manager $cacheManager
    ) {
        parent::__construct($context);
        $this->config = $config;
        $this->jsonResultFactory = $jsonResultFactory;
        $this->cacheManager = $cacheManager;
    }

    /**
     * Delete all temporary config used for auto key exchange
     */
    public function execute()
    {
        $this->config
            ->deleteConfig(AutoKeyExchange::CONFIG_XML_PATH_PUBLIC_KEY, 'default', 0)
            ->deleteConfig(AutoKeyExchange::CONFIG_XML_PATH_PRIVATE_KEY, 'default', 0)
            ->deleteConfig(AutoKeyExchange::CONFIG_XML_PATH_AUTH_TOKEN, 'default', 0);

        $this->cacheManager->clean([CacheTypeConfig::TYPE_IDENTIFIER]);

        $result = $this->jsonResultFactory->create();
        $result->setData(['result' => 'success']);
        return $result;
    }
}
