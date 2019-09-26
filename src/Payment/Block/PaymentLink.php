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
namespace Amazon\Payment\Block;

use Amazon\Core\Helper\CategoryExclusion;
use Amazon\Core\Helper\Data;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Catalog\Model\Product;

/**
 * @api
 */
class PaymentLink extends Template
{
    /**
     * @var Data
     */
    protected $coreHelper;

    /**
     * @var CategoryExclusion
     */
    protected $categoryExclusionHelper;

    /**
     * @param Context           $context
     * @param Data              $coreHelper
     * @param CategoryExclusion $categoryExclusionHelper
     * @param array             $data
     */
    public function __construct(
        Context $context,
        Data $coreHelper,
        CategoryExclusion $categoryExclusionHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->coreHelper              = $coreHelper;
        $this->categoryExclusionHelper = $categoryExclusionHelper;
    }

    /**
     * {@inheritdoc}
     */
    protected function _toHtml()
    {
        if (! $this->coreHelper->isPaymentButtonEnabled() || $this->categoryExclusionHelper->isQuoteDirty()) {
            return '';
        }

        return parent::_toHtml();
    }
}
