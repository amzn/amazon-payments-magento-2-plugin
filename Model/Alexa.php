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

namespace Amazon\Pay\Model;

use Amazon\Pay\Client\ClientFactoryInterface;
use Amazon\Pay\Logger\AlexaLogger;
use Amazon\Pay\Helper\Alexa as AlexaHelper;
use Magento\Framework\Phrase;
use Magento\Sales\Model\Order\Payment;
use Magento\Store\Model\ScopeInterface;

class Alexa
{
    /**
     * @var AmazonConfig
     */
    private $amazonConfig;

    /**
     * @var ClientFactoryInterface
     */
    private $clientFactory;

    /**
     * @var Payment\Transaction\Repository
     */
    private $transactionRepository;

    /**
     * @var \Amazon\Pay\Logger\AlexaLogger
     */
    private $alexaLogger;

    /**
     * @var AlexaHelper
     */
    private $alexaHelper;

    /**
     * Alexa constructor
     *
     * @param AmazonConfig $amazonConfig
     * @param ClientFactoryInterface $clientFactory
     * @param Payment\Transaction\Repository $transactionRepository
     * @param AlexaHelper $alexaHelper
     * @param AlexaLogger $alexaLogger
     */
    public function __construct(
        AmazonConfig $amazonConfig,
        ClientFactoryInterface $clientFactory,
        Payment\Transaction\Repository $transactionRepository,
        AlexaHelper $alexaHelper,
        AlexaLogger $alexaLogger
    ) {
        $this->amazonConfig = $amazonConfig;
        $this->clientFactory = $clientFactory;
        $this->transactionRepository = $transactionRepository;
        $this->alexaHelper = $alexaHelper;
        $this->alexaLogger = $alexaLogger;
    }

    /**
     * Issue call through AP SDK
     *
     * @param int|string $storeId
     * @param string $method
     * @param array $arguments
     * @return array
     * @throws \Exception
     */
    protected function apiCall($storeId, $method, $arguments)
    {
        $client = $this->clientFactory->create($storeId, ScopeInterface::SCOPE_STORE);
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        $clientResponse = call_user_func_array([$client, $method], $arguments);

        $response = $this->processResponse($clientResponse, $method);

        $status = isset($response['status']) ? $response['status'] : '';
    
        if ($status != '200') {
            $errorMessage = __('API error:') . ' (' . $status . ') ';
            $errorMessage .= !empty($response['reasonCode']) ? $response['reasonCode'] . ': ' : '';
            $errorMessage .= !empty($response['message']) ? $response['message'] : '';
            throw new \Magento\Framework\Exception\StateException(new Phrase($errorMessage));
        }
        return $response;
    }

    /**
     * Process SDK client response
     *
     * @param mixed $clientResponse
     * @param string $functionName
     * @return array
     */
    protected function processResponse($clientResponse, $functionName)
    {
        $response = [];

        if (!isset($clientResponse['response'])) {
            $this->alexaLogger->debug(__('Unable to ' . $functionName));
        } else {
            $response = json_decode($clientResponse['response'], true);
        }

        // Add HTTP response status code
        if (isset($clientResponse['status'])) {
            $response['status'] = $clientResponse['status'];
        }

        // Log
        $isError = (!isset($response['status']) || !in_array($response['status'], [200, 201]));
        if ($isError || $this->amazonConfig->isLoggingEnabled()) {
            // phpcs:ignore PHPCompatibility.FunctionUse.ArgumentFunctionsReportCurrentValue.NeedsInspection
            $debugBackTrace = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 2);
            $this->alexaLogger->debug($functionName . ' <- ', $debugBackTrace[1]['args']);
            if ($isError) {
                $this->alexaLogger->error($functionName . ' -> ', $response);
            } else {
                $this->alexaLogger->debug($functionName . ' -> ', $response);
            }
        }

