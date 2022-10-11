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

namespace Amazon\Pay\Model\Adapter;

use Amazon\Pay\Model\Config\Source\PaymentAction;
use Couchbase\Scope;
use Magento\Checkout\Model\Session as MagentoCheckoutSession;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Quote\Model\Quote;

class AmazonPayAdapter
{
    const PAYMENT_INTENT_CONFIRM = 'Confirm';
    const PAYMENT_INTENT_AUTHORIZE = 'Authorize';
    const PAYMENT_INTENT_AUTHORIZE_WITH_CAPTURE = 'AuthorizeWithCapture';
    const SPC_SYNC_URL_FRAGMENT = 'v2/singlePageCheckoutDetails';
    const SPC_ENABLED_CONFIG = 'payment/amazon_pay/spc_enabled';

    /**
     * @var \Amazon\Pay\Client\ClientFactoryInterface
     */
    private $clientFactory;

    /**
     * @var \Amazon\Pay\Model\AmazonConfig
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
     * @var \Amazon\Pay\Helper\Data
     */
    private $amazonHelper;

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    private $productMetadata;

    /**
     * @var \Amazon\Pay\Logger\Logger
     */
    private $logger;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $url;

    /**
     * @var \Magento\Framework\App\Response\RedirectInterface
     */
    private $redirect;

    protected $scopeConfig;

    protected $magentoCheckoutSession;

    /**
     * AmazonPayAdapter constructor.
     * @param \Amazon\Pay\Client\ClientFactoryInterface $clientFactory
     * @param \Amazon\Pay\Model\AmazonConfig $amazonConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param \Amazon\Pay\Helper\Data $amazonHelper
     * @param \Magento\Framework\App\ProductMetadataInterface $productMetadata
     * @param \Amazon\Pay\Logger\Logger $logger
     * @param \Magento\Framework\UrlInterface $url
     * @param \Magento\Framework\App\Response\RedirectInterface $redirect
     * @param ScopeConfigInterface $scopeConfig
     * @param MagentoCheckoutSession $magentoCheckoutSession
     */
    public function __construct(
        \Amazon\Pay\Client\ClientFactoryInterface $clientFactory,
        \Amazon\Pay\Model\AmazonConfig $amazonConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Amazon\Pay\Helper\Data $amazonHelper,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        \Amazon\Pay\Logger\Logger $logger,
        \Magento\Framework\UrlInterface $url,
        \Magento\Framework\App\Response\RedirectInterface $redirect,
        ScopeConfigInterface $scopeConfig,
        MagentoCheckoutSession $magentoCheckoutSession
    ) {
        $this->clientFactory = $clientFactory;
        $this->amazonConfig = $amazonConfig;
        $this->storeManager = $storeManager;
        $this->quoteRepository = $quoteRepository;
        $this->amazonHelper = $amazonHelper;
        $this->productMetadata = $productMetadata;
        $this->logger = $logger;
        $this->url = $url;
        $this->redirect = $redirect;
        $this->scopeConfig = $scopeConfig;
        $this->magentoCheckoutSession = $magentoCheckoutSession;
    }

