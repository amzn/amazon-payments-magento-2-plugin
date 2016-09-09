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
namespace Amazon\Payment\Setup\Table;

use Amazon\Payment\Api\Data\PendingRefundInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;

class AmazonPendingRefund
{
    const TABLE_NAME = 'amazon_pending_refund';

    /**
     * @var AdapterInterface
     */
    protected $connection;

    /**
     * @var ResourceConnection
     */
    protected $resource;

    /**
     * @param AdapterInterface   $connection
     * @param ResourceConnection $resource
     */
    public function __construct(AdapterInterface $connection, ResourceConnection $resource)
    {
        $this->connection = $connection;
        $this->resource = $resource;
    }

    /**
     * @return void
     */
    public function createTable()
    {
        $this->connection->dropTable(static::TABLE_NAME);
        $this->doCreateTable();
    }

    /**
     * @throws \Zend_Db_Exception
     * @return void
     */
    protected function doCreateTable()
    {
        $table = $this->connection->newTable($this->resource->getTableName(static::TABLE_NAME));

        $table
            ->addColumn(
                PendingRefundInterface::ID,
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
                PendingRefundInterface::REFUND_ID,
                Table::TYPE_TEXT,
                255,
                [
                    'nullable' => false
                ]
            )
            ->addColumn(
                PendingRefundInterface::CREATED_AT,
                Table::TYPE_DATETIME,
                null,
                [
                    'nullable' => false
                ]
            )
            ->addColumn(
                PendingRefundInterface::ORDER_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true,
                    'nullable' => false,
                    'comment'  => 'order id'
                ]
            )
            ->addColumn(
                PendingRefundInterface::PAYMENT_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true,
                    'nullable' => false,
                    'comment'  => 'payment id'
                ]
            );

        $this->connection->createTable($table);
        $this->addTableIndexes();
    }

    /**
     * @return void
     */
    protected function addTableIndexes()
    {
        $pendingColumns = [
            PendingRefundInterface::ORDER_ID,
            PendingRefundInterface::PAYMENT_ID,
            PendingRefundInterface::REFUND_ID,
        ];

        $indexName = $this->connection->getIndexName(
            $this->resource->getTableName(static::TABLE_NAME),
            $pendingColumns,
            AdapterInterface::INDEX_TYPE_UNIQUE
        );

        $this->connection->addIndex(
            $this->resource->getTableName(static::TABLE_NAME),
            $indexName,
            $pendingColumns,
            AdapterInterface::INDEX_TYPE_UNIQUE
        );
    }
}
