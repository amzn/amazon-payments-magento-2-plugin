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

namespace Amazon\Pay\Model\Config;

use Amazon\Pay\Helper\Data as AmazonHelper;
use Amazon\Pay\Helper\Key as KeyHelper;
use Amazon\Pay\Model\AmazonConfig;
use Magento\Framework\App\State;
use Magento\Framework\App\Cache\Type\Config as CacheTypeConfig;
use Magento\Backend\Model\UrlInterface;

class AutoKeyExchange
{

    public const CONFIG_XML_PATH_PRIVATE_KEY = 'payment/amazon_payment/autokeyexchange/privatekey';
    public const CONFIG_XML_PATH_PUBLIC_KEY  = 'payment/amazon_payment/autokeyexchange/publickey';
    public const CONFIG_XML_PATH_AUTH_TOKEN  = 'payment/amazon_payment/autokeyexchange/auth_token';

    /**
     * @var array
     */
    private $_spIds = [
        'USD' => 'AUGT0HMCLQVX1',
        'GBP' => 'A1BJXVS5F6XP',
        'EUR' => 'A2ZAYEJU54T1BM',
        'JPY' => 'A1MCJZEB1HY93J',
    ];

    /**
     * @var array
     */
    private $_mapCurrencyRegion = [
        'EUR' => 'de',
        'USD' => 'us',
        'GBP' => 'uk',
        'JPY' => 'ja',
    ];

    /**
     * @var int
     */
    private $_storeId;

    /**
     * @var int
     */
    private $_websiteId;

    /**
     * @var string
     */
    private $_scope;

    /**
     * @var int
     */
    private $_scopeId;

    /**
     * @var AmazonHelper
     */
    private $amazonHelper;

    /**
     * @var AmazonConfig
     */
    private $amazonConfig;

    /**
     * @var KeyHelper
     */
    private $keyHelper;

    /**
     * @var \Magento\Framework\App\Config\ConfigResource\ConfigInterface
     */
    private $config;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    private $productMeta;

    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    private $encryptor;

    /**
     * @var UrlInterface
     */
    private $backendUrl;

    /**
     * @var \Magento\Framework\App\Cache\Manager
     */
    private $cacheManager;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $connection;

    /**
     * @var State
     */
    private $state;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    private $messageManager;

    /**
     * @var \Magento\Framework\Math\Random
     */
    private $mathRandom;

    /**
     * AutoKeyExchange constructor
     *
     * @param AmazonHelper $amazonHelper
     * @param AmazonConfig $amazonConfig
     * @param KeyHelper $keyHelper
     * @param \Magento\Framework\App\Config\ConfigResource\ConfigInterface $config
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\App\ProductMetadataInterface $productMeta
     * @param \Magento\Framework\Encryption\EncryptorInterface $encryptor
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Framework\App\ResourceConnection $connection
     * @param \Magento\Framework\App\Cache\Manager $cacheManager
     * @param State $state
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param UrlInterface $backendUrl
     * @param \Magento\Framework\Math\Random $mathRandom
     * @param \Psr\Log\LoggerInterface $logger
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        AmazonHelper $amazonHelper,
        AmazonConfig $amazonConfig,
        KeyHelper $keyHelper,
        \Magento\Framework\App\Config\ConfigResource\ConfigInterface $config,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\ProductMetadataInterface $productMeta,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\App\ResourceConnection $connection,
        \Magento\Framework\App\Cache\Manager $cacheManager,
        \Magento\Framework\App\State $state,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Backend\Model\UrlInterface $backendUrl,
        \Magento\Framework\Math\Random $mathRandom,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->amazonHelper  = $amazonHelper;
        $this->amazonConfig  = $amazonConfig;
        $this->keyHelper     = $keyHelper;
        $this->config        = $config;
        $this->scopeConfig   = $scopeConfig;
        $this->productMeta   = $productMeta;
        $this->encryptor     = $encryptor;
        $this->backendUrl    = $backendUrl;
        $this->cacheManager  = $cacheManager;
        $this->connection    = $connection;
        $this->state         = $state;
        $this->mathRandom    = $mathRandom;
        $this->logger        = $logger;

        $this->storeManager = $storeManager;
        $this->messageManager = $messageManager;

        $this->_storeId = $keyHelper->getStoreId();
        $this->_websiteId = $keyHelper->getWebsiteId();
        $this->_scope = $keyHelper->getScope();
        $this->_scopeId = $keyHelper->getScopeId();
    }

    /**
     * Get AKE domain based on display currency
     *
     * @return string
     */
    private function getEndpointDomain()
    {
        return in_array($this->getConfig('currency/options/default'), ['EUR', 'GBP'])
            ? 'https://payments-eu.amazon.com/'
            : 'https://payments.amazon.com/';
    }