    /**
     * @return string
     */
    protected function getMerchantCustomInformation()
    {
        return sprintf(
            'Magento Version: %s, Plugin Version: %s',
            $this->productMetadata->getVersion(),
            $this->amazonHelper->getModuleVersion('Amazon_Pay')
        );
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
                $amount = (float)number_format($amount, 2, '.', '');
                break;
        }
        return [
            'amount' => $amount,
            'currencyCode' => $currencyCode,
        ];
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
     * @param $paymentIntent
     * @return mixed
     */
    public function updateCheckoutSession($quote, $checkoutSessionId, $paymentIntent = self::PAYMENT_INTENT_AUTHORIZE)
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
            'webCheckoutDetails' => [
                'checkoutResultReturnUrl' => $this->amazonConfig->getCheckoutResultReturnUrl()
            ],
            'paymentDetails' => [
                'paymentIntent' => $paymentIntent,
                'canHandlePendingAuthorization' => $this->amazonConfig->canHandlePendingAuthorization(),
                'chargeAmount' => $this->createPrice($quote->getGrandTotal(), $quote->getQuoteCurrencyCode()),
            ],
            'merchantMetadata' => [
                'merchantStoreName' => $this->amazonConfig->getStoreName(),
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
     * @param bool $captureNow
     * @return mixed
     */
    public function createCharge($storeId, $chargePermissionId, $amount, $currency, $captureNow = false)
    {
        $headers = $this->getIdempotencyHeader();

        $payload = [
            'chargePermissionId' => $chargePermissionId,
            'chargeAmount' => $this->createPrice($amount, $currency),
            'captureNow' => $captureNow,
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
     * @param array $headers
     * @return mixed
     */
    public function captureCharge($storeId, $chargeId, $amount, $currency, $headers = [])
    {
        $headers = array_merge($headers, $this->getIdempotencyHeader());

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
     * @param int $storeId
     * @param string $chargePermissionId
     * @return array
     */
    public function getChargePermission(int $storeId, string $chargePermissionId)
    {
        $response = $this->clientFactory->create($storeId)->getChargePermission($chargePermissionId);

        return $this->processResponse($response, __FUNCTION__);
    }

    /**
     * @param int $storeId
     * @param string $chargePermissionId
     * @param array $data
     * @return mixed
     */
    public function updateChargePermission(int $storeId, string $chargePermissionId, array $data)
    {
        $payload = [
            'merchantMetadata' => [
                'merchantReferenceId' => $data['merchantReferenceId']
            ]
        ];

        if (isset($data['merchantStoreName'])) {
            $payload['merchantMetadata']['merchantStoreName'] = $data['merchantStoreName'];
        }

        if (isset($data['customInformation'])) {
            $payload['merchantMetadata']['customInformation'] = $data['customInformation'];
        }

        if (isset($data['noteToBuyer'])) {
            $payload['merchantMetadata']['noteToBuyer'] = $data['noteToBuyer'];
        }

        $response = $this->clientFactory->create($storeId)->updateChargePermission($chargePermissionId, $payload);

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
            'closureReason' => substr($reason, 0, 255),
            'cancelPendingCharges' => $cancelPendingCharges,
        ];

        $response = $this->clientFactory->create($storeId)->closeChargePermission($chargePermissionId, $payload);

        return $this->processResponse($response, __FUNCTION__);
    }

    /**
     * AuthorizeClient and SaleClient Gateway Command
     *
     * @param $data
     * @return array|mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function authorize($data)
    {
        $quote = $this->quoteRepository->get($data['quote_id']);
        if (!empty($data['charge_permission_id'])) {
            $getChargePermissionResponse = $this->getChargePermission(
                $quote->getStoreId(),
                $data['charge_permission_id']
            );
            if ($getChargePermissionResponse['statusDetails']['state'] == "Chargeable") {
                $response = $this->createCharge(
                    $quote->getStoreId(),
                    $data['charge_permission_id'],
                    $data['amount'],
                    $quote->getQuoteCurrencyCode(),
                    true
                );
            } else {
                $this->logger->debug(__('Charge permission not in Chargeable state: ') . $data['charge_permission_id']);
            }
        } elseif (!empty($data['amazon_checkout_session_id'])) {
            $response = $this->getCheckoutSession($quote->getStoreId(), $data['amazon_checkout_session_id']);
        }

        return $response;
    }

    /**
     * @param $storeId
     * @param $sessionId
     * @param $amount
     * @param $currencyCode
     */
    public function completeCheckoutSession($storeId, $sessionId, $amount, $currencyCode)
    {
        $payload = [
            'chargeAmount' => $this->createPrice($amount, $currencyCode),
        ];

        $rawResponse = $this->clientFactory->create($storeId)->completeCheckoutSession(
            $sessionId,
            json_encode($payload)
        );
        return $this->processResponse($rawResponse, __FUNCTION__);
    }

    /**
     * @param $token
     * @return array
     */
    public function getBuyer($token)
    {
        $response = $this->clientFactory
            ->create()
            ->getBuyer($token);

        return $this->processResponse($response, __FUNCTION__);
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

        // Log
        $isError = !in_array($response['status'], [200, 201]);
        if ($isError || $this->amazonConfig->isLoggingEnabled()) {
            $this->logSanitized($functionName, $response, $isError);
        }

        return $response;
    }

    protected function logSanitized($functionName, $response, $isError)
    {
        $debugBackTrace = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 3);
        $buyerKeys = ['buyerId' => '', 'primeMembershipTypes' => '', 'status' => ''];

        if ($functionName == 'getBuyer') {
            $response = array_intersect_key($response, $buyerKeys);
            $debugBackTrace[2]['args'] = [];
        }

        if (isset($response['buyer'])) {
            $response['buyer'] = array_intersect_key($response['buyer'], $buyerKeys);
        }

        unset($response['shippingAddress'], $response['billingAddress']);

        $this->logger->debug($functionName . ' <- ', $debugBackTrace[2]['args']);
        if ($isError) {
            $this->logger->error($functionName . ' -> ', $response);
        } else {
            $this->logger->debug($functionName . ' -> ', $response);
        }
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

    /**
     * Generate login static signature for amazon.Pay.renderButton used by checkout.js
     *
     * @return string
     */
    public function generateLoginButtonPayload()
    {
        $payload = [
            'signInReturnUrl' => $this->getSignInUrl(),
            'signInCancelUrl' => $this->getSignInCancelUrl(),
            'storeId' => $this->amazonConfig->getClientId(),
            'signInScopes' => ['name', 'email'],
        ];

        return json_encode($payload, JSON_UNESCAPED_SLASHES);
    }

    /**
     * Generate checkout static signature for amazon.Pay.renderButton used by checkout.js
     *
     * @return string
     */
    public function generateCheckoutButtonPayload()
    {
        $payload = [
            'webCheckoutDetails' => [
                'checkoutReviewReturnUrl' => $this->amazonConfig->getCheckoutReviewReturnUrl(),
                'checkoutCancelUrl' => $this->getCheckoutCancelUrl(),
            ],
            'storeId' => $this->amazonConfig->getClientId(),
            'scopes' => ['name', 'email', 'phoneNumber', 'billingAddress'],
        ];

        if ($deliverySpecs = $this->amazonConfig->getDeliverySpecifications()) {
            $payload['deliverySpecifications'] = $deliverySpecs;
        }

        // Add to the payload when using SPC
        if ($this->scopeConfig->isSetFlag(self::SPC_ENABLED_CONFIG)) {
            // Always use Authorize for now, so that async transactions are handled properly
            $paymentIntent = self::PAYMENT_INTENT_AUTHORIZE;
            $quote = $this->magentoCheckoutSession->getQuote();
            $currencyCode = $quote->getQuoteCurrencyCode();

            $payload['chargePermissionType'] = 'OneTime'; // probably needs to be dynamic if buying a subscription (feature not released)
            $payload['platformId'] = $this->amazonConfig->getPlatformId();
            $payload['merchantMetadata'] = [
                'merchantReferenceId' => $quote->getReservedOrderId(),
                'merchantStoreReferenceId' => $quote->getStore()->getCode(),
                'merchantStoreName' => $this->amazonConfig->getStoreName(),
                'customInformation' => $this->getMerchantCustomInformation(),
            ];
            $payload['paymentDetails'] = [
                'paymentIntent' => $paymentIntent,
                'canHandlePendingAuthorization' => $this->amazonConfig->canHandlePendingAuthorization(),

            ];
            $payload['cartDetails'] = [
                'cartId' => $quote->getId(),
            ];
        }

        return json_encode($payload, JSON_UNESCAPED_SLASHES);
    }

    public function generatePayNowButtonPayload(Quote $quote, $paymentIntent = PaymentAction::AUTHORIZE)
    {
        // Always use Authorize for now, so that async transactions are handled properly
        $paymentIntent = self::PAYMENT_INTENT_AUTHORIZE;
        $currencyCode = $quote->getQuoteCurrencyCode();

        $payload = [
            'webCheckoutDetails' => [
                'checkoutMode' => 'ProcessOrder',
                'checkoutResultReturnUrl' => $this->amazonConfig->getPayNowResultUrl(),
                'checkoutCancelUrl' => $this->getCheckoutCancelUrl(),
            ],
            'storeId' => $this->amazonConfig->getClientId(),
            'scopes' => ['name', 'email', 'phoneNumber', 'billingAddress'],
            'paymentDetails' => [
                'paymentIntent' => $paymentIntent,
                'canHandlePendingAuthorization' => $this->amazonConfig->canHandlePendingAuthorization(),
                'chargeAmount' => $this->createPrice($quote->getGrandTotal(), $currencyCode),
                'presentmentCurrency' => $currencyCode,
            ],
            'merchantMetadata' => [
                'merchantReferenceId' => $quote->getReservedOrderId(),
                'merchantStoreName' => $this->amazonConfig->getStoreName(),
                'customInformation' => $this->getMerchantCustomInformation(),
            ],
        ];

        $address = $quote->getShippingAddress();
        if (!empty($address->getPostcode())) {
            $addressData = [
                'name' => $address->getName(),
                'city' => $address->getCity(),
                'postalCode' => $address->getPostcode(),
                'countryCode' => $address->getCountry(),
                'phoneNumber' => $address->getTelephone(),
            ];
            // do not submit stateOrRegion for these EU countries as it will fail validation, see EU tab on
            // https://developer.amazon.com/docs/amazon-pay-checkout/address-formatting-and-validation.html
            if (!in_array($address->getCountry(), ['UK', 'GB', 'SG', 'AE', 'MX'])) {
                $addressData['stateOrRegion'] = $address->getRegionCode();
            }
            foreach ($address->getStreet() as $index => $streetLine) {
                $addressKey = 'addressLine' . ($index + 1);
                $addressData[$addressKey] = $streetLine;
            }

            // Remove empty fields, or ones that contain only "-"
            $addressData = array_filter($addressData, function ($val) {
                return !empty($val) && $val != "-";
            });

            // Make sure phone number is set for PayNow button
            if (!array_key_exists('phoneNumber', $addressData)) {
                $addressData['phoneNumber'] = "0";
            }

            $payload['addressDetails'] = $addressData;
        }

        return json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    public function signButton($payload, $storeId = null)
    {
        return $this->clientFactory->create($storeId)->generateButtonSignature($payload);
    }

    protected function getCheckoutCancelUrl()
    {
        $checkoutCancelUrl = $this->amazonConfig->getCheckoutCancelUrl();
        if (empty($checkoutCancelUrl)) {
            return $this->getDefaultCancelUrl();
        }

        return $this->url->getUrl($checkoutCancelUrl);
    }

    protected function getSignInCancelUrl()
    {
        $signInCancelUrl = $this->amazonConfig->getSignInCancelUrl();
        if (empty($signInCancelUrl)) {
            return $this->getDefaultCancelUrl();
        }

        return $this->url->getUrl($signInCancelUrl);
    }

    protected function getDefaultCancelUrl()
    {
        $referer = $this->redirect->getRefererUrl();
        if ($referer == $this->url->getUrl('checkout')) {
            return $this->url->getUrl('checkout/cart');
        }

        return $referer;
    }

    protected function getSignInUrl()
    {
        $signInUrl = $this->amazonConfig->getSignInResultUrlPath();
        return $this->url->getUrl($signInUrl);
    }

    /**
     * @param int $storeId
     * @param string $payload
     * @param $headers
     * @return array
     */
    public function spcSyncTokens(int $storeId, string $payload, $headers = null)
    {
        $response = $this->clientFactory->create($storeId)->apiCall(
            'POST',
            self::SPC_SYNC_URL_FRAGMENT,
            $payload,
            $headers
        );

        return $this->processResponse($response, __FUNCTION__);
    }
}
