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

namespace Amazon\PayV2\Model\Adapter;

/**
 * Class AmazonPayV2Adapter
 */
class AmazonPayV2Adapter
{
    /**
     * @var \Amazon\PayV2\Client\ClientFactoryInterface
     */
    private $clientFactory;

    /**
     * @var \Amazon\PayV2\Model\AmazonConfig
     */
    private $amazonConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var \Amazon\PayV2\Helper\Data
     */
    private $amazonHelper;

    /**
     * @var \Amazon\PayV2\Logger\Logger
     */
    private $logger;

    /**
     * AmazonPayV2Adapter constructor.
     * @param \Amazon\PayV2\Client\ClientFactoryInterface $clientFactory
     * @param \Amazon\PayV2\Model\AmazonConfig $amazonConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param \Amazon\PayV2\Helper\Data $amazonHelper
     * @param \Amazon\PayV2\Logger\Logger $logger
     */
    public function __construct(
        \Amazon\PayV2\Client\ClientFactoryInterface $clientFactory,
        \Amazon\PayV2\Model\AmazonConfig $amazonConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Amazon\PayV2\Helper\Data $amazonHelper,
        \Amazon\PayV2\Logger\Logger $logger
    ) {
        $this->clientFactory = $clientFactory;
        $this->amazonConfig = $amazonConfig;
        $this->storeManager = $storeManager;
        $this->quoteRepository = $quoteRepository;
        $this->amazonHelper = $amazonHelper;
        $this->logger = $logger;
    }

    /**
     * @return string
     */
    protected function getMerchantCustomInformation()
    {
        return sprintf('Magento Version: 2, Plugin Version: %s (v2)', $this->amazonHelper->getVersion());
    }

    /**
     * @param mixed $amount
     * @param string $currencyCode
     * @return array
     */
    protected function createPrice($amount, $currencyCode)
    {
        switch ($currencyCode) {
            case 'JPY':
                $amount = round($amount);
                break;
            default:
                $amount = (float) $amount;
                break;
        }
        return [
            'amount' => $amount,
            'currencyCode' => $currencyCode,
        ];
    }

    /**
     * Create new Amazon Checkout Session
     *
     * @param $storeId
     * @return mixed
     */
    public function createCheckoutSession($storeId)
    {
        $headers = $this->getIdempotencyHeader();

        $payload = [
            'webCheckoutDetail' => [
                'checkoutReviewReturnUrl' => $this->amazonConfig->getCheckoutReviewUrl(),
            ],
            'storeId' => $this->amazonConfig->getClientId(),
            'platformId' => $this->amazonConfig->getPlatformId(),
        ];

        $response = $this->clientFactory->create($storeId)->createCheckoutSession($payload, $headers);

        return $this->processResponse($response, __FUNCTION__);
    }

    /**
     * Return checkout session details
     *
     * @param $storeId
     * @param $checkoutSessionId
     * @return mixed
     */
    public function getCheckoutSession($storeId, $checkoutSessionId)
    {
        $response = $this->clientFactory->create($storeId)->getCheckoutSession($checkoutSessionId);

        return $this->processResponse($response, __FUNCTION__);
    }

    /**
     * Update Checkout Session to set payment info and transaction metadata
     *
     * @param $quote
     * @param $checkoutSessionId
     * @return mixed
     */
    public function updateCheckoutSession($quote, $checkoutSessionId)
    {
        $storeId = $quote->getStoreId();
        $store = $quote->getStore();

        if (!$quote->getReservedOrderId()) {
            try {
                $quote->reserveOrderId()->save();
            } catch (\Exception $e) {
                $this->logger->debug($e->getMessage());
            }
        }

        $payload = [
            'webCheckoutDetail' => [
                'checkoutResultReturnUrl' => $this->amazonConfig->getCheckoutResultUrl()
            ],
            'paymentDetail' => [
                'paymentIntent' => 'Authorize',
                'canHandlePendingAuthorization' => $this->amazonConfig->canHandlePendingAuthorization(),
                'chargeAmount' => $this->createPrice($quote->getGrandTotal(), $quote->getQuoteCurrencyCode()),
            ],
            'merchantMetadata' => [
                'merchantReferenceId' => $quote->getReservedOrderId(),
                'merchantStoreName' => $this->amazonConfig->getStoreName() ?: $store->getName(),
                'customInformation' => $this->getMerchantCustomInformation(),
            ],
            'platformId' => $this->amazonConfig->getPlatformId(),
        ];

        $response = $this->clientFactory->create($storeId)->updateCheckoutSession($checkoutSessionId, $payload);

        return $this->processResponse($response, __FUNCTION__);
    }

    /**
     * Get charge
     *
     * @param $storeId
     * @param $chargeId
     * @return mixed
     */
    public function getCharge($storeId, $chargeId)
    {
        $response = $this->clientFactory->create($storeId)->getCharge($chargeId);
        return $this->processResponse($response, __FUNCTION__);
    }

