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
use Magento\Catalog\Model\Product;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

/**
 * @api
 */
class ProductPagePaymentLink extends PaymentLink
{
    /**
     * @var CategoryExclusion
     */
    protected $categoryExclusionHelper;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @param Context           $context
     * @param Data              $coreHelper
     * @param CategoryExclusion $categoryExclusionHelper
     * @param Registry          $registry
     * @param array             $data
     */
    public function __construct(
        Context $context,
        Data $coreHelper,
        CategoryExclusion $categoryExclusionHelper,
        Registry $registry,
        array $data = []
    ) {
        parent::__construct($context, $coreHelper, $categoryExclusionHelper, $data);
        $this->registry = $registry;
    }

    /**
     * {@inheritdoc}
     */
    protected function _toHtml()
    {
        if (! $this->coreHelper->isPwaButtonVisibleOnProductPage()) {
            return '';
        }

        /** @var \Magento\Catalog\Model\Product $product */
        $product = $this->registry->registry('product');

        if ($product instanceof Product &&
            $this->categoryExclusionHelper->productHasExcludedCategory($product)
        ) {
            return '';
        }

        return parent::_toHtml();
    }
}