    /**
     * Return register popup endpoint URL
     *
     * @return string
     */
    public function getEndpointRegister()
    {
        return $this->getEndpointDomain() . 'register';
    }

    /**
     * Return pubkey endpoint URL
     *
     * @return string
     */
    public function getEndpointPubkey()
    {
        return $this->getEndpointDomain() . 'register/getpublickey';
    }

    /**
     * Return listener origins
     *
     * @return array
     */
    public function getListenerOrigins()
    {
        return [
            'payments.amazon.com',
            'payments-eu.amazon.com',
            'sellercentral.amazon.com',
            'sellercentral-europe.amazon.com'
        ];
    }

    /**
     * Generate and save RSA keys
     *
     * @return mixed
     */
    protected function generateKeys()
    {
        $keys = $this->keyHelper->generateKeys();
        $encrypt = $this->encryptor->encrypt($keys['privatekey']);

        $this->config
            ->saveConfig(self::CONFIG_XML_PATH_PUBLIC_KEY, $keys['publickey'], 'default', 0)
            ->saveConfig(self::CONFIG_XML_PATH_PRIVATE_KEY, $encrypt, 'default', 0);

        $this->cacheManager->clean([CacheTypeConfig::TYPE_IDENTIFIER]);

        return $keys;
    }

    /**
     * Generate and save auth token
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function generateAuthToken()
    {
        $authToken = $this->mathRandom->getUniqueHash();
        $this->config->saveConfig(self::CONFIG_XML_PATH_AUTH_TOKEN, $authToken, 'default', 0);
        return $authToken;
    }

    /**
     * Delete key-pair from config
     *
     * @return void
     */
    public function destroyKeys()
    {
        $this->config
            ->deleteConfig(self::CONFIG_XML_PATH_PUBLIC_KEY, 'default', 0)
            ->deleteConfig(self::CONFIG_XML_PATH_PRIVATE_KEY, 'default', 0)
            ->deleteConfig(self::CONFIG_XML_PATH_AUTH_TOKEN, 'default', 0);

        $this->cacheManager->clean([CacheTypeConfig::TYPE_IDENTIFIER]);
    }

