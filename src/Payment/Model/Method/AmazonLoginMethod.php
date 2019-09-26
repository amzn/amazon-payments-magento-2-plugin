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
namespace Amazon\Payment\Model\Method;

use Amazon\Core\Helper\Data as AmazonCoreHelper;

/**
 * Class AmazonLoginMethod
 * For display of Amazon Pay in payment method list
 */
class AmazonLoginMethod extends \Magento\Payment\Model\Method\AbstractMethod
{
    const PAYMENT_METHOD_LOGIN_METHOD = 'amazonlogin';

    /**
     * Payment method code
     *
     * @var string
     */
    protected $_code = self::PAYMENT_METHOD_LOGIN_METHOD;

    /**
     * @var string
     */
    protected $_formBlockType = 'Magento\Payment\Block\Form';

    /**
     * Info instructions block path
     *
     * @var string
     */
    protected $_infoBlockType = 'Magento\Payment\Block\Info\Instructions';

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_isOffline = true;

    /**
     * Is active
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isActive($storeId = null)
    {
        return (bool)(int)$this->_scopeConfig->getValue(
            AmazonCoreHelper::AMAZON_ACTIVE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        ) && (bool)(int)$this->getConfigData('active', $storeId);
    }
}
