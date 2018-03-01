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

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * CustomerLink (Amazon Customer <=> Magento Customer) CRUD interface.
 *
 * @api
 */
interface CustomerLinkRepositoryInterface
{
    /**
     * Lists payment tokens that match specified search criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria The search criteria.
     * @return \Amazon\Login\Api\Data\CustomerLinkSearchResultsInterface search result interface.
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * Loads by Customer ID.
     *
     * @param int $customerId The customer ID.
     * @return \Amazon\Login\Api\Data\CustomerLinkInterface Customer link interface.
     */
    public function get($customerId);

    /**
     * Loads by Entity ID.
     *
     * @param int $entityId The customer link entity ID.
     * @return \Amazon\Login\Api\Data\CustomerLinkInterface Customer link interface.
     */
    public function getById($entityId);

    /**
     * Delete customer link.
     *
     * @param \Amazon\Login\Api\Data\CustomerLinkInterface Customer link interface.
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(Data\CustomerLinkInterface $customerLink);

    /**
     * Delete customer link by entity id
     *
     * @param string $entityId
     * @return bool
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     */
    public function deleteById($entityId);

    /**
     * Save customer link
     *
     * @param \Amazon\Login\Api\Data\CustomerLinkInterface Customer link interface.
     * @return \Amazon\Login\Api\Data\CustomerLinkInterface
     * @throws CouldNotSaveException
     */
    public function save(Data\CustomerLinkInterface $customerLink);
}