    /**
     * Create charge
     *
     * @param $storeId
     * @param $chargePermissionId
     * @param $amount
     * @param $currency
     * @return mixed
     */
    public function createCharge($storeId, $chargePermissionId, $amount, $currency)
    {
        $headers = $this->getIdempotencyHeader();

        $payload = [
            'chargePermissionId' => $chargePermissionId,
            'chargeAmount' => $this->createPrice($amount, $currency),
        ];

        $response = $this->clientFactory->create($storeId)->createCharge($payload, $headers);

        return $this->processResponse($response, __FUNCTION__);
    }

    /**
     * Capture charge
     *
     * @param $storeId
     * @param $chargeId
     * @param $amount
     * @param $currency
     * @return mixed
     */
    public function captureCharge($storeId, $chargeId, $amount, $currency)
    {
        $headers = $this->getIdempotencyHeader();

        $payload = [
            'captureAmount' => $this->createPrice($amount, $currency),
        ];

        $response = $this->clientFactory->create($storeId)->captureCharge($chargeId, $payload, $headers);

        return $this->processResponse($response, __FUNCTION__);
    }

    /**
     * Create refund
     *
     * @param $storeId
     * @param $chargeId
     * @param $amount
     * @param $currency
     * @return mixed
     */
    public function createRefund($storeId, $chargeId, $amount, $currency)
    {
        $headers = $this->getIdempotencyHeader();

        $payload = [
            'chargeId' => $chargeId,
            'refundAmount' => $this->createPrice($amount, $currency),
        ];

        $response = $this->clientFactory->create($storeId)->createRefund($payload, $headers);

        return $this->processResponse($response, __FUNCTION__);
    }

    /**
     * Get refund
     *
     * @param $storeId
     * @param $refundId
     * @return mixed
     */
    public function getRefund($storeId, $refundId)
    {
        $response = $this->clientFactory->create($storeId)->getRefund($refundId);
        return $this->processResponse($response, __FUNCTION__);
    }

    /**
     * Cancel charge
     *
     * @param $storeId
     * @param $chargeId
     */
    public function cancelCharge($storeId, $chargeId, $reason = 'ADMIN VOID')
    {
        $payload = [
            'cancellationReason' => $reason
        ];

        $response = $this->clientFactory->create($storeId)->cancelCharge($chargeId, $payload);

        return $this->processResponse($response, __FUNCTION__);
    }

    /**
     * @param int $storeId
     * @param string $chargePermissionId
     * @param string $reason
     * @param boolean $cancelPendingCharges
     * @return array
     */
    public function closeChargePermission($storeId, $chargePermissionId, $reason, $cancelPendingCharges = false)
    {
        $payload = [
            'closureReason' => $reason,
            'cancelPendingCharges' => $cancelPendingCharges,
        ];

        $response = $this->clientFactory->create($storeId)->closeChargePermission($chargePermissionId, $payload);

        return $this->processResponse($response, __FUNCTION__);
    }

    /**
     * AuthorizeClient and SaleClient Gateway Command
     *
     * @param $data
     */
    public function authorize($data, $captureNow = false)
    {
        $quote = $this->quoteRepository->get($data['quote_id']);
        $response = $this->getCheckoutSession($quote->getStoreId(), $data['amazon_checkout_session_id']);
        if (!empty($response['chargeId'])) {
            // Get charge for async checkout
            $charge = $this->getCharge($quote->getStoreId(), $response['chargeId']);

            if ($captureNow && $charge['statusDetail']['state'] == 'Authorized') {
                $response = $this->captureCharge(
                    $quote->getStoreId(),
                    $response['chargeId'],
                    $quote->getGrandTotal(),
                    $quote->getStore()->getCurrentCurrency()->getCode()
                );
            }
            $response['charge'] = $charge;
        }

        return $response;
    }

    /**
     * Process SDK client response
     *
     * @param $clientResponse
     * @param $functionName
     * @return array
     */
    protected function processResponse($clientResponse, $functionName)
    {
        $response = [];

        if (!isset($clientResponse['response'])) {
            $this->logger->debug(__('Unable to ' . $functionName));
        } else {
            $response = json_decode($clientResponse['response'], true);
        }

        // Add HTTP response status code
        if (isset($clientResponse['status'])) {
            $response['status'] = $clientResponse['status'];
        }

        // Log error
        if (!in_array($response['status'], [200, 201]) && $this->amazonConfig->isLoggingEnabled()) {
            $this->logger->error($functionName . ' ' . $response['status'], $response);
            $debugBackTrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
            $this->logger->debug($functionName . ' backtrace', $debugBackTrace[2]);
        } elseif ($this->amazonConfig->isLoggingDeveloper()) {
            // Log full response if dev
            $debugBackTrace = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 2);
            $this->logger->debug($functionName, $debugBackTrace[1]['args']);
            $this->logger->debug(print_r($response, true));
        }

        return $response;
    }

    /**
     * Generate idempotency header
     *
     * @return array
     */
    protected function getIdempotencyHeader()
    {
        return [
            'x-amz-pay-idempotency-key' => uniqid(),
        ];
    }
}
