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
namespace Amazon\Payment\Model\ResourceModel;

use Amazon\Payment\Api\Data\PendingRefundInterface;
use Amazon\Payment\Setup\UpgradeSchema as AmazonSchemaHelper;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class PendingRefund extends AbstractDb
{
    const TABLE_NAME = AmazonSchemaHelper::PENDING_REFUND_TABLE_NAME;

    protected function _construct()
    {
        $this->_init(static::TABLE_NAME, PendingRefundInterface::ID);
    }

    /**
     * {@inheritDoc}
     */
    protected function _getLoadSelect($field, $value, $object)
    {
        /** @var \Amazon\Payment\Model\PendingRefund $object */
        $select = parent::_getLoadSelect($field, $value, $object);
        $select->forUpdate($object->hasLockOnLoad());

        return $select;
    }
}
