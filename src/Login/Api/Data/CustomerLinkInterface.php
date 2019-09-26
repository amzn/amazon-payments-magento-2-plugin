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
interface CustomerLinkInterface
{
    /*
     * Entity ID.
     */
    const ENTITY_ID = 'entity_id';

    /*
     * Customer ID.
     */
    const CUSTOMER_ID = 'customer_id';

    /*
     * Amazon ID.
     */
    const AMAZON_ID = 'amazon_id';

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
}
