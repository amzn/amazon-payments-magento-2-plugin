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

use Magento\Catalog\Model\Product;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Quote\Model\Quote;
use Magento\Store\Model\ScopeInterface;

/**
 * @deprecated As of February 2021, this Legacy Amazon Pay plugin has been
 * deprecated, in favor of a newer Amazon Pay version available through GitHub
 * and Magento Marketplace. Please download the new plugin for automatic
 * updates and to continue providing your customers with a seamless checkout
 * experience. Please see https://pay.amazon.com/help/E32AAQBC2FY42HS for details
 * and installation instructions.
 */
class CategoryExclusion extends AbstractHelper
{
    const ATTR_QUOTE_ITEM_IS_EXCLUDED_PRODUCT = 'is_excluded_product';

    /**
     * @var Session
     */
    protected $checkoutSession;

    /**
     * @param Context $context
     * @param Session $checkoutSession
     */
    public function __construct(Context $context, Session $checkoutSession)
    {
        parent::__construct($context);
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * @param string      $scope
     * @param null|string $scopeCode
     *
     * @return array
     */
    public function getExcludedCategories($scope = ScopeInterface::SCOPE_STORE, $scopeCode = null)
    {
        $excludedCategoryIds = $this->scopeConfig
                                    ->getValue('payment/amazon_payment/excluded_categories', $scope, $scopeCode);

        return explode(',', $excludedCategoryIds);
    }

    /**
     * @param Product $product
     *
     * @return bool
     */
    public function productHasExcludedCategory(Product $product)
    {
        /**
         * \Magento\Catalog\Model\Product::getCategoryIds() doesn't take into consideration the *current*
         * category, but returns *all* of the associated categories IDs of the product.
         *
         * This means that, even if the *current* category is not blacklisted, the button might still not
         * appear. This is expected behaviour.
         */
        return count(array_intersect($product->getCategoryIds(), $this->getExcludedCategories())) > 0;
    }

    /**
     * @return bool
     */
    public function isQuoteDirty()
    {
        if (!empty($this->getExcludedCategories())) {
            /** @var \Magento\Quote\Model\Quote\Item\AbstractItem $quoteItem */
            foreach ($this->checkoutSession->getQuote()->getAllItems() as $quoteItem) {
                $isDirtyQuoteItem = $quoteItem->getDataUsingMethod(
                    CategoryExclusion::ATTR_QUOTE_ITEM_IS_EXCLUDED_PRODUCT
                );
                if (!$quoteItem->isDeleted() && $isDirtyQuoteItem) {
                    return true;
                }
            }
        }

        return false;
    }
}
