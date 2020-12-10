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
namespace Amazon\Core\Observer;

use Amazon\Core\Helper\Data;
use Amazon\Core\Helper\CategoryExclusion;
use Magento\Checkout\Model\Session;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * @deprecated As of February 2021, this Legacy Amazon Pay plugin has been
 * deprecated, in favor of a newer Amazon Pay version available through GitHub
 * and Magento Marketplace. Please download the new plugin for automatic
 * updates and to continue providing your customers with a seamless checkout
 * experience. Please see https://pay.amazon.com/help/E32AAQBC2FY42HS for details
 * and installation instructions.
 */
class ExcludedCategoryQuoteItemAddition implements ObserverInterface
{
    /**
     * @var CategoryExclusion
     */
    private $categoryExclusionHelper;

    /**
     * @var Data
     */
    private $coreHelper;

    /**
     * @param CategoryExclusion $categoryExclusionHelper
     * @param Data $coreHelper
     */
    public function __construct(
        CategoryExclusion $categoryExclusionHelper,
        Data $coreHelper
    ) {
        $this->categoryExclusionHelper = $categoryExclusionHelper;
        $this->coreHelper              = $coreHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(Observer $observer)
    {
        /** @see \Magento\Quote\Model\Quote::addProduct() */

        if ($this->coreHelper->isPwaEnabled()) {
            /** @var \Magento\Quote\Model\Quote\Item\AbstractItem $quoteItem */
            foreach ($observer->getItems() as $quoteItem) {
                $isExcludedProduct = $this->categoryExclusionHelper->productHasExcludedCategory(
                    $quoteItem->getProduct()
                );
                $quoteItem->setDataUsingMethod(
                    CategoryExclusion::ATTR_QUOTE_ITEM_IS_EXCLUDED_PRODUCT,
                    $isExcludedProduct
                );
            }
        }
    }
}
