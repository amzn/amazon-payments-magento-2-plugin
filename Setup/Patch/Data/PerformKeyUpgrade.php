<?php
/**
 * Copyright © Amazon.com, Inc. or its affiliates. All Rights Reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the "License").
 * You may not use this file except in compliance with the License.
 * A copy of the License is located at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * or in the "license" file accompanying this file. This file is distributed
 * on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either
 * express or implied. See the License for the specific language governing
 * permissions and limitations under the License.
 *
 */

namespace Amazon\Pay\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\Config\ConfigResource\ConfigInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Amazon\Pay\Api\KeyUpgradeInterface;

class PerformKeyUpgrade implements DataPatchInterface
{
    public const PATH_TRANSLATION_MAP = [
        'payment/amazon_payment/active' => 'payment/amazon_payment_v2/active',
        'payment/amazon_payment/merchant_id' => 'payment/amazon_payment_v2/merchant_id',
        'payment/amazon_payment/client_id' => 'payment/amazon_payment_v2/store_id',
        'payment/amazon_payment/lwa_enabled' => 'payment/amazon_payment_v2/lwa_enabled',
        'payment/amazon_payment/payment_action' => 'payment/amazon_payment_v2/payment_action',
        'payment/amazon_payment/button_color' => 'payment/amazon_payment_v2/button_color',
        'payment/amazon_payment/allowed_ips' => 'payment/amazon_payment_v2/allowed_ips'
    ];

    /**
     * @var KeyUpgradeInterface
     */
    private $keyUpgrade;

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var EncryptorInterface
     */
    private $encryptor;

    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @param KeyUpgradeInterface $keyUpgrade
     * @param ConfigInterface $config
     * @param EncryptorInterface $encryptor
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        KeyUpgradeInterface $keyUpgrade,
        ConfigInterface $config,
        EncryptorInterface $encryptor,
        ModuleDataSetupInterface $moduleDataSetup,
        ResourceConnection $resourceConnection
    ) {
        $this->keyUpgrade = $keyUpgrade;
        $this->config = $config;
        $this->encryptor = $encryptor;
        $this->moduleDataSetup = $moduleDataSetup;
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * @inheritdoc
     */
    public function apply()
    {
        $accessKeys = $this->getAccessKeysByScope();
        foreach ($accessKeys as [
                'scope' => $scopeType,
                'scope_id' => $scopeId,
                'value' => $accessKey
            ]) {

            if ($accessKey) {
                $publicKeyId = $this->keyUpgrade->getPublicKeyId(
                    $scopeType,
                    $scopeId,
                    $accessKey
                );

                if (!empty($publicKeyId)) {
                    $this->keyUpgrade->updateKeysInConfig(
                        $publicKeyId,
                        $scopeType,
                        $scopeId
                    );
                }
            }

        }

        // Upgrade all other configs
        $savedConfigs = $this->getSavedV1Configs();
        foreach ($savedConfigs as [
                'scope_id' => $scopeId,
                'scope' => $scopeType,
                'path' => $path,
                'value' => $value
            ]) {

            // Prevent overwriting already valid config
            if ($this->v2PathAlreadyExists(
                self::PATH_TRANSLATION_MAP[$path],
                $scopeType,
                $scopeId
            )) {
                continue;
            }

            $this->config->saveConfig(
                self::PATH_TRANSLATION_MAP[$path],
                $value,
                $scopeType,
                $scopeId
            );
        }
    }

    /**
     * Return a list of scopes with unique access key IDs
     *
     * @return array
     */
    private function getAccessKeysByScope()
    {
        $conn = $this->moduleDataSetup->getConnection();
        $select = $conn->select()
            ->from($this->resourceConnection->getTableName('core_config_data'), ['scope_id', 'scope', 'value'])
            ->where('path = ?', 'payment/amazon_payment/access_key')
            ->order('scope_id');

        return $conn->fetchAll($select);
    }

    /**
     * Return all explicitly saved Amazon Pay CV1 config values that need to have their paths updated
     *
     * @return array
     */
    private function getSavedV1Configs()
    {
        $conn = $this->moduleDataSetup->getConnection();
        $select = $conn->select()
            ->from($this->resourceConnection->getTableName('core_config_data'), ['scope_id', 'scope', 'path', 'value'])
            ->where('path in (?)', array_keys(self::PATH_TRANSLATION_MAP))
            ->order('scope_id');

        return $conn->fetchAll($select);
    }

    /**
     * Return all Amazon Pay CV2 config values that already exist in core_config
     *
     * @return array
     */
    private function getSavedV2Configs()
    {
        $conn = $this->moduleDataSetup->getConnection();
        $select = $conn->select()
            ->from('core_config_data', ['scope_id', 'scope', 'path', 'value'])
            ->where('path in (?)', array_values(self::PATH_TRANSLATION_MAP))
            ->order('scope_id');

        return $conn->fetchAll($select);
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
     * Get version
     *
     * @return string
     */
    public static function getVersion()
    {
        return '5.0.0';
    }

    /**
     * Check if passed config already exists
     *
     * @param string $path
     * @param string $scopeType
     * @param int $scopeId
     * @return bool
     */
    private function v2PathAlreadyExists(
        string $path,
        string $scopeType,
        int $scopeId
    ) {
        static $existingPaths = null;
        if ($existingPaths === null) {
            $existingPaths = $this->getSavedV2Configs();
        }
        foreach ($existingPaths as $config) {
            if ($path == $config['path']
                && $scopeType == $config['scope']
                && $scopeId == $config['scope_id']
            ) {
                return true;
            }
        }
        return false;
    }
}
