<?php

namespace Amazon\Core\Model\Config;

use Amazon\Core\Helper\Data;
use Magento\Framework\App\State;
use Magento\Framework\App\Cache\Type\Config as CacheTypeConfig;
use Magento\Store\Model\ScopeInterface;
use Magento\Backend\Model\UrlInterface;

class SimplePath
{
    const API_ENDPOINT_DOWNLOAD_KEYS = 'https://payments.amazon.com/register';
    const API_ENDPOINT_GET_PUBLICKEY = 'https://payments.amazon.com/register/getpublickey';

    const PARAM_SP_ID = 'A2K7HE1S3M5XJ';

    const CONFIG_XML_PATH_PRIVATE_KEY = 'payment/amazon_payments/simplepath/privatekey';
    const CONFIG_XML_PATH_PUBLIC_KEY  = 'payment/amazon_payments/simplepath/publickey';

    /**
     * @var \Magento\Backend\Model\UrlInterface
     */
    protected $backendUrl;

    /**
     * SimplePath constructor.
     *
     * @param ScopeConfigInterface $config
     * @param Data                 $coreHelper
     * @param State                $state
     */
    //public function __construct(ScopeConfigInterface $config, Data $coreHelper, State $state, UrlInterface $backendUrl)

    public function __construct(
        \Magento\Framework\App\Config\ConfigResource\ConfigInterface $config,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Backend\Model\UrlInterface $backendUrl,
        \phpseclib\Crypt\RSA $rsa,
        \phpseclib\Crypt\AES $aes,
        \Magento\Framework\App\Cache\Manager $cacheManager,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\App\ResourceConnection $connection,
        \Magento\Framework\Module\ModuleListInterface $moduleList,
        \Magento\Framework\App\ProductMetadataInterface $productMeta,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Psr\Log\LoggerInterface $logger
    )
    {

        $this->config       = $config;
        $this->scopeConfig  = $scopeConfig;
        $this->encryptor    = $encryptor;
        $this->storeManager = $storeManager;
        $this->backendUrl   = $backendUrl;
        $this->rsa          = $rsa;
        $this->aes          = $aes;
        $this->cacheManager = $cacheManager;
        $this->request      = $request;
        $this->connection   = $connection;
        $this->moduleList   = $moduleList;
        $this->productMeta  = $productMeta;
        $this->logger       = $logger;

        $this->messageManager = $messageManager;
    }

