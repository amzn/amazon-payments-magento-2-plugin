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

use AmazonPayV2\Client as AmazonClient;

class Alexa
{
    /**
     * @var AlexaConfig
     */
    private $alexaConfig;

    /**
     * @var \Amazon\Core\Model\AmazonConfig
     */
    private $amazonConfig;

    /**
     * @var \Amazon\Core\Logger\AlexaLogger
     */
    private $alexaLogger;

    /**
     * @var \Amazon\Alexa\Model\AlexaCarrierFactory
     */
    private $carrierFactory;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    private $messageManager;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * Alexa constructor.
     * @param AlexaConfig $alexaConfig
     * @param \Amazon\Core\Model\AmazonConfig $amazonConfig
     * @param \Amazon\Alexa\Logger\AlexaLogger $alexaLogger
     * @param \Amazon\Alexa\Model\AlexaCarrierFactory $carrierFactory
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        AlexaConfig $alexaConfig,
        \Amazon\Core\Model\AmazonConfig $amazonConfig,
        \Amazon\Alexa\Logger\AlexaLogger $alexaLogger,
        \Amazon\Alexa\Model\AlexaCarrierFactory $carrierFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->alexaConfig      = $alexaConfig;
        $this->amazonConfig     = $amazonConfig;
        $this->alexaLogger      = $alexaLogger;
        $this->carrierFactory   = $carrierFactory;
        $this->scopeConfig      = $scopeConfig;
        $this->messageManager   = $messageManager;
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
        $result = $this->submitDeliveryTracker(
            $orderReference,
            $track->getTrackNumber(),
            $track->getCarrierCode(),
            $track->getTitle()
        );

        if (isset($result['status'], $result['response'])) {

            if ($this->amazonConfig->isLoggingEnabled()) {
                // @codingStandardsIgnoreStart
                $this->alexaLogger->debug(print_r($result, true));
                // @codingStandardsIgnoreEnd
            }

            $response = json_decode($result['response'], true);

            if (!$response) {
                return;
            }

            if ($result['status'] == '200') {
                $details = $response['deliveryDetails'][0];

                $comment = __(
                    'Amazon Pay has received shipping tracking information for carrier %1 and tracking number %2.',
                    $details['carrierCode'],
                    $details['trackingNumber']
                );

                $shipment->addComment($comment)->save();

                $this->messageManager->addSuccessMessage($comment);

            } else {
                $errorMessage  = __('Alexa Delivery Tracker returned an error:') . ' (' . $result['status'] . ") \n";
                $errorMessage .= !empty($response['reasonCode']) ? $response['reasonCode'] . ': ' : '';
                $errorMessage .= !empty($response['message']) ? $response['message'] : '';

                if (strpos($response['message'], 'missing key') !== false) {
                    $errorMessage = __('Please add the missing Private/Public key value in the Alexa Delivery Notification settings in Amazon Pay to enable Delivery Notifications.');
                }

                if ($this->amazonConfig->isLoggingEnabled()) {
                    $this->alexaLogger->debug($errorMessage);
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
        $publicKeyId = $this->alexaConfig->getAlexaPublicKeyId();
        $privateKey  = $this->alexaConfig->getAlexaPrivateKey();

        if (!$publicKeyId || !$privateKey) {
            $this->messageManager->addNoticeMessage(__('Please add the missing Private/Public key value in the Alexa Delivery Notification settings in Amazon Pay to enable Delivery Notifications.'));
            return;
        }

        $apiConfig = [
            'public_key_id' => $publicKeyId,
            'private_key'   => $privateKey,
            'sandbox'       => false, // deliveryTrackers not available in sandbox mode
            'region'        => $this->amazonConfig->getRegion()
        ];

        $payload = [
            'amazonOrderReferenceId' => $orderReference,
            'deliveryDetails' => [[
                'trackingNumber' => $trackingNumber,
                'carrierCode' => $this->carrierFactory->create()->loadByCarrierTitle($carrierTitle, $carrierCode),
            ]]
        ];

        $result = [];

        try {
            $client = new AmazonClient($apiConfig);
            $result = $client->deliveryTrackers(json_encode($payload));
        } catch (\Exception $e) {
            $this->logger->critical($e);
            $this->alexaLogger->debug($e->getMessage());
            $this->messageManager->addNoticeMessage(__(
                'Unable to submit Alexa Delivery Notification: %1',
                $e->getMessage()
            ));
        }

        return $result;
    }
}
