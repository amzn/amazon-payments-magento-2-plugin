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

namespace Amazon\Pay\Setup\Patch\Data;

use Amazon\Pay\Api\Data\AsyncInterfaceFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\App\ResourceConnection;

class MigrateLegacyTransactions implements DataPatchInterface
{
    public const AUTH_TABLE_NAME = 'amazon_pending_authorization';
    public const ACTION_AUTH   = 'authorization';
    public const CAPTURE_TABLE_NAME = 'amazon_pending_capture';
    public const REFUND_TABLE_NAME = 'amazon_pending_refund';
    public const ACTION_REFUND = 'refund';

    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var AsyncInterfaceFactory
     */
    private $asyncFactory;

    /**
     * MigrateLegacyTransactions constructor
     *
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param ResourceConnection $resourceConnection
     * @param AsyncInterfaceFactory $asyncFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        ResourceConnection $resourceConnection,
        AsyncInterfaceFactory $asyncFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->resourceConnection = $resourceConnection;
        $this->asyncFactory = $asyncFactory;
    }

    /**
     * @inheritdoc
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        $connection  = $this->resourceConnection->getConnection();
        if ($connection->isTableExists(self::AUTH_TABLE_NAME)) {
            $select = $connection->select()
                ->from(self::AUTH_TABLE_NAME)
                ->where(
                    "processed = 0"
                );
            $rows = $connection->fetchAll($select);
            foreach ($rows as $row) {
                if (!is_array($row) || !array_key_exists('authorization_id', $row)) {
                    continue;
                }
                $pendingId = substr_replace($row['authorization_id'], 'C', 20, 1);
                $this->asyncFactory->create()
                    ->setPendingId($pendingId)
                    ->setPendingAction(self::ACTION_AUTH)
                    ->save();
            }
        }

        $this->moduleDataSetup->getConnection()->endSetup();
    }
    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public static function getVersion()
    {
        return '5.0.0';
    }
}