    /**
     * Return RSA public key
     *
     * @param bool $pemformat
     * @param bool $reset
     * @return mixed|string|string[]|null
     */
    public function getPublicKey($pemformat = false, $reset = false)
    {
        $publickey = $this->scopeConfig->getValue(self::CONFIG_XML_PATH_PUBLIC_KEY, 'default', 0);

        // Generate key pair
        if (!$publickey || $reset || strlen($publickey) < 300) {
            $keys = $this->generateKeys();
            $publickey = $keys['publickey'];
        }

        if (!$pemformat) {
            $pubtrim   = ['-----BEGIN PUBLIC KEY-----', '-----END PUBLIC KEY-----', "\n"];
            $publickey = str_replace($pubtrim, ['','',''], $publickey);
            // Remove binary characters
            $publickey = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $publickey);
        }
        return $publickey;
    }

    /**
     * Return RSA private key
     *
     * @return string
     */
    public function getPrivateKey()
    {
        return $this->encryptor->decrypt($this->scopeConfig->getValue(self::CONFIG_XML_PATH_PRIVATE_KEY, 'default', 0));
    }

    /**
     * Verify and decrypt JSON payload
     *
     * @param string $payloadJson
     * @param bool $autoEnable
     * @param bool $autoSave
     * @return bool
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function decryptPayload($payloadJson, $autoEnable = true, $autoSave = true)
    {
        try {
            $payload = json_decode($payloadJson);

            $publicKeyId = urldecode($payload['publicKeyId'] ?? '');
            $decryptedKey = null;

            $success = openssl_private_decrypt(
                base64_decode($publicKeyId), // phpcs:ignore Magento2.Functions.DiscouragedFunction
                $decryptedKey,
                $this->getPrivateKey()
            );

            if ($success) {
                $config = [
                    'merchant_id' => $payload->merchantId,
                    'store_id' => $payload->storeId,
                    'private_key' => $this->getPrivateKey(),
                    'public_key_id' => $decryptedKey,
                ];
                $this->saveToConfig($config);
                $this->destroyKeys();
            }
            return $success;
        } catch (\Exception $e) {
            $this->logger->critical($e);
            $this->messageManager->addError(__($e->getMessage()));
            $link = 'https://amzn.github.io/amazon-payments-magento-2-plugin/configuration.html';
            $this->messageManager->addError(
                __(
                    "If you experience consistent errors during key transfer " .
                    "click <a href=\"%1\" target=\"_blank\">Amazon Pay for Magento 2</a> for detailed instructions.",
                    $link
                )
            );

        }

        return false;
    }

    /**
     * Save values to Magento config
     *
     * @param mixed $config
     * @param bool $autoEnable
     * @return bool
     */
    public function saveToConfig($config, $autoEnable = true)
    {
        $this->config->saveConfig(
            'payment/amazon_payment_v2/merchant_id',
            $config['merchant_id'],
            $this->_scope,
            $this->_scopeId
        );
        $this->config->saveConfig(
            'payment/amazon_payment_v2/store_id',
            $config['store_id'],
            $this->_scope,
            $this->_scopeId
        );
        $this->config->saveConfig(
            'payment/amazon_payment_v2/private_key',
            $this->encryptor->encrypt($config['private_key']),
            $this->_scope,
            $this->_scopeId
        );
        $this->config->saveConfig(
            'payment/amazon_payment_v2/public_key_id',
            $config['public_key_id'],
            $this->_scope,
            $this->_scopeId
        );

        $currency = $this->getConfig('currency/options/default');
        if (isset($this->_mapCurrencyRegion[$currency])) {
            $this->config->saveConfig(
                'payment/amazon_payment/payment_region',
                $this->_mapCurrencyRegion[$currency],
                $this->_scope,
                $this->_scopeId
            );
        }
        $this->config->saveConfig(
            'payment/amazon_payment/sandbox',
            '0',
            $this->_scope,
            $this->_scopeId
        );

        if ($autoEnable) {
            $this->autoEnable();
        }

        $this->cacheManager->clean([CacheTypeConfig::TYPE_IDENTIFIER]);

        return true;
    }

    /**
     * Auto-enable payment method
     *
     * @return void
     */
    public function autoEnable()
    {
        if (!$this->getConfig('payment/amazon_payment_v2/active')) {
            $this->config->saveConfig('payment/amazon_payment_v2/active', true, $this->_scope, $this->_scopeId);
            $this->messageManager->addSuccessMessage(__("Amazon Pay is now enabled."));
            $this->cacheManager->clean([CacheTypeConfig::TYPE_IDENTIFIER]);
        }
    }

    /**
     * Return listener URL
     *
     * @return string
     */
    public function getReturnUrl()
    {
        $baseUrl = $this->storeManager->getStore($this->_storeId)->getBaseUrl(UrlInterface::URL_TYPE_WEB, true);
        $baseUrl = str_replace('http:', 'https:', $baseUrl);
        $authToken = $this->getConfig(self::CONFIG_XML_PATH_AUTH_TOKEN, 'default', 0);
        if (empty($authToken)) {
            $authToken = $this->generateAuthToken();
        }
        $params  = 'website=' . $this->_websiteId .
            '&store=' . $this->_storeId .
            '&scope=' . $this->_scope .
            '&auth=' . $authToken;
        return $baseUrl . 'amazon_pay/autokeyexchange/listener?' . $params;
    }

    /**
     * Return array of form POST params for Auto Key Exchange sign up
     *
     * @return array
     */
    public function getFormParams()
    {
        // Get redirect URLs and store URL-s
        $urlArray = [];
        $baseUrls = [];
        $stores = $this->storeManager->getStores();
        foreach ($stores as $store) {
            // Get secure base URL
            if ($baseUrl = $store->getBaseUrl(UrlInterface::URL_TYPE_WEB, true)) {
                // phpcs:ignore Magento2.Functions.DiscouragedFunction
                $url = parse_url($baseUrl);
                if (isset($url['host'])) {
                    $baseUrls[] = 'https://' . $url['host'];
                }
            }
            // Get unsecure base URL
            if ($baseUrl = $store->getBaseUrl(UrlInterface::URL_TYPE_WEB, false)) {
                // phpcs:ignore Magento2.Functions.DiscouragedFunction
                $url = parse_url($baseUrl);
                if (isset($url['host'])) {
                    $baseUrls[] = 'https://' . $url['host'];
                }
            }
        }
        $baseUrls = array_unique($baseUrls);

        $moduleVersion = $this->amazonHelper->getModuleVersion();
        if ($moduleVersion == "Read error!") {
            $moduleVersion = '--';
        }

        $currency = $this->getConfig('currency/options/default');

        return [
            'keyShareURL' => $this->getReturnUrl(),
            'publicKey'   => $this->getPublicKey(),
            'locale'      => $this->getConfig('general/locale/code'),
            'source'      => 'SPPL',
            'spId'        => isset($this->_spIds[$currency]) ? $this->_spIds[$currency] : '',
            'onboardingVersion'           => '2',
            'spSoftwareVersion'           => $this->productMeta->getVersion(),
            'spAmazonPluginVersion'       => $moduleVersion,
            'merchantStoreDescription'    => $this->getConfig('general/store_information/name'),
            'merchantLoginDomains[]'      => $baseUrls,
            'merchantPrivacyNoticeURL'    => $baseUrls[0] . '/privacy-policy-cookie-restriction-mode',
        ];
    }

    /**
     * Return config value based on scope and scope ID
     *
     * @param string $path
     * @return mixed
     */
    public function getConfig($path)
    {
        return $this->scopeConfig->getValue($path, $this->_scope, $this->_scopeId);
    }

    /**
     * Return payment region based on currency
     *
     * @return string
     */
    public function getRegion()
    {
        $currency = $this->getCurrency();

        $region = null;
        if ($currency) {
            $region = isset($this->_mapCurrencyRegion[$currency]) ?
                strtoupper($this->_mapCurrencyRegion[$currency]) :
                'DE';
        }

        if ($region == 'DE') {
            $region = 'Euro Region';
        }

        return $region ? $region : 'US';
    }

    /**
     * Return a valid store currency, otherwise return null
     *
     * @return string|null
     */
    public function getCurrency()
    {
        $currency = $this->getConfig('currency/options/default');
        $isCurrencyValid = isset($this->_mapCurrencyRegion[$currency]);
        if (!$isCurrencyValid) {
            if ($this->amazonConfig->isActive($this->_scope, $this->_scopeId)) {
                $isCurrencyValid = $this->amazonConfig->canUseCurrency($currency, $this->_scope, $this->_scopeId);
            } else {
                $isCurrencyValid = in_array(
                    $currency,
                    $this->amazonConfig->getValidCurrencies($this->_scope, $this->_scopeId)
                );
            }
        }
        return $isCurrencyValid ? $currency : null;
    }

    /**
     * Return merchant country
     *
     * @return string
     */
    public function getCountry()
    {
        $co = $this->getConfig('paypal/general/merchant_country');
        return $co ?: 'US';
    }

    /**
     * Validate provided auth token against the one stored in the database
     *
     * @param string $authToken
     * @return bool
     */
    public function validateAuthToken($authToken)
    {
        return $this->getConfig(self::CONFIG_XML_PATH_AUTH_TOKEN, 'default', 0) == $authToken;
    }

    /**
     * Return array of config for JSON Amazon Auto Key Exchange variables.
     *
     * @return array
     */
    public function getJsonAmazonAKEConfig()
    {
        return [
            'co'            => $this->getCountry(),
            'currency'      => $this->getCurrency(),
            'amazonUrl'     => $this->getEndpointRegister(),
            'pollUrl'       => $this->backendUrl->getUrl('amazon_pay/pay/autoKeyExchangePoll'),
            'resetAKEUrl'   => $this->backendUrl->getUrl('amazon_pay/pay/resetAutoKey'),
            'formParams'    => $this->getFormParams(),
            'isMultiCurrencyRegion' => (int) $this->amazonConfig->isMulticurrencyRegion($this->_scope, $this->_scopeId),
        ];
    }
}
