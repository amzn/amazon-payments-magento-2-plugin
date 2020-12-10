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

namespace Amazon\Core\Model\Config;

use Amazon\Core\Helper\Data as CoreHelper;
use Amazon\Core\Model\AmazonConfig;
use Magento\Framework\App\State;
use Magento\Framework\App\Cache\Type\Config as CacheTypeConfig;
use Magento\Backend\Model\UrlInterface;
use Magento\Payment\Helper\Formatter;
use \phpseclib\Crypt\RSA;
use \phpseclib\Crypt\AES;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 *
 * @deprecated As of February 2021, this Legacy Amazon Pay plugin has been
 * deprecated, in favor of a newer Amazon Pay version available through GitHub
 * and Magento Marketplace. Please download the new plugin for automatic
 * updates and to continue providing your customers with a seamless checkout
 * experience. Please see https://pay.amazon.com/help/E32AAQBC2FY42HS for details
 * and installation instructions.
 */
class SimplePath
{

    const CONFIG_XML_PATH_PRIVATE_KEY = 'payment/amazon_payments/simplepath/privatekey';

    const CONFIG_XML_PATH_PUBLIC_KEY  = 'payment/amazon_payments/simplepath/publickey';

    private $_spIds = [
        'USD' => 'AUGT0HMCLQVX1',
        'GBP' => 'A1BJXVS5F6XP',
        'EUR' => 'A2ZAYEJU54T1BM',
        'JPY' => 'A1MCJZEB1HY93J',
    ];

    private $_mapCurrencyRegion = [
        'EUR' => 'de',
        'USD' => 'us',
        'GBP' => 'uk',
        'JPY' => 'ja',
    ];

    /**
     * @var
     */
    private $_storeId;

    /**
     * @var
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
     * @var CoreHelper
     */
    private $coreHelper;

    /**
     * @var AmazonConfig
     */
    private $amazonConfig;

    /**
     * SimplePath constructor.
     * @param CoreHelper $coreHelper
     * @param AmazonConfig $amazonConfig
     * @param \Magento\Framework\App\Config\ConfigResource\ConfigInterface $config
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\App\ProductMetadataInterface $productMeta
     * @param \Magento\Framework\Encryption\EncryptorInterface $encryptor
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Framework\App\ResourceConnection $connection
     * @param \Magento\Framework\App\Cache\Manager $cacheManager
     * @param \Magento\Framework\App\Request\Http $request
     * @param State $state
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param UrlInterface $backendUrl
     * @param \Magento\Paypal\Model\Config $paypal
     * @param \Psr\Log\LoggerInterface $logger
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        CoreHelper $coreHelper,
        AmazonConfig $amazonConfig,
        \Magento\Framework\App\Config\ConfigResource\ConfigInterface $config,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\ProductMetadataInterface $productMeta,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\App\ResourceConnection $connection,
        \Magento\Framework\App\Cache\Manager $cacheManager,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\App\State $state,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Backend\Model\UrlInterface $backendUrl,
        \Magento\Paypal\Model\Config $paypal,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->coreHelper    = $coreHelper;
        $this->amazonConfig  = $amazonConfig;
        $this->config        = $config;
        $this->scopeConfig   = $scopeConfig;
        $this->productMeta   = $productMeta;
        $this->encryptor     = $encryptor;
        $this->backendUrl    = $backendUrl;
        $this->cacheManager  = $cacheManager;
        $this->connection    = $connection;
        $this->state         = $state;
        $this->request       = $request;
        $this->storeManager  = $storeManager;
        $this->paypal        = $paypal;
        $this->logger        = $logger;

        $this->messageManager = $messageManager;

        // Find store ID and scope
        $this->_websiteId = $request->getParam('website', 0);
        $this->_storeId   = $request->getParam('store', 0);
        $this->_scope     = $request->getParam('scope');

        // Website scope
        if ($this->_websiteId) {
            $this->_scope = !$this->_scope ? 'websites' : $this->_scope;
        } else {
            $this->_websiteId = $storeManager->getWebsite()->getId();
        }

        // Store scope
        if ($this->_storeId) {
            $this->_websiteId = $this->storeManager->getStore($this->_storeId)->getWebsite()->getId();
            $this->_scope = !$this->_scope ? 'stores' : $this->_scope;
        } else {
            $this->_storeId = $storeManager->getWebsite($this->_websiteId)->getDefaultStore()->getId();
        }

        // Set scope ID
        switch ($this->_scope) {
            case 'websites':
                $this->_scopeId = $this->_websiteId;
                break;
            case 'stores':
                $this->_scopeId = $this->_storeId;
                break;
            default:
                $this->_scope = 'default';
                $this->_scopeId = 0;
                break;
        }
    }

    /**
     * Return domain
     */
    private function getEndpointDomain()
    {
        return in_array($this->getConfig('currency/options/default'), ['EUR', 'GBP'])
            ? 'https://payments-eu.amazon.com/'
            : 'https://payments.amazon.com/';
    }

