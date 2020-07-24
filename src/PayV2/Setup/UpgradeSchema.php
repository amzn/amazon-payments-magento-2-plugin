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

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        if (version_compare($context->getVersion(), '1.4.0', '<')) {
            $table = $setup->getConnection()->newTable($setup->getTable('amazon_payv2_checkout_session'));
            $table
                ->addColumn(
                    'id',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'identity' => true,
                        'unsigned' => true,
                        'primary'  => true,
                        'nullable' => false,
                        'comment' => 'ID',
                    ]
                )
                ->addColumn(
                    'session_id',
                    Table::TYPE_TEXT,
                    36,
                    [
                        'nullable' => false,
                        'comment' => 'Session ID',
                    ]
                )
                ->addColumn(
                    'quote_id',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'padding' => 10,
                        'unsigned' => true,
                        'nullable' => false,
                        'comment' => 'Quote ID',
                    ]
                )
                ->addColumn(
                    'is_active',
                    Table::TYPE_BOOLEAN,
                    null,
                    [
                        'nullable' => false,
                        'default' => 1,
                        'comment' => 'Is Active',
                    ]
                )
                ->addColumn(
                    'created_at',
                    Table::TYPE_DATETIME,
                    null,
                    [
                        'nullable' => false,
                        'comment' => 'Created At',
                    ]
                )
                ->addColumn(
                    'canceled_at',
                    Table::TYPE_DATETIME,
                    null,
                    [
                        'nullable' => true,
                        'comment' => 'Canceled At',
                    ]
                )
                ->addColumn(
                    'updated_at',
                    Table::TYPE_DATETIME,
                    null,
                    [
                        'nullable' => true,
                        'comment' => 'Updated At',
                    ]
                )
                ->addColumn(
                    'completed_at',
                    Table::TYPE_DATETIME,
                    null,
                    [
                        'nullable' => true,
                        'comment' => 'Completed At',
                    ]
                )
                ->addForeignKey(
                    'AMAZON_PAYV2_CHECKOUT_SESSION_QUOTE_ID_QUOTE_ENTITY_ID',
                    'quote_id',
                    'quote',
                    'entity_id',
                    Table::ACTION_CASCADE
                )
                ->addIndex(
                    'AMAZON_PAYV2_CHECKOUT_SESSION_IS_ACTIVE',
                    ['is_active'],
                    ['type' => AdapterInterface::INDEX_TYPE_INDEX]
                );

            $setup->getConnection()->createTable($table);
        }
    }
}
