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
namespace Amazon\Login\Api;

use Amazon\Login\Api\Data\CustomerLinkInterface;
use Magento\Customer\Api\Data\CustomerInterface;

/**
 * CustomerLink (Amazon Customer <=> Magento Customer) Management interface.
 *
 * @api
 */
interface CustomerLinkManagementInterface
{
    /**
     * Load CustomerLink by Customer ID.
     *
     * @param int $customerId
     * @return CustomerLinkInterface Customer link interface.
     */
    public function getByCustomerId($customerId);

    /**
     * Create magento customer using amazon customer details
     *
     * @param AmazonCustomerInterface $amazonCustomer
     * @return CustomerInterface|null
     */
    public function create(\Amazon\Core\Api\Data\AmazonCustomerInterface $amazonCustomer);

    /**
     * Create or update magento/amazon customer link entity
     *
     * @param integer $customerId
     * @param string  $amazonId
     * @return void
     */
    public function updateLink($customerId, $amazonId);
}