    /**
     * Generate and save RSA keys
     */
    public function generateKeys()
    {
        //$keys = $rsa->generateKeys(array('private_key_bits' => 2048, 'privateKeyBits' => 2048, 'hashAlgorithm' => 'sha1'));

        $keys = $this->rsa->createKey(2048);

        $this->config
            ->saveConfig(self::CONFIG_XML_PATH_PUBLIC_KEY, $keys['publickey'], 'default', 0)
            ->saveConfig(self::CONFIG_XML_PATH_PRIVATE_KEY, $this->encryptor->encrypt($keys['privatekey']), 'default', 0);

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
     * @param bool $pemformat  Return key in PEM format
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
            $publickey = str_replace(array('-----BEGIN PUBLIC KEY-----', '-----END PUBLIC KEY-----', "\n"), array('','',''), $publickey);
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
     * @param string $payloadJson
     */
    public function decryptPayload($payloadJson, $autoEnable = true, $autoSave = true)
    {
        try {
          $payload = (object) json_decode($payloadJson);
          $payloadVerify = clone $payload;

          // Unencrypted?
          if (!isset($payload->encryptedKey) && isset($payload->merchant_id, $payload->access_key, $payload->secret_key)) {
              return $this->saveToConfig($payloadJson, $autoEnable);
          }

          // Validate JSON
          if (!isset($payload->encryptedKey, $payload->encryptedPayload, $payload->iv, $payload->sigKeyID, $payload->signature)) {
              throw new  \Magento\Framework\Validator\Exception(__("Unable to import Amazon keys. Please verify your JSON format and values."));
          }

          // URL decode values
          foreach ($payload as $key => $value) {
              $payload->$key = urldecode($value);
          }

          // Retrieve Amazon public key to verify signature
          try {
              $client = new \Zend_Http_Client(self::API_ENDPOINT_GET_PUBLICKEY, array(
                  'maxredirects' => 2,
                  'timeout'      => 30));

              $client->setParameterGet(array('sigkey_id' => $payload->sigKeyID));

              $response = $client->request();
              $amazonPublickey = urldecode($response->getBody());

          } catch (Exception $e) {
              throw new \Magento\Framework\Validator\Exception(__($e->getMessage()));
          }

          // Use raw JSON (without signature or URL decode) as the data to verify signature
          unset($payloadVerify->signature);
          $payloadVerifyJson = json_encode($payloadVerify);

          // Verify signature using Amazon publickey and JSON paylaod
          if ($amazonPublickey && openssl_verify($payloadVerifyJson, base64_decode($payload->signature), $this->key2pem($amazonPublickey), 'SHA256')) {

              // Decrypt Amazon key using own private key
              $decryptedKey = null;
              openssl_private_decrypt(base64_decode($payload->encryptedKey), $decryptedKey, $this->getPrivateKey(), OPENSSL_PKCS1_OAEP_PADDING);

              // Decrypt final payload (AES 128-bit CBC)
              if (function_exists('mcrypt_decrypt')) {
                  $finalPayload = @mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $decryptedKey, base64_decode($payload->encryptedPayload), MCRYPT_MODE_CBC, base64_decode($payload->iv));
              } else {
                  // This uses openssl_decrypt, which may have issues
                  $this->aes->setKey($decryptedKey);
                  $this->aes->setIV(base64_decode($payload->iv, true));
                  $this->aes->setKeyLength(128);
                  $finalPayload = $this->aes->decrypt($payload->encryptedPayload);
              }

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

        } catch (Exception $e) {
            $this->logger->critical($e);
            $this->messageManager->addError(__($e->getMessage()));

            $link = 'https://payments.amazon.com/help/202024240';
            $this->messageManager->addError(__("If you're experiencing consistent errors with transferring keys, click <a href=\"%s\" target=\"_blank\">Manual Transfer Instructions</a> to learn more.", $link));
        }

        return false;
    }

    /**
     * Save values to Mage config
     *
     * @param string $json
     */
    public function saveToConfig($json, $autoEnable = true)
    {
        if ($values = (object) json_decode($json)) {
            foreach ($values as $key => $value) {
              $values->{strtolower($key)} = $value;
            }

            $this->config->saveConfig('payment/amazon_payment/merchant_id', $values->merchant_id, 'default', 0);
            $this->config->saveConfig('payment/amazon_payment/client_id', $values->client_id, 'default', 0);
            $this->config->saveConfig('payment/amazon_payment/client_secret', $this->encryptor->encrypt($values->client_secret), 'default', 0);
            $this->config->saveConfig('payment/amazon_payment/access_key', $values->access_key, 'default', 0);
            $this->config->saveConfig('payment/amazon_payment/secret_key', $this->encryptor->encrypt($values->secret_key), 'default', 0);

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
        if (!$this->scopeConfig->getValue('payment/amazon_payment/pwa_enabled')) {
            $this->config->saveConfig('payment/amazon_payment/pwa_enabled', true, 'default', 0);
            $this->messageManager->addSuccess(__("Login and Pay with Amazon is now enabled."));
        }
    }

    /**
     * Return listener URL
     */
    public function getReturnUrl()
    {
        /*
        //$url = $this->_backendUrl->getUrl('amazon_payments/simplepath', array('_store' => Mage::helper('amazon_payments')->getAdminStoreId(), '_forced_secure' => true));
        $url = $this->_backendUrl->getUrl('amazon_payments/simplepath');
        // $this->_storeManager->getStore()->getBaseUrl()
        // Add index.php
        $baseUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB, true);
        return str_replace($baseUrl, $baseUrl . 'index.php/', $url);
        */

        $baseUrl = $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_WEB, true);
        $baseUrl = str_replace('http:', 'https:', $baseUrl);
        return $baseUrl . 'amazon_core/simplepath/listener';

        //return $this->storeManager->getStore()->getUrl('amazon_core/simplepath/listener', array('_secure' => true, '_forced_secure' => true));
    }

    /**
     * Return simplepath URL
     */
    public function getSimplepathUrl()
    {
        return self::API_ENDPOINT_DOWNLOAD_KEYS . '?returnUrl=' . urlencode($this->getReturnUrl()) .
						'&pub_key=' . urlencode($this->getPublicKey()) .
						'#event/fromSP';
    }

    /**
     * Return array of form POST params for SimplePath sign up
     */
    public function getFormParams()
    {
        // Retrieve store URLs from config table
        $urls = array();
        $db = $this->connection->getConnection();
        $select = $db->select()
            ->from(
                ['c' => 'core_config_data']
            )
            ->where('c.path IN (?)', array('web/unsecure/base_url', 'web/secure/base_url'));

        foreach ($db->fetchAll($select) as $row) {
            $url = parse_url($row['value']);

            if (isset($url['host'])){
                $urls[] = 'https://' . $url['host'];
            }
        }

        $urls = array_unique($urls);


        $version = $this->moduleList->getOne('Amazon_Core');
        $coreVersion = ($version && isset($version['setup_version'])) ? $version['setup_version'] : '--';

        return array(
            'locale' => $this->scopeConfig->getValue('general/country/default'),
            'spId' => self::PARAM_SP_ID,
            'allowedLoginDomains[]' => $urls,
            'spSoftwareVersion' => $coreVersion,
            'spAmazonPluginVersion' => $this->productMeta->getVersion(),
        );
    }

    /**
     * Return array of config for JSON AmazonSp variable.
     */
    public function getJsonAmazonSpConfig()
    {
        return array(
            'amazonUrl'     => $this->getSimplepathUrl(),
            'pollUrl'       => $this->backendUrl->getUrl('amazonsp/simplepath/poll'),
            'spUrl'         => $this->backendUrl->getUrl('amazonsp/simplepath/spurl'),
            'importUrl'     => $this->backendUrl->getUrl('amazonsp/simplepath/import'),
            'isSecure'      => (int) ($this->request->isSecure()),
            'region'        => (int) $this->scopeConfig->getValue('general/country/default'),
            //'isUsa'         => (int) (Mage::helper('amazon_payments')->getAdminConfig() == 'US' && Mage::helper('amazon_payments')->getAdminRegion() != 'eu'),
            'hasOpenssl'    => (int) (extension_loaded('openssl')),
            'formParams'    => $this->getFormParams(),
        );
    }
}