    /**
     * Return register popup endpoint URL
     */
    public function getEndpointRegister()
    {
        return $this->getEndpointDomain() . 'register';
    }

    /**
     * Return pubkey endpoint URL
     */
    public function getEndpointPubkey()
    {
        return $this->getEndpointDomain() . 'register/getpublickey';
    }

    /**
     * Return listener origins
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
     */
    public function generateKeys()
    {
        $rsa = new RSA();
        $keys = $rsa->createKey(2048);
        $encrypt = $this->encryptor->encrypt($keys['privatekey']);

        $this->config
            ->saveConfig(self::CONFIG_XML_PATH_PUBLIC_KEY, $keys['publickey'], 'default', 0)
            ->saveConfig(self::CONFIG_XML_PATH_PRIVATE_KEY, $encrypt, 'default', 0);

        $this->cacheManager->clean([CacheTypeConfig::TYPE_IDENTIFIER]);

        return $keys;
    }

    /**
     * Delete key-pair from config
     */
    public function destroyKeys()
    {
        $this->config
            ->deleteConfig(self::CONFIG_XML_PATH_PUBLIC_KEY, 'default', 0)
            ->deleteConfig(self::CONFIG_XML_PATH_PRIVATE_KEY, 'default', 0);

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
     */
    public function getPrivateKey()
    {
        return $this->encryptor->decrypt($this->scopeConfig->getValue(self::CONFIG_XML_PATH_PRIVATE_KEY, 'default', 0));
    }

    /**
     * Convert key to PEM format for openssl functions
     */
    public function key2pem($key)
    {
        return "-----BEGIN PUBLIC KEY-----\n" .
            chunk_split($key, 64, "\n") .
            "-----END PUBLIC KEY-----\n";
    }

    /**
     * Verify and decrypt JSON payload
     *
     * @param                                        string $payloadJson
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function decryptPayload($payloadJson, $autoEnable = true, $autoSave = true)
    {
        try {
            $payload = (object) json_decode($payloadJson);
            $payloadVerify = clone $payload;

            // Unencrypted via admin
            if ($this->state->getAreaCode() == 'adminhtml' &&
                isset($payload->merchant_id, $payload->access_key, $payload->secret_key)
            ) {
                return $this->saveToConfig($payloadJson, $autoEnable);
            }

            // Validate JSON
            if (!isset($payload->encryptedKey, $payload->encryptedPayload, $payload->iv, $payload->sigKeyID)) {
                throw new \Magento\Framework\Validator\Exception(
                    __(
                        'Unable to import Amazon keys. ' .
                        'Please verify your JSON format and values.'
                    )
                );
            }

            foreach ($payload as $key => $value) {
                $payload->$key = rawurldecode($value);
            }

            // Retrieve Amazon public key to verify signature
            try {
                $client = new \Zend_Http_Client(
                    $this->getEndpointPubkey(),
                    [
                        'maxredirects' => 2,
                        'timeout'      => 30,
                    ]
                );
                $client->setParameterGet(['sigkey_id' => $payload->sigKeyID]);
                $response = $client->request();
                $amazonPublickey = urldecode($response->getBody());
            } catch (\Exception $e) {
                throw new \Magento\Framework\Validator\Exception(__($e->getMessage()));
            }

            // Use raw JSON (without signature or URL decode) as the data to verify signature
            unset($payloadVerify->signature);
            $payloadVerifyJson = json_encode($payloadVerify);

            // Verify signature using Amazon publickey and JSON paylaod
            if ($amazonPublickey &&
                openssl_verify(
                    $payloadVerifyJson,
                    base64_decode($payload->signature),
                    $this->key2pem($amazonPublickey),
                    'SHA256'
                )
            ) {
                // Decrypt Amazon key using own private key
                $decryptedKey = null;
                openssl_private_decrypt(
                    base64_decode($payload->encryptedKey),
                    $decryptedKey,
                    $this->getPrivateKey(),
                    OPENSSL_PKCS1_OAEP_PADDING
                );

                // Decrypt final payload (AES 256-bit CBC)
                $aes = new AES();
                $aes->setKey($decryptedKey);
                $aes->setIV(base64_decode($payload->iv, true));
                $aes->setKeyLength(256);
                $finalPayload = $aes->decrypt(base64_decode($payload->encryptedPayload));

                // Remove binary characters
                $finalPayload = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $finalPayload);

                if (json_decode($finalPayload)) {
                    if ($autoSave) {
                        $this->saveToConfig($finalPayload, $autoEnable);
                        $this->destroyKeys();
                    }
                    return $finalPayload;
                }
            } else {
                throw new \Magento\Framework\Validator\Exception("Unable to verify signature for JSON payload.");
            }
        } catch (\Exception $e) {
            $this->logger->critical($e);
            $this->messageManager->addError(__($e->getMessage()));
            $link = 'https://payments.amazon.com/help/202024240';
            $this->messageManager->addError(
                __(
                    "If you're experiencing consistent errors with transferring keys, " .
                    "click <a href=\"%1\" target=\"_blank\">Manual Transfer Instructions</a> to learn more.",
                    $link
                )
            );
        }

        return false;
    }

    /**
     * Save values to Mage config
     *
     * @param $json
     * @param bool $autoEnable
     * @return bool
     */
    public function saveToConfig($json, $autoEnable = true)
    {
        if ($values = (object) json_decode($json)) {
            foreach ($values as $key => $value) {
                $values->{strtolower($key)} = $value;
            }

            $this->config->saveConfig(
                'payment/amazon_payment/merchant_id',
                $values->merchant_id,
                $this->_scope,
                $this->_scopeId
            );
            $this->config->saveConfig(
                'payment/amazon_payment/client_id',
                $values->client_id,
                $this->_scope,
                $this->_scopeId
            );
            $this->config->saveConfig(
                'payment/amazon_payment/client_secret',
                $this->encryptor->encrypt($values->client_secret),
                $this->_scope,
                $this->_scopeId
            );
            $this->config->saveConfig(
                'payment/amazon_payment/access_key',
                $values->access_key,
                $this->_scope,
                $this->_scopeId
            );
            $this->config->saveConfig(
                'payment/amazon_payment/secret_key',
                $this->encryptor->encrypt($values->secret_key),
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

            if ($autoEnable) {
                $this->autoEnable();
            }

            $this->cacheManager->clean([CacheTypeConfig::TYPE_IDENTIFIER]);

            return true;
        }
    }

    /**
     * Auto-enable payment method
     */
    public function autoEnable()
    {
        if (!$this->getConfig('payment/amazon_payment/active')) {
            $this->config->saveConfig('payment/amazon_payment/active', true, $this->_scope, $this->_scopeId);
            $this->messageManager->addSuccessMessage(__("Login and Pay with Amazon is now enabled."));
        }
    }

    /**
     * Return listener URL
     */
    public function getReturnUrl()
    {
        $baseUrl = $this->storeManager->getStore($this->_storeId)->getBaseUrl(UrlInterface::URL_TYPE_WEB, true);
        $baseUrl = str_replace('http:', 'https:', $baseUrl);
        $params  = 'website=' . $this->_websiteId . '&store=' . $this->_storeId . '&scope=' . $this->_scope;
        return $baseUrl . 'amazon_core/simplepath/listener?' . urlencode($params);
    }

    /**
     * Return array of form POST params for SimplePath sign up
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
                $value = $baseUrl . 'amazon/login/processAuthHash/';
                $urlArray[] = $value;
                $url = parse_url($baseUrl);
                if (isset($url['host'])) {
                    $baseUrls[] = 'https://' . $url['host'];
                }
            }
            // Get unsecure base URL
            if ($baseUrl = $store->getBaseUrl(UrlInterface::URL_TYPE_WEB, false)) {
                $url = parse_url($baseUrl);
                if (isset($url['host'])) {
                    $baseUrls[] = 'https://' . $url['host'];
                }
            }
        }
        $urlArray = array_unique($urlArray);
        $baseUrls = array_unique($baseUrls);

        $coreVersion = $this->coreHelper->getVersion();
        if (!$coreVersion) {
            $coreVersion = '--';
        }

        $currency = $this->getConfig('currency/options/default');

        return [
            'keyShareURL' => $this->getReturnUrl(),
            'publicKey'   => $this->getPublicKey(),
            'locale'      => $this->getConfig('general/locale/code'),
            'source'      => 'SPPL',
            'spId'        => isset($this->_spIds[$currency]) ? $this->_spIds[$currency] : '',
            'spSoftwareVersion'           => $this->productMeta->getVersion(),
            'spAmazonPluginVersion'       => $coreVersion,
            'merchantStoreDescription'    => $this->getConfig('general/store_information/name'),
            'merchantLoginDomains[]'      => $baseUrls,
            'merchantLoginRedirectURLs[]' => $urlArray,
        ];
    }

    /**
     * Return config value based on scope and scope ID
     */
    public function getConfig($path)
    {
        return $this->scopeConfig->getValue($path, $this->_scope, $this->_scopeId);
    }

    /**
     * Return payment region based on currency
     */
    public function getRegion()
    {
        $currency = $this->getCurrency();

        $region = null;
        if ($currency) {
            $region = isset($this->_mapCurrencyRegion[$currency]) ? strtoupper($this->_mapCurrencyRegion[$currency]) : 'DE';
        }


        if ($region == 'DE') {
            $region = 'Euro Region';
        }

        return $region ? $region : 'US';
    }

    /**
     * Return a valid store currency, otherwise return null
     */
    public function getCurrency()
    {
        $currency = $this->getConfig('currency/options/default');
        $isCurrencyValid = isset($this->_mapCurrencyRegion[$currency]);
        if (!$isCurrencyValid) {
            if ($this->getConfig(CoreHelper::AMAZON_ACTIVE, $this->_scope, $this->_scopeId)) {
                $isCurrencyValid = $this->amazonConfig->canUseCurrency($currency, $this->_scope, $this->_scopeId);
            } else {
                $isCurrencyValid = in_array($currency, $this->amazonConfig->getValidCurrencies($this->_scope, $this->_scopeId));
            }
        }
        return $isCurrencyValid ? $currency : null;
    }

    /**
     * Return merchant country
     */
    public function getCountry()
    {
        $co = $this->getConfig('paypal/general/merchant_country');
        return $co ? $co : 'US';
    }

    /**
     * Return array of config for JSON AmazonSp variable.
     */
    public function getJsonAmazonSpConfig()
    {
        return [
            'co'            => $this->getCountry(),
            'region'        => $this->getRegion(),
            'currency'      => $this->getCurrency(),
            'amazonUrl'     => $this->getEndpointRegister(),
            'pollUrl'       => $this->backendUrl->getUrl('amazonsp/simplepath/poll/'),
            'isSecure'      => (int) ($this->request->isSecure()),
            'hasOpenssl'    => (int) (extension_loaded('openssl')),
            'formParams'    => $this->getFormParams(),
            'isMultiCurrencyRegion' => (int) $this->amazonConfig->isMulticurrencyRegion($this->_scope, $this->_scopeId),
        ];
    }
}
