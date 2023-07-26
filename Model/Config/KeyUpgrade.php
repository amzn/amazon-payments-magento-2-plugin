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

use Laminas\Uri\Uri;
use Magento\Backend\Model\UrlInterface;
use Magento\Framework\App\Config\ConfigResource\ConfigInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Store\Model\StoreManagerInterface;
use Amazon\Pay\Api\KeyUpgradeInterface;
use Amazon\Pay\Helper\Key as KeyHelper;
use Amazon\Pay\Logger\Logger;
use Amazon\Pay\Model\AmazonConfig;

class KeyUpgrade implements KeyUpgradeInterface
{
    public const ACTION = 'GetPublicKeyId';

    public const SIGNATURE_METHOD = 'HmacSHA256';

    public const SIGNATURE_VERSION = '2';

    /**
     * @var string
     */
    private $_scope;

    /**
     * @var int
     */
    private $_scopeId;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var AmazonConfig
     */
    private $amazonConfig;

    /**
     * @var KeyHelper
     */
    private $keyHelper;

    /**
     * @var UrlInterface
     */
    private $backendUrl;

    /**
     * @var Curl
     */
    private $curl;

    /**
     * @var EncryptorInterface
     */
    private $encryptor;

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var array
     */
    private $keys;

    /**
     * KeyUpgrade constructor
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param AmazonConfig $amazonConfig
     * @param KeyHelper $keyHelper
     * @param UrlInterface $backendUrl
     * @param Curl $curl
     * @param EncryptorInterface $encryptor
     * @param ConfigInterface $config
     * @param Logger $logger
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        AmazonConfig $amazonConfig,
        KeyHelper $keyHelper,
        UrlInterface $backendUrl,
        Curl $curl,
        EncryptorInterface $encryptor,
        ConfigInterface $config,
        Logger $logger
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->amazonConfig = $amazonConfig;
        $this->keyHelper = $keyHelper;
        $this->backendUrl = $backendUrl;
        $this->curl = $curl;
        $this->encryptor = $encryptor;
        $this->config = $config;
        $this->logger = $logger;
        $this->_scope = $keyHelper->getScope();
        $this->_scopeId = $keyHelper->getScopeId();
    }

    /**
     * @inheritDoc
     */
    public function getPublicKeyId(
        string $scopeType,
        int $scopeCode,
        string $accessKey
    ) {
        if (isset($this->keys) && !empty($this->keys)) {
            $this->resetKeys();
        }

        $sellerId = $this->scopeConfig->getValue(
            'payment/amazon_payment/merchant_id',
            $scopeType,
            $scopeCode
        );

        $secretKey = $this->encryptor->decrypt($this->scopeConfig->getValue(
            'payment/amazon_payment/secret_key',
            $scopeType,
            $scopeCode
        ));

        $serviceUrl = $this->getServiceUrl($scopeType, $scopeCode);
        if (empty($serviceUrl)) {
            $this->logger->debug('Invalid Amazon Pay region detected in legacy credentials. ' .
                'Unable to perform automatic key upgrade.');
            return '';
        }

        $resourcePathPrefix = $this->getReleaseMode();
        $resourcePath = $resourcePathPrefix . '/v2/publicKeyId';

        $timestamp = gmdate("Y-m-d\TH:i:s\\Z", time());
        $params = [
            'AWSAccessKeyId' => $accessKey,
            'Action' => self::ACTION,
            'SellerId' => $sellerId,
            'SignatureMethod' => self::SIGNATURE_METHOD,
            'SignatureVersion' =>self::SIGNATURE_VERSION,
            'Timestamp' => $timestamp
        ];

        $stringToSign = $this->generateStringToSign($params, $serviceUrl, $resourcePath);
        $signature = base64_encode(hash_hmac('sha256', $stringToSign, $secretKey, true));
        $publicKey = str_replace("\r", '', $this->getKeyPair()['publickey']);

        $request = $this->createCurlRequest($serviceUrl, $resourcePath, $params, $signature, $publicKey);

        $this->curl->setOption(CURLOPT_RETURNTRANSFER, 1);
        try {
            $this->curl->get($request);
        } catch (\Exception $e) {
            $this->logger->error('Unable to successfully request a key upgrade: ' . $e->getMessage());
            return '';
        }
        $output = $this->curl->getBody();

        $responseObj = json_decode($output);
        $publicKeyId = $responseObj->publicKeyId ?? '';
        if (empty($publicKeyId)) {
            $this->logger->debug('Unable to automatically upgrade public key for Merchant ID ' . $sellerId .
                ', Access Key ' . $accessKey . ': ' . json_encode($responseObj));
        }

        return $publicKeyId;
    }

