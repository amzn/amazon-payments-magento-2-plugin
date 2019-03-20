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

use Amazon\Core\Model\AmazonConfig;
use Amazon\Core\Helper\CategoryExclusion;
use Magento\Checkout\CustomerData\Cart;

class CartSection
{
    /**
     * @var CategoryExclusion
     */
    private $categoryExclusionHelper;

    /**
     * @var AmazonConfig
     */
    private $amazonConfig;

    /**
     * @param CategoryExclusion $categoryExclusionHelper
     * @param AmazonConfig $amazonConfigt
     */
    public function __construct(
        CategoryExclusion $categoryExclusionHelper,
        AmazonConfig $amazonConfig
    ) {
        $this->categoryExclusionHelper = $categoryExclusionHelper;
        $this->amazonConfig            = $amazonConfig;
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
        if ($this->amazonConfig->isPwaEnabled()) {
            $result['amazon_quote_has_excluded_item'] = $this->categoryExclusionHelper->isQuoteDirty();
        }

        return $result;
    }
}
