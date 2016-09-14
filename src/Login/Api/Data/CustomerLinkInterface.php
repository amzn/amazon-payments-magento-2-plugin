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

use Exception;

interface CustomerLinkInterface
{
    /**
     * Set amazon id
     *
     * @param integer $amazonId
     *
     * @return $this
     */
    public function setAmazonId($amazonId);

    /**
     * Get amazon id
     *
     * @return string
     */
    public function getAmazonId();

    /**
     * Set customer id
     *
     * @param integer $customerId
     *
     * @return $this
     */
    public function setCustomerId($customerId);

    /**
     * Get customer id
     *
     * @return integer
     */
    public function getCustomerId();

    /**
     * Save customer link
     *
     * @return $this
     * @throws Exception
     */
    public function save();

    /**
     * Load customer link data
     *
     * @param integer $modelId
     * @param null|string $field
     * @return $this
     */
    public function load($modelId, $field = null);
}