    /**
     * @inheritDoc
     */
    public function getKeyPair()
    {
        if (empty($this->keys)) {
            $this->keys = $this->keyHelper->generateKeys();
        }

        return $this->keys;
    }

    /**
     * Return true if this scope still has a CV1 access key set
     *
     * @return mixed
     */
    public function getMwsKeyForScope()
    {
        $accessKey = $this->scopeConfig->getValue(
            'payment/amazon_payment/access_key',
            $this->_scope,
            $this->_scopeId
        );

        return $accessKey;
    }

    /**
     * Check if V2 public key ID already exists
     *
     * @return mixed
     */
    public function getExistingPublicKeyId()
    {
        return $this->amazonConfig->getPublicKeyId($this->_scope, $this->_scopeId);
    }

    /**
     * @inheritDoc
     */
    public function updateKeysInConfig(
        $publicKeyId,
        $scopeType,
        $scopeId
    ) {
        //Save new public key and remove old access key
        $this->config->saveConfig(
            'payment/amazon_payment_v2/public_key_id',
            $publicKeyId,
            $scopeType,
            $scopeId
        );

        $this->config->deleteConfig(
            'payment/amazon_payment/access_key',
            $scopeType,
            $scopeId
        );

        // Save private key
        $keyPair = $this->getKeyPair();
        $privateKeyValue = $this->encryptor->encrypt($keyPair['privatekey']);
        $this->config->saveConfig(
            'payment/amazon_payment_v2/private_key',
            $privateKeyValue,
            $scopeType,
            $scopeId
        );
    }

    /**
     * Generate config object for manual upgrade from admin section
     *
     * @return array
     */
    public function getJsonAmazonKeyUpgradeConfig()
    {
        return [
            'scope'         => $this->_scope,
            'scopeCode'     => $this->_scopeId,
            'accessKey'     => $this->getMwsKeyForScope(),
            'keyUpgradeUrl' => $this->backendUrl->getUrl('amazon_pay/pay/manualKeyUpgrade')
        ];
    }

    /**
     * Empty keypair for new public key ID request
     *
     * @return void
     */
    private function resetKeys()
    {
        $this->keys = [];
    }

    /**
     * Get request domain based on AP region, e.g. 'pay-api.amazon.eu'
     *
     * @param string $scopeType
     * @param int $scopeCode
     * @return string
     */
    private function getServiceUrl(string $scopeType, int $scopeCode)
    {
        $urlMap = [
            'us' => 'pay-api.amazon.com',
            'de' => 'pay-api.amazon.eu',
            'uk' => 'pay-api.amazon.eu',
            'jp' => 'pay-api.amazon.jp'
        ];
        $region = $this->amazonConfig->getRegion($scopeType, $scopeCode);

        return $urlMap[$region];
    }

    /**
     * Get resource path prefix based on release mode (live or sandbox)
     *
     * @return string
     */
    private function getReleaseMode()
    {
        // Live is currently the only available path
        return '/live';
    }

    /**
     * Create the request string from which a signature will be calculated
     *
     * @param array $params
     * @param string $serviceUrl
     * @param string $resourcePath
     * @return string
     */
    private function generateStringToSign(array &$params, string $serviceUrl, string $resourcePath)
    {
        ksort($params, SORT_NUMERIC);

        $uri = new Uri($serviceUrl);
        $stringToSign = 'GET' . PHP_EOL
            . $uri->getPath() . PHP_EOL
            . $resourcePath . PHP_EOL
            . http_build_query($params, "", "&", PHP_QUERY_RFC3986);

        return $stringToSign;
    }

    /**
     * Create a request executable by a CurlHandle
     *
     * @param string $domain
     * @param string $resourcePath
     * @param array $params
     * @param string $signature
     * @param string $publicKey
     * @return string
     */
    private function createCurlRequest(
        string $domain,
        string $resourcePath,
        array $params,
        string $signature,
        string $publicKey
    ) {
        $params['PublicKey'] = $publicKey;
        $params['Signature'] = $signature;

        $queryString = http_build_query($params, '', '&', PHP_QUERY_RFC3986);

        $request = $domain
            . $resourcePath
            . '?' . str_replace('SellerId', 'MerchantId', $queryString);

        return 'https://' . $request;
    }
}
