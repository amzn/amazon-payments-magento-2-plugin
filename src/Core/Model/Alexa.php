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

namespace Amazon\Core\Model;

use Magento\Framework\App\Cache\Type\Config as CacheTypeConfig;
use phpseclib\Crypt\RSA;
use AmazonPayV2\Client as AmazonClient;

class Alexa
{
    /**
     * Mappings of carrier titles to codes
     */
    const CSV = 'files/amazon-pay-delivery-tracker-supported-carriers.csv';

    /**
     * @var \Amazon\Core\Helper\Data
     */
    protected $amazonCoreHelper;

    /**
     * @var \Amazon\Core\Logger\AlexaLogger
     */
    protected $alexaLogger;

    /**
     * @var \Magento\Framework\File\Csv
     */
    protected $csv;

    /**
     * @var \Magento\Framework\App\Config\ConfigResource\ConfigInterface
     */
    protected $config;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\App\Cache\Manager
     */
    protected $cacheManager;

    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    protected $encryptor;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var \Magento\Framework\Module\Dir\Reader
     */
    protected $moduleReader;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * Map of carrier titles to codes
     */
    private $carriers = [];

    /**
     * Config constructor.
     *
     * @param \Amazon\Core\Helper\Data $amazonCoreHelper
     * @param \Amazon\Core\Logger\AlexaLogger $alexaLogger
     * @param \Magento\Framework\File\Csv $csv
     * @param \Magento\Framework\App\Config\ConfigResource\ConfigInterface $config
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\App\Cache\Manager $cacheManager
     * @param \Magento\Framework\Encryption\EncryptorInterface $encryptor
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Framework\Module\Dir\Reader $moduleReader
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Amazon\Core\Helper\Data $amazonCoreHelper,
        \Amazon\Core\Logger\AlexaLogger $alexaLogger,
        \Magento\Framework\File\Csv $csv,
        \Magento\Framework\App\Config\ConfigResource\ConfigInterface $config,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\Cache\Manager $cacheManager,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\Module\Dir\Reader $moduleReader,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->amazonCoreHelper = $amazonCoreHelper;
        $this->alexaLogger      = $alexaLogger;
        $this->csv              = $csv;
        $this->config           = $config;
        $this->storeManager     = $storeManager;
        $this->scopeConfig      = $scopeConfig;
        $this->cacheManager     = $cacheManager;
        $this->encryptor        = $encryptor;
        $this->messageManager   = $messageManager;
        $this->moduleReader     = $moduleReader;
        $this->logger           = $logger;
    }

    /**
     * Add Alexa delivery notification
     *
     * @param $track \Magento\Sales\Model\Order\Shipment\Track
     * @return $result array
     */
    public function addDeliveryNotification($track)
    {
        /** @var \Magento\Sales\Model\Order\Shipment $shipment */
        $shipment = $track->getShipment();
        /** @var \Magento\Sales\Model\Order $shipment */
        $order = $shipment->getOrder();

        /** @var \Amazon\Payment\Model\OrderLink $orderLink */
        $orderLink = $order->getExtensionAttributes()->getAmazonOrderReferenceId();
        $orderReference = $orderLink->getAmazonOrderReferenceId();

        // Send to Amazon API
        $result = $this->submitDeliveryTracker($orderReference, $track->getTrackNumber(), $track->getCarrierCode(),
            $track->getTitle());

        if (!empty($result['status'])) {
            $response = json_decode($result['response'], true);

            if ($this->amazonCoreHelper->isLoggingEnabled()) {
                $this->alexaLogger->debug(print_r($result, true));
            }

            if ($result['status'] == '200') {
                $details = $response['deliveryDetails'][0];

                $comment = __('Amazon Pay has received shipping tracking information for carrier %1 and tracking number %2.',
                    $details['carrierCode'], $details['trackingNumber']);

                $shipment->addComment($comment)->save();

                $this->messageManager->addSuccessMessage($comment);

            } else {
                $errorMessage  = __('Alexa Delivery Tracker returned an error:') . ' (' . $result['status'] . ") \n";
                $errorMessage .= !empty($response['reasonCode']) ? $response['reasonCode'] . ': ' : '';
                $errorMessage .= !empty($response['message']) ? $response['message'] : '';

                if (strpos($response['message'], 'missing key') !== false) {
                    $errorMessage = __('Please add the missing Private/Public key value in the Alexa Delivery Notification settings in Amazon Pay to enable Delivery Notifications.');
                }

                $this->messageManager->addNoticeMessage($errorMessage);
            }
        }

        return $result;
    }

