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
namespace Amazon\Core\Plugin;

use Amazon\Core\Helper\Data;
use Amazon\Core\Helper\CategoryExclusion;
use Magento\Checkout\CustomerData\Cart;

class CartSection
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
     * @param Cart  $subject
     * @param array $result
     *
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetSectionData(Cart $subject, $result)
    {
        if ($this->coreHelper->isPwaEnabled()) {
            $result['amazon_quote_has_excluded_item'] = $this->categoryExclusionHelper->isQuoteDirty();
        }

        return $result;
    }
}
