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

namespace Amazon\Payment\Helper\Shortcut;

class Factory
{
    /**
     * Default validator
     */
    const DEFAULT_VALIDATOR = \Amazon\Payment\Helper\Shortcut\Validator::class;

    /**
     * Checkout validator
     */
    const CHECKOUT_VALIDATOR = \Amazon\Payment\Helper\Shortcut\CheckoutValidator::class;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $_objectManager;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->_objectManager = $objectManager;
    }

    /**
     * @param mixed $parameter
     * @return \Amazon\Payment\Helper\Shortcut\ValidatorInterface
     */
    public function create($parameter = null)
    {
        $instanceName = self::DEFAULT_VALIDATOR;
        if (is_object($parameter) && $parameter instanceof \Magento\Checkout\Model\Session) {
            $instanceName = self::CHECKOUT_VALIDATOR;
        }
        return $this->_objectManager->create($instanceName);
    }
}
