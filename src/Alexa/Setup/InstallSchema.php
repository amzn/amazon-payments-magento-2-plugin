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
namespace Amazon\Alexa\Setup;

use Amazon\Alexa\Model\ResourceModel\AlexaCarrier;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Class InstallSchema
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * Create Alexa Carrier table
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $table = $setup->getConnection()->newTable($setup->getTable(AlexaCarrier::TABLE_NAME));

        $table
            ->addColumn(
                'carrier_title',
                Table::TYPE_TEXT,
                255,
                [
                    //'identity' => true,
                    'primary'  => true,
                    'nullable' => false
                ]
            )
            ->addColumn(
                'carrier_code',
                Table::TYPE_TEXT,
                255,
                [
                    'nullable' => false
                ]
            );

        $setup->getConnection()->createTable($table);
    }
}
