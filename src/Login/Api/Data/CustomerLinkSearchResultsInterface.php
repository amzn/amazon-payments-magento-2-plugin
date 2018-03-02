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
namespace Amazon\Login\Api\Data;

/**
 * @api
 */
interface CustomerLinkSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * Gets collection items.
     *
     * @return \Amazon\Login\Api\Data\CustomerLinkInterface[] Array of collection items.
     */
    public function getItems();

    /**
     * Sets collection items.
     *
     * @param \Amazon\Login\Api\Data\CustomerLinkInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
