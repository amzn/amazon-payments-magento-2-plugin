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
namespace Amazon\Login\Setup;

use Amazon\Login\Model\ResourceModel\CustomerLink;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

/**
 * @deprecated As of February 2021, this Legacy Amazon Pay plugin has been
 * deprecated, in favor of a newer Amazon Pay version available through GitHub
 * and Magento Marketplace. Please download the new plugin for automatic
 * updates and to continue providing your customers with a seamless checkout
 * experience. Please see https://pay.amazon.com/help/E32AAQBC2FY42HS for details
 * and installation instructions.
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        if (version_compare($context->getVersion(), '1.1.0', '<')) {
            $table = $setup->getTable('customer_entity');
            $setup->getConnection()->addForeignKey(
                $setup->getFkName(CustomerLink::TABLE_NAME, 'customer_id', $table, 'entity_id'),
                $setup->getTable(CustomerLink::TABLE_NAME),
                'customer_id',
                $setup->getTable('customer_entity'),
                'entity_id',
                AdapterInterface::FK_ACTION_CASCADE
            );
        }

        if (version_compare($context->getVersion(), '1.2.0', '<')) {
            $setup->getConnection()->addIndex(
                $setup->getTable(CustomerLink::TABLE_NAME),
                $setup->getIdxName(CustomerLink::TABLE_NAME, ['customer_id'], AdapterInterface::INDEX_TYPE_UNIQUE),
                ['customer_id'],
                AdapterInterface::INDEX_TYPE_UNIQUE
            );
        }
    }
}
