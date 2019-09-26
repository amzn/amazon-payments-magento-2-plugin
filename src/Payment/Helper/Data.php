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
namespace Amazon\Payment\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Module\ModuleListInterface;

class Data extends AbstractHelper
{
    const MODULE_CODE = 'Amazon_Payment';

    /**
     * @var ModuleListInterface
     */
    private $moduleList;

    /**
     * Data constructor.
     *
     * @param Context             $context
     * @param ModuleListInterface $moduleList
     */
    public function __construct(Context $context, ModuleListInterface $moduleList)
    {
        parent::__construct($context);
        $this->moduleList = $moduleList;
    }
}