    /**
     * Submit delivery tracker payload to Amazon API
     *
     * @param $orderReference string
     * @param $trackingNumber string
     * @param $carrierCode string
     */
    public function submitDeliveryTracker($orderReference, $trackingNumber, $carrierCode, $carrierTitle = '')
    {
        $publicKeyId = $this->amazonCoreHelper->getAlexaPublicKeyId();
        $privateKey  = $this->amazonCoreHelper->getAlexaPrivateKey();

        if (!$publicKeyId || !$privateKey) {
            $this->messageManager->addNoticeMessage(__('Please add the missing Private/Public key value in the Alexa Delivery Notification settings in Amazon Pay to enable Delivery Notifications.'));
            return;
        }

        $apiConfig = [
            'public_key_id' => $publicKeyId,
            'private_key'   => $privateKey,
            'sandbox'       => false, // deliveryTrackers not available in sandbox mode
            'region'        => $this->amazonCoreHelper->getRegion()
        ];

        $payload = [
            'amazonOrderReferenceId' => $orderReference,
            'deliveryDetails' => [[
                'trackingNumber' => $trackingNumber,
                'carrierCode' => $this->mapCarrierCode($carrierCode, $carrierTitle),
            ]]
        ];

        $result = [];

        try {
            $client = new AmazonClient($apiConfig);
            $result = $client->deliveryTrackers(json_encode($payload));
        } catch (\Exception $e) {
            $this->logger->critical($e);
            $this->alexaLogger->debug($e->getMessage());
            $this->messageManager->addNoticeMessage(__('Unable to submit Alexa Delivery Notification: %1',
                $e->getMessage()));
        }

        return $result;
    }

    /**
     * Return carrier code
     */
    private function mapCarrierCode($code, $title = '')
    {
        // Map carrier titles to codes
        if (empty($this->carriers)) {
            $fileDir = $this->moduleReader->getModuleDir(
                \Magento\Framework\Module\Dir::MODULE_ETC_DIR,
                'Amazon_Core'
            );

            try {
                $this->carriers = $this->csv->getDataPairs($fileDir . DIRECTORY_SEPARATOR . self::CSV);
                $this->carriers = array_change_key_case($this->carriers, CASE_LOWER);
            } catch (\Exception $e) {
                $this->logger->critical($e);
            }
        }

        if (isset($this->carriers[strtolower($title)])) {
            return $this->carriers[strtolower($title)];
        }

        if (stripos($code, 'usps') !== false) {
            return 'USPS';
        }

        if (stripos($code, 'ups') !== false) {
            return 'UPS';
        }

        if (stripos($code, 'fedex') !== false) {
            return 'FEDEX';
        }
        return strtoupper($code);
    }

    /**
     * Generate and save new public/private keys
     */
    public function generateKeys()
    {
        $rsa = new RSA();
        $keys = $rsa->createKey(2048);
        $encrypt = $this->encryptor->encrypt($keys['privatekey']);

        $this->config
            ->saveConfig('payment/amazon_payment/alexa_public_key', $keys['publickey'], 'default', 0)
            ->saveConfig('payment/amazon_payment/alexa_private_key', $encrypt, 'default', 0);

        $this->cacheManager->clean([CacheTypeConfig::TYPE_IDENTIFIER]);
    }
}