        return $response;
    }

    /**
     * Get charge permission ID from Amazon Charge
     *
     * @param \Magento\Sales\Model\Order $order
     * @return string
     */
    protected function getChargePermissionId($order)
    {
        $payment = $order->getPayment();
        /* @var $payment Payment */
        if ($this->amazonConfig->getPaymentAction(ScopeInterface::SCOPE_STORE, $order->getStoreId()) ==
            \Amazon\Pay\Model\Config\Source\PaymentAction::AUTHORIZE) {
            $transationType = Payment\Transaction::TYPE_AUTH;
        } else {
            $transationType = Payment\Transaction::TYPE_CAPTURE;
        }
        $transaction = $this->transactionRepository->getByTransactionType($transationType, $payment->getId());
        if (!$transaction) {
            throw new \Magento\Framework\Exception\NotFoundException(
                new Phrase('Failed to lookup order transaction')
            );
        }
        $txnId = str_replace('-capture', '', $transaction->getTxnId());
        $response = $this->apiCall($order->getStoreId(), 'getCharge', [$txnId]);
        return $response['chargePermissionId'];
    }

    /**
     * Get list of delivery carriers
     *
     * This list is populated from amazon-pay-delivery-tracker-supported-carriers.csv.
     *
     * @return array
     */
    protected function getDeliveryCarriers()
    {
        $result = [];
        $carriers = $this->alexaHelper->getDeliveryCarriers();
        foreach ($carriers as $carrier) {
            $result[$carrier['title']] = $carrier['code'];
        }
        return $result;
    }

    /**
     * Get carrier code from a Shipment Tracking instance
     *
     * @param \Magento\Sales\Model\Order\Shipment\Track $track
     * @return string
     */
    protected function getCarrierCode($track)
    {
        $result = '';
        $carriersMapping = $this->amazonConfig->getCarriersMapping(ScopeInterface::SCOPE_STORE, $track->getStoreId());
        if (array_key_exists($track->getCarrierCode(), $carriersMapping)) {
            $result = $carriersMapping[$track->getCarrierCode()];
        }
        if (empty($result)) {
            $deliveryCarriers = $this->getDeliveryCarriers();
            if (array_key_exists($track->getTitle(), $deliveryCarriers)) {
                $result = $deliveryCarriers[$track->getTitle()];
            }
        }
        if (empty($result)) {
            $result = strtoupper($track->getCarrierCode());
        }
        return $result;
    }

    /**
     * True if Alexa notifications are enabled and AP was used for order payment
     *
     * @param \Magento\Sales\Model\Order\Shipment\Track $track
     * @return bool
     */
    protected function canAddDeliveryNotification($track)
    {
        $result = false;
        if ($this->amazonConfig->isAlexaEnabled(ScopeInterface::SCOPE_STORE, $track->getStoreId())) {
            $result = $track->getShipment()->getOrder()->getPayment()->getMethod() ==
                \Amazon\Pay\Gateway\Config\Config::CODE;
        }
        return $result;
    }

    /**
     * Add alexa notification to shipment
     *
     * @param \Magento\Sales\Model\Order\Shipment\Track $track
     * @return array
     */
    public function addDeliveryNotification($track)
    {
        $result = [];
        if ($this->canAddDeliveryNotification($track)) {
            $chargePermissionId = $this->getChargePermissionId($track->getShipment()->getOrder());
            $carrierCode = $this->getCarrierCode($track);
            if ($carrierCode == 'CUSTOM') {
                $this->alexaLogger->debug('addDeliveryNotification: -> No matched Alexa Notification carrier for ' .
                                          'Carrier title: ' . $track->getTitle() .
                                          'Carrier code: ' . $track->getCarrierCode() .
                                          ' - merchantReferenceId: ' .
                                          $track->getShipment()->getOrder()->getIncrementId());

                throw new \Magento\Framework\Exception\NotFoundException(
                    new Phrase('No matched Alexa Notification carrier for: ' .
                                $track->getCarrierCode() . ' - ' . $track->getTitle())
                );
            }

            $response = $this->apiCall($track->getStoreId(), 'deliveryTrackers', [json_encode([
                'amazonOrderReferenceId' => $chargePermissionId,
                'deliveryDetails' => [[
                    'trackingNumber' => $track->getTrackNumber(),
                    'carrierCode' => $carrierCode,
                ]]
            ])]);
            $result = $response['deliveryDetails'][0];
            $result['carrierTitle'] = $track->getTitle();
        }
        return $result;
    }
}
