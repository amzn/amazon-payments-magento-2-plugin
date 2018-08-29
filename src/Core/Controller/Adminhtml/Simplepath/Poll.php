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
namespace Amazon\Core\Controller\Adminhtml\Simplepath;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Controller\Adminhtml\System;

class Poll extends System
{

    /**
     * @var \Amazon\Core\Model\Config\SimplePath
     */
    private $simplePath;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    private $jsonResultFactory;

    public function __construct(
        Context $context,
        \Amazon\Core\Model\Config\SimplePath $simplePath,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Controller\Result\JsonFactory $jsonResultFactory
    ) {
        parent::__construct($context);
        $this->simplePath = $simplePath;
        $this->scopeConfig = $scopeConfig;
        $this->jsonResultFactory = $jsonResultFactory;
    }

    /**
     * Detect whether Amazon credentials are set (polled by Ajax)
     */
    public function execute()
    {
        // Keypair is destroyed when credentials are saved
        $shouldRefresh = !($this->scopeConfig->getValue(
            \Amazon\Core\Model\Config\SimplePath::CONFIG_XML_PATH_PUBLIC_KEY,
            'default',
            0
        ));

        if ($shouldRefresh) {
            $this->simplePath->autoEnable();
        }

        $result = $this->jsonResultFactory->create();
        $result->setData((int)$shouldRefresh);
        return $result;
    }
}
