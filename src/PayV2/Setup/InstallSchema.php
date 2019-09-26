<?php
/**
 * Copyright © Amazon.com, Inc. or its affiliates. All Rights Reserved.
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
namespace Amazon\PayV2\Setup;

use Amazon\PayV2\Model\ResourceModel\Async;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements InstallSchemaInterface
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $table = $setup->getConnection()->newTable($setup->getTable(Async::TABLE_NAME));

        $table
            ->addColumn(
                'entity_id',
                Table::TYPE_INTEGER,
                null,
                [
                    'identity' => true,
                    'unsigned' => true,
                    'primary'  => true,
                    'nullable' => false
                ]
            )
            ->addColumn(
                'order_id',
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true,
                    'nullable' => true
                ]
            )
            ->addColumn(
                'is_pending',
                Table::TYPE_BOOLEAN,
                1,
                [
                    'unsigned' => true,
                    'nullable' => false,
                    'default' => 1,
                ]
            )
            ->addColumn(
                'pending_action',
                Table::TYPE_TEXT,
                20,
                [
                    'nullable' => false
                ]
            )
            ->addColumn(
                'pending_id',
                Table::TYPE_TEXT,
                50,
                [
                    'nullable' => true
                ]
            )
            ->addColumn(
                'created_at',
                Table::TYPE_DATETIME,
                null,
                [
                    'nullable' => true
                ]
            )
            ->addColumn(
                'updated_at',
                Table::TYPE_DATETIME,
                null,
                [
                    'nullable' => true
                ]
            )
            ->addIndex(
                $setup->getIdxName(
                    Async::TABLE_NAME,
                    ['customer_id', 'amazon_id'],
                    AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                ['is_pending'],
                ['type' => AdapterInterface::INDEX_TYPE_INDEX]
            );

        $setup->getConnection()->createTable($table);
    }
}
