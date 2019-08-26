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

namespace Amazon\Alexa\Model;

use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Cache\Type\Config as CacheTypeConfig;
use Zend\Crypt\PublicKey\RsaOptions;

class AlexaConfig
{
    /**
     * @var \Magento\Framework\App\Config\ConfigResource\ConfigInterface
     */
    private $config;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    private $encryptor;

    /**
     * @var \Magento\Framework\App\Cache\Manager
     */
    private $cacheManager;

    /**
     * AlexaConfig constructor.
     * @param \Magento\Framework\App\Config\ConfigResource\ConfigInterface $config
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\App\Cache\Manager $cacheManager
     * @param \Magento\Framework\Encryption\EncryptorInterface $encryptor
     */
    public function __construct(
        \Magento\Framework\App\Config\ConfigResource\ConfigInterface $config,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\Cache\Manager $cacheManager,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor
    ) {
        $this->config       = $config;
        $this->scopeConfig  = $scopeConfig;
        $this->cacheManager = $cacheManager;
        $this->encryptor    = $encryptor;
    }

    /**
     * Check to Alexa Delivery Notifications is enabled
     *
     * @param string $scope
     * @param null $scopeCode
     * @param null $store
     *
     * @return bool
     */
    public function isAlexaEnabled($scope = ScopeInterface::SCOPE_STORE, $scopeCode = null)
    {
        return $this->scopeConfig->getValue(
            'payment/amazon_payment/alexa_active',
            $scope,
            $scopeCode
        );
    }

    /**
     * Return Alexa Private Key
     *
     * @param string $scope
     * @param null $scopeCode
     * @param null $store
     *
     * @return string
     */
    public function getAlexaPrivateKey($scope = ScopeInterface::SCOPE_STORE, $scopeCode = null)
    {
        return $this->scopeConfig->getValue(
            'payment/amazon_payment/alexa_private_key',
            $scope,
            $scopeCode
        );
    }

    /**
     * Return Alexa Public Key
     *
     * @param string $scope
     * @param null $scopeCode
     * @param null $store
     *
     * @return string
     */
    public function getAlexaPublicKey($scope = ScopeInterface::SCOPE_STORE, $scopeCode = null)
    {
        return $this->scopeConfig->getValue(
            'payment/amazon_payment/alexa_public_key',
            $scope,
            $scopeCode
        );
    }

    /**
     * Return Alexa Public Key ID
     *
     * @param string $scope
     * @param null $scopeCode
     * @param null $store
     *
     * @return string
     */
    public function getAlexaPublicKeyId($scope = ScopeInterface::SCOPE_STORE, $scopeCode = null)
    {
        return $this->scopeConfig->getValue(
            'payment/amazon_payment/alexa_public_key_id',
            $scope,
            $scopeCode
        );
    }

    /**
     * Generate and save new public/private keys
     */
    public function generateKeys()
    {
        $rsa = new RsaOptions();
        $rsa->generateKeys(array(
            'private_key_bits' => 2048,
        ));

        $encrypt = $this->encryptor->encrypt((string) $rsa->getPrivateKey());

        $this->config
            ->saveConfig('payment/amazon_payment/alexa_public_key', (string) $rsa->getPublicKey())
            ->saveConfig('payment/amazon_payment/alexa_private_key', $encrypt);

        $this->cacheManager->clean([CacheTypeConfig::TYPE_IDENTIFIER]);
    }
}
