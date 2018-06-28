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
namespace Amazon\Login\Plugin;

use Closure;
use Amazon\Core\Helper\Data as AmazonHelper;
use Magento\Customer\Model\ResourceModel\Customer\Collection;
use Magento\Eav\Model\Entity\Attribute\AttributeInterface;
use Magento\Framework\DB\Select;

class CustomerCollection
{
    /**
     * @var AmazonHelper
     */
    private $amazonHelper;

    /**
     * @param AmazonHelper $amazonHelper
     */
    public function __construct(
        AmazonHelper $amazonHelper
    ) {
        $this->amazonHelper = $amazonHelper;
    }

    /**
     * Resolve issue with core magento not allowing extension attributes to be applied as filter
     *
     * @param Collection                              $collection
     * @param Closure                                 $proceed
     * @param AttributeInterface|integer|string|array $attribute
     * @param array|string|null                       $condition
     * @param string                                  $joinType
     *
     * @return Collection
     */
    public function aroundAddAttributeToFilter(
        Collection $collection,
        Closure $proceed,
        $attribute,
        $condition = null,
        $joinType = 'inner'
    ) {
        if ($this->amazonHelper->isLwaEnabled() && is_array($attribute)) {
            $attribute = $this->addAmazonIdFilter($attribute, $collection);

            if (0 === count($attribute)) {
                return $collection;
            }
        }

        return $proceed($attribute, $condition, $joinType);
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    private function addAmazonIdFilter(array $attribute, Collection $collection)
    {
        foreach ($attribute as $key => $condition) {
            if ('amazon_id' == $condition['attribute']) {
                $collection->getSelect()->where('extension_attribute_amazon_id.amazon_id = ?', $condition['eq']);
                unset($attribute[$key]);
            }
        }

        return $attribute;
    }
}
