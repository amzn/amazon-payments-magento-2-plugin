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

namespace Amazon\Core\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Module\ModuleListInterface;
use Magento\Framework\Module\StatusFactory;
use Amazon\Core\Model\AmazonConfig;

/**
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 */
class Data extends AbstractHelper
{

    const AMAZON_ACTIVE = 'payment/amazon_payment/active';

    /**
     * @var Config
     */
    private $config;

    /**
     * Data constructor.
     *
     * @param Context $context
     * @param AmazonConfig $config
     */
    public function __construct(
        Context $context,
        AmazonConfig $config
    ) {
        parent::__construct($context);
        $this->config = $config;
    }

    /**
     * Magic method which creates a simple wrapper around Amazon\Core\Model\AmazonConfig
     *
     * @param $method
     * @param $arguments
     * @return mixed
     */
    public function __call($method, $arguments)
    {
        return call_user_func_array([$this->config, $method], $arguments);
    }

}
