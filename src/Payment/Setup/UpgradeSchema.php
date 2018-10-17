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
namespace Amazon\Payment\Setup;

use Amazon\Payment\Api\Data\PendingAuthorizationInterface;
use Amazon\Payment\Api\Data\PendingCaptureInterface;
use Amazon\Payment\Model\ResourceModel\OrderLink;
use Amazon\Payment\Model\ResourceModel\PendingAuthorization;
use Amazon\Payment\Model\ResourceModel\PendingCapture;
use Amazon\Payment\Model\ResourceModel\QuoteLink;
use Amazon\Payment\Api\Data\PendingRefundInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Sales\Api\Data\TransactionInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{

    const PENDING_REFUND_TABLE_NAME = 'amazon_pending_refund';

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {


        if (version_compare($context->getVersion(), '1.4.0', '<')) {
            $table = $setup->getConnection()->newTable($setup->getTable(PendingCapture::TABLE_NAME));

            $table
                ->addColumn(
                    PendingCaptureInterface::ID,
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
                    PendingCaptureInterface::CAPTURE_ID,
                    Table::TYPE_TEXT,
                    255,
                    [
                        'nullable' => false
                    ]
                )
                ->addColumn(
                    PendingCaptureInterface::CREATED_AT,
                    Table::TYPE_DATETIME,
                    null,
                    [
                        'nullable' => false
                    ]
                )
                ->addIndex(
                    $setup->getIdxName(
                        PendingCapture::TABLE_NAME,
                        [PendingCaptureInterface::CAPTURE_ID],
                        AdapterInterface::INDEX_TYPE_UNIQUE
                    ),
                    [PendingCaptureInterface::CAPTURE_ID],
                    ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
                );

            $setup->getConnection()->createTable($table);
        }


        if (version_compare($context->getVersion(), '1.7.0', '<')) {
            $this->addColumnsToPendingCaptureQueue($setup);
        }

        if (version_compare($context->getVersion(), '1.8.0', '<')) {
            $this->createPendingAuthorizationQueueTable($setup);
        }

        if (version_compare($context->getVersion(), '1.9.0', '<')) {
            $this->createPendingRefundTable($setup);
        }

        if (version_compare($context->getVersion(), '1.10.0', '<')) {
            $this->addColumnsToPendingAuthorizationQueue($setup);
        }

        if (version_compare($context->getVersion(), '1.11.0', '<')) {
            $this->addCaptureColumnsToPendingAuthorizationQueue($setup);
        }
    }

    private function addCaptureColumnsToPendingAuthorizationQueue(SchemaSetupInterface $setup)
    {
        $setup->getConnection()->addColumn(
            $setup->getTable(PendingAuthorization::TABLE_NAME),
            PendingAuthorizationInterface::CAPTURE,
            [
                'unsigned' => true,
                'nullable' => true,
                'default'  => 0,
                'type'     => Table::TYPE_SMALLINT,
                'comment'  => 'Initial authorization has capture'
            ]
        );

        $setup->getConnection()->addColumn(
            $setup->getTable(PendingAuthorization::TABLE_NAME),
            PendingAuthorizationInterface::CAPTURE_ID,
            [
                'nullable' => true,
                'type'     => Table::TYPE_TEXT,
                'length'   => 255,
                'comment'  => 'Initial authorization capture id'
            ]
        );
    }

    private function addColumnsToPendingAuthorizationQueue(SchemaSetupInterface $setup)
    {
        $setup->getConnection()->addColumn(
            $setup->getTable(PendingAuthorization::TABLE_NAME),
            PendingAuthorizationInterface::PROCESSED,
            [
                'unsigned' => true,
                'nullable' => true,
                'default'  => 0,
                'type'     => Table::TYPE_SMALLINT,
                'comment'  => 'Initial authorization processed'
            ]
        );
    }

    private function addColumnsToPendingCaptureQueue(SchemaSetupInterface $setup)
    {
        $setup->getConnection()->addColumn(
            $setup->getTable(PendingCapture::TABLE_NAME),
            'order_id',
            [
                'unsigned' => true,
                'nullable' => false,
                'type'     => Table::TYPE_INTEGER,
                'comment'  => 'order id'
            ]
        );

        $setup->getConnection()->addColumn(
            $setup->getTable(PendingCapture::TABLE_NAME),
            'payment_id',
            [
                'unsigned' => true,
                'nullable' => false,
                'type'     => Table::TYPE_INTEGER,
                'comment'  => 'payment id'
            ]
        );

        $setup->getConnection()->dropIndex(
            $setup->getTable(PendingCapture::TABLE_NAME),
            $setup->getIdxName(
                PendingCapture::TABLE_NAME,
                [PendingCaptureInterface::CAPTURE_ID],
                AdapterInterface::INDEX_TYPE_UNIQUE
            )
        );

        $pendingColumns = [
            PendingCaptureInterface::ORDER_ID,
            PendingCaptureInterface::PAYMENT_ID,
            PendingCaptureInterface::CAPTURE_ID
        ];

        $setup->getConnection()->addIndex(
            $setup->getTable(PendingCapture::TABLE_NAME),
            $setup->getIdxName(
                PendingCapture::TABLE_NAME,
                $pendingColumns,
                AdapterInterface::INDEX_TYPE_UNIQUE
            ),
            $pendingColumns,
            AdapterInterface::INDEX_TYPE_UNIQUE
        );
    }

    private function createPendingAuthorizationQueueTable(SchemaSetupInterface $setup)
    {
        $table = $setup->getConnection()->newTable($setup->getTable(PendingAuthorization::TABLE_NAME));

        $table
            ->addColumn(
                PendingAuthorizationInterface::ID,
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
                PendingAuthorizationInterface::ORDER_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true,
                    'nullable' => false
                ]
            )
            ->addColumn(
                PendingAuthorizationInterface::PAYMENT_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true,
                    'nullable' => false
                ]
            )
            ->addColumn(
                PendingAuthorizationInterface::AUTHORIZATION_ID,
                Table::TYPE_TEXT,
                255,
                [
                    'nullable' => true
                ]
            )
            ->addColumn(
                PendingAuthorizationInterface::CREATED_AT,
                Table::TYPE_DATETIME,
                null,
                [
                    'nullable' => false
                ]
            )
            ->addColumn(
                PendingAuthorizationInterface::UPDATED_AT,
                Table::TYPE_DATETIME,
                null,
                [
                    'nullable' => true
                ]
            );

        $setup->getConnection()->createTable($table);

        $pendingColumns = [
            PendingAuthorizationInterface::ORDER_ID,
            PendingAuthorizationInterface::PAYMENT_ID,
            PendingAuthorizationInterface::AUTHORIZATION_ID
        ];

        $setup->getConnection()->addIndex(
            $setup->getTable(PendingAuthorization::TABLE_NAME),
            $setup->getIdxName(
                PendingAuthorization::TABLE_NAME,
                $pendingColumns,
                AdapterInterface::INDEX_TYPE_UNIQUE
            ),
            $pendingColumns,
            AdapterInterface::INDEX_TYPE_UNIQUE
        );
    }

    private function createPendingRefundTable(SchemaSetupInterface $setup)
    {
        $setup->getConnection()->dropTable(static::PENDING_REFUND_TABLE_NAME);

        $table = $setup->getConnection()->newTable($setup->getTable(static::PENDING_REFUND_TABLE_NAME));

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

        $setup->getConnection()->createTable($table);

        $pendingColumns = [
            PendingRefundInterface::ORDER_ID,
            PendingRefundInterface::PAYMENT_ID,
            PendingRefundInterface::REFUND_ID,
        ];

        $indexName = $setup->getConnection()->getIndexName(
            $setup->getTable(static::PENDING_REFUND_TABLE_NAME),
            $pendingColumns,
            AdapterInterface::INDEX_TYPE_UNIQUE
        );

        $setup->getConnection()->addIndex(
            $setup->getTable(static::PENDING_REFUND_TABLE_NAME),
            $indexName,
            $pendingColumns,
            AdapterInterface::INDEX_TYPE_UNIQUE
        );
    }
}
