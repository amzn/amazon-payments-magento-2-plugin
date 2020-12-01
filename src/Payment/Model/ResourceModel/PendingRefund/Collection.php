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
namespace Amazon\Payment\Model\ResourceModel\PendingRefund;

use Amazon\Payment\Api\Data\PendingRefundInterface;
use Amazon\Payment\Model\PendingRefund as PendingRefundModel;
use Amazon\Payment\Model\ResourceModel\PendingRefund as PendingRefundResourceModel;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * @deprecated As of February 2021, this Legacy Amazon Pay plugin has been
 * deprecated, in favor of a newer Amazon Pay version available through GitHub
 * and Magento Marketplace. Please download the new plugin for automatic
 * updates and to continue providing your customers with a seamless checkout
 * experience. Please see https://pay.amazon.com/help/E32AAQBC2FY42HS for details
 * and installation instructions.
 */
class Collection extends AbstractCollection
{

    protected function _construct()
    {
        $this->_init(PendingRefundModel::class, PendingRefundResourceModel::class);
    }

    /**
     * @return \Generator
     */
    public function getIdGenerator()
    {
        $this->_renderFilters()->_renderOrders()->_renderLimit();
        $select = $this->getSelect();

        $statement = $select->getConnection()->query($select, $this->_bindParams);

        while ($row = $statement->fetch()) {
            yield $row[PendingRefundInterface::ID];
        }
    }
}
