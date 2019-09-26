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
namespace Amazon\Payment\Model\ResourceModel\PendingAuthorization;

use Amazon\Payment\Api\Data\PendingAuthorizationInterface;
use Generator;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Amazon\Payment\Model\PendingAuthorization as PendingAuthorizationModel;
use Amazon\Payment\Model\ResourceModel\PendingAuthorization as PendingAuthorizationResourceModel;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(PendingAuthorizationModel::class, PendingAuthorizationResourceModel::class);
    }

    /**
     * Get ID generator
     *
     * @return Generator
     */
    public function getIdGenerator()
    {
        $this->_renderFilters()->_renderOrders()->_renderLimit();
        $select = $this->getSelect();

        $statement = $select->getConnection()->query($select, $this->_bindParams);

        while ($row = $statement->fetch()) {
            yield $row[PendingAuthorizationInterface::ID];
        }
    }
}
