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
use Magento\Framework\Module\Dir;
use Magento\Framework\Phrase;
use Magento\Framework\Serialize\SerializerInterface;
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
     * @var Dir
     */
    private $moduleDir;

    /**
     * @var \Magento\Framework\File\Csv
     */
    private $csv;

    /**
     * @var \Magento\Framework\Config\CacheInterface
     */
    private $cache;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @param AmazonConfig $amazonConfig
     * @param ClientFactoryInterface $clientFactory
     * @param Payment\Transaction\Repository $transactionRepository
     * @param Dir $moduleDir
     * @param \Magento\Framework\File\Csv $csv
     */
    public function __construct(
        AmazonConfig $amazonConfig,
        ClientFactoryInterface $clientFactory,
        Payment\Transaction\Repository $transactionRepository,
        Dir $moduleDir,
        \Magento\Framework\File\Csv $csv,
        \Magento\Framework\Config\CacheInterface $cache,
        SerializerInterface $serializer
    ) {
        $this->amazonConfig = $amazonConfig;
        $this->clientFactory = $clientFactory;
        $this->transactionRepository = $transactionRepository;
        $this->moduleDir = $moduleDir;
        $this->csv = $csv;
        $this->cache = $cache;
        $this->serializer = $serializer;
    }

    /**
     * @param int $storeId
     * @param string $method
     * @param array $arguments
     * @return array
     * @throws \Exception
     */
    protected function apiCall($storeId, $method, $arguments)
    {
        $client = $this->clientFactory->create($storeId, ScopeInterface::SCOPE_STORE);
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        $data = call_user_func_array([$client, $method], $arguments);
        $status = $data['status'];
        $response = json_decode($data['response'], true);
        if ($status != '200') {
            $errorMessage = __('API error:') . ' (' . $status . ') ';
            $errorMessage .= !empty($response['reasonCode']) ? $response['reasonCode'] . ': ' : '';
            $errorMessage .= !empty($response['message']) ? $response['message'] : '';
            throw new \Magento\Framework\Exception\StateException(new Phrase($errorMessage));
        }
        return $response;
    }

    /**
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
     * @return array
     */
    protected function fetchDeliveryCarriers()
    {
        $result = [];
        $fileName = implode(DIRECTORY_SEPARATOR, [
            $this->moduleDir->getDir('Amazon_Pay', Dir::MODULE_ETC_DIR),
            'files',
            'amazon-pay-delivery-tracker-supported-carriers.csv'
        ]);
        foreach ($this->csv->getData($fileName) as $row) {
            list($carrierTitle, $carrierCode) = $row;
            $result[$carrierTitle] = $carrierCode;
        }
        return $result;
    }

    /**
     * @return array
     */
    protected function getDeliveryCarriers()
    {
        $cacheKey = hash('sha256', __METHOD__);
        $result = $this->cache->load($cacheKey);
        if ($result) {
            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            $result = $this->serializer->unserialize(gzuncompress($result));
        }
        if (!$result) {
            $result = $this->fetchDeliveryCarriers();
            $this->cache->save(
                // phpcs:ignore Magento2.Functions.DiscouragedFunction
                gzcompress($this->serializer->serialize($result)),
                $cacheKey
            );
        }
        return $result;
    }

    /**
     * @param \Magento\Sales\Model\Order\Shipment\Track $track
     * @return string
     */
    protected function getCarrierCode($track)
    {
        $result = '';
        $deliveryCarriers = $this->getDeliveryCarriers();
        if (isset($deliveryCarriers[$track->getTitle()])) {
            $result = $deliveryCarriers[$track->getTitle()];
        }
        if (empty($result)) {
            foreach (['usps', 'ups', 'fedex'] as $carrierCode) {
                if (stripos($track->getCarrierCode(), $carrierCode) !== false) {
                    $result = strtoupper($carrierCode);
                    break;
                }
            }
        }
        if (empty($result)) {
            $result = strtoupper($track->getCarrierCode());
        }
        return $result;
    }

    /**
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
     * @param \Magento\Sales\Model\Order\Shipment\Track $track
     * @return array
     */
    public function addDeliveryNotification($track)
    {
        $result = [];
        if ($this->canAddDeliveryNotification($track)) {
            $chargePermissionId = $this->getChargePermissionId($track->getShipment()->getOrder());
            $carrierCode = $this->getCarrierCode($track);
            $response = $this->apiCall($track->getStoreId(), 'deliveryTrackers', [json_encode([
                'amazonOrderReferenceId' => $chargePermissionId,
                'deliveryDetails' => [[
                    'trackingNumber' => $track->getTrackNumber(),
                    'carrierCode' => $carrierCode,
                ]]
            ])]);
            $result = $response['deliveryDetails'][0];
        }
        return $result;
    }
}
