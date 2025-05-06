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

use Amazon\Pay\Api\Data\CheckoutSessionInterface;
use Amazon\Pay\Gateway\Config\Config;
use Amazon\Pay\Helper\Session;
use Amazon\Pay\Model\Config\Source\AuthorizationMode;
use Amazon\Pay\Model\Config\Source\PaymentAction;
use Amazon\Pay\Helper\Customer as CustomerHelper;
use Amazon\Pay\Model\Customer\CompositeMatcher as Matcher;
use Amazon\Pay\Api\Data\AmazonCustomerInterface;
use Amazon\Pay\Model\Exception\OrderFailureException;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Framework\Webapi\Exception as WebapiException;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Framework\Encryption\Encryptor;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order\Payment;
use Magento\Quote\Model\MaskedQuoteIdToQuoteIdInterface;
use Magento\Sales\Api\Data\TransactionInterface as Transaction;
use Magento\Vault\Api\PaymentTokenRepositoryInterface;
use Magento\Vault\Api\PaymentTokenManagementInterface;
use Magento\Vault\Model\PaymentTokenFactory;
use Magento\Integration\Model\Oauth\TokenFactory as TokenModelFactory;
use Magento\Authorization\Model\UserContextInterface as UserContext;
use Magento\Framework\Phrase\Renderer\Translate as Translate;
use Magento\SalesRule\Model\Coupon\UpdateCouponUsages;

class CheckoutSessionManagement implements \Amazon\Pay\Api\CheckoutSessionManagementInterface
{
    protected const GENERIC_COMPLETE_CHECKOUT_ERROR_MESSAGE = 'Unable to complete Amazon Pay checkout.';

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Quote\Model\QuoteIdMaskFactory
     */
    private $quoteIdMaskFactory;

    /**
     * @var \Magento\Quote\Api\CartManagementInterface
     */
    private $cartManagement;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var CustomerRegistry
     */
    private $customerRegistry;

    /**
     * @var Encryptor
     */
    private $encryptor;

    /**
     * @var \Magento\Sales\Api\OrderPaymentRepositoryInterface
     */
    private $paymentRepository;

    /**
     * @var \Magento\Framework\Validator\Factory
     */
    private $validatorFactory;

    /**
     * @var \Magento\Directory\Model\ResourceModel\Country\CollectionFactory
     */
    private $countryCollectionFactory;

    /**
     * @var \Magento\Sales\Api\TransactionRepositoryInterface
     */
    private $transactionRepository;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $magentoCheckoutSession;

    /**
     * @var \Amazon\Pay\Domain\AmazonAddressFactory
     */
    private $amazonAddressFactory;

    /**
     * @var \Amazon\Pay\Helper\Address
     */
    private $addressHelper;

    /**
     * @var \Amazon\Pay\Helper\Data
     */
    private $amazonHelper;

    /**
     * @var AmazonConfig
     */
    private $amazonConfig;

    /**
     * @var Adapter\AmazonPayAdapter
     */
    private $amazonAdapter;

    /**
     * @var AsyncManagement
     */
    private $asyncManagement;

    /**
     * @var AsyncManagement\Charge
     */
    private $asyncCharge;

    /**
     * @var array
     */
    private $amazonSessions = [];

    /**
     * @var array
     */
    private $carts = [];

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    private $orderCollectionFactory;

    /**
     * @var MaskedQuoteIdToQuoteIdInterface
     */
    private $maskedQuoteIdConverter;

    /**
     * @var PaymentTokenRepositoryInterface
     */
    private $paymentTokenRepository;

    /**
     * @var PaymentTokenFactory
     */
    private $paymentTokenFactory;

    /**
     * @var PaymentTokenManagement
     */
    private $paymentTokenManagement;

    /**
     * @var \Amazon\Pay\Model\Subscription\SubscriptionManager
     */
    private $subscriptionManager;

    /**
     * @var CustomerHelper
     */
    private $customerHelper;

    /**
     * @var Matcher
     */
    private $matcher;

    /**
     * Token Model
     *
     * @var TokenModelFactory
     */
    private $tokenModelFactory;

    /**
     *
     * @var UserContext
     */
    private $userContext;

    /**
     * @var \Amazon\Pay\Logger\Logger
     */
    private $logger;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var Translate
     */
    private $translationRenderer;

    /**
     * @var UpdateCouponUsages
     */
    private $updateCouponUsages;

    /**
     * CheckoutSessionManagement constructor.
     *
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Quote\Model\QuoteIdMaskFactory $quoteIdMaskFactory
     * @param \Magento\Quote\Api\CartManagementInterface $cartManagement
     * @param \Magento\Quote\Api\CartRepositoryInterface $cartRepository
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\Customer\Model\CustomerRegistry $customerRegistry
     * @param \Magento\Framework\Encryption\Encryptor $encryptor
     * @param \Magento\Sales\Api\OrderPaymentRepositoryInterface $paymentRepository
     * @param \Magento\Framework\Validator\Factory $validatorFactory ,
     * @param \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory ,
     * @param \Magento\Checkout\Model\Session $magentoCheckoutSession
     * @param \Amazon\Pay\Domain\AmazonAddressFactory $amazonAddressFactory ,
     * @param \Amazon\Pay\Helper\Address $addressHelper ,
     * @param \Amazon\Pay\Helper\Data $amazonHelper
     * @param AmazonConfig $amazonConfig
     * @param Adapter\AmazonPayAdapter $amazonAdapter
     * @param AsyncManagement $asyncManagement
     * @param \Amazon\Pay\Model\AsyncManagement\Charge $asyncCharge
     * @param \Magento\Sales\Api\TransactionRepositoryInterface $transactionRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory
     * @param MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdConverter
     * @param PaymentTokenRepositoryInterface $paymentTokenRepository
     * @param PaymentTokenFactory $paymentTokenFactory
     * @param PaymentTokenManagementInterface $paymentTokenManagement
     * @param \Amazon\Pay\Model\Subscription\SubscriptionManager $subscriptionManager
     * @param CustomerHelper $customerHelper
     * @param Matcher $matcher
     * @param TokenModelFactory $tokenModelFactory
     * @param UserContext $userContext
     * @param \Amazon\Pay\Logger\Logger $logger
     * @param Session $session
     * @param Translate $translationRenderer
     * @param UpdateCouponUsages $updateCouponUsages
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Quote\Model\QuoteIdMaskFactory $quoteIdMaskFactory,
        \Magento\Quote\Api\CartManagementInterface $cartManagement,
        \Magento\Quote\Api\CartRepositoryInterface $cartRepository,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Customer\Model\CustomerRegistry $customerRegistry,
        \Magento\Framework\Encryption\Encryptor $encryptor,
        \Magento\Sales\Api\OrderPaymentRepositoryInterface $paymentRepository,
        \Magento\Framework\Validator\Factory $validatorFactory,
        \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory,
        \Magento\Checkout\Model\Session $magentoCheckoutSession,
        \Amazon\Pay\Domain\AmazonAddressFactory $amazonAddressFactory,
        \Amazon\Pay\Helper\Address $addressHelper,
        \Amazon\Pay\Helper\Data $amazonHelper,
        \Amazon\Pay\Model\AmazonConfig $amazonConfig,
        \Amazon\Pay\Model\Adapter\AmazonPayAdapter $amazonAdapter,
        \Amazon\Pay\Model\AsyncManagement $asyncManagement,
        \Amazon\Pay\Model\AsyncManagement\Charge $asyncCharge,
        \Magento\Sales\Api\TransactionRepositoryInterface $transactionRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdConverter,
        PaymentTokenRepositoryInterface $paymentTokenRepository,
        PaymentTokenFactory $paymentTokenFactory,
        PaymentTokenManagementInterface $paymentTokenManagement,
        \Amazon\Pay\Model\Subscription\SubscriptionManager $subscriptionManager,
        CustomerHelper $customerHelper,
        Matcher $matcher,
        TokenModelFactory $tokenModelFactory,
        UserContext $userContext,
        \Amazon\Pay\Logger\Logger $logger,
        Session $session,
        Translate $translationRenderer,
        UpdateCouponUsages $updateCouponUsages
    ) {
        $this->storeManager = $storeManager;
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->cartManagement = $cartManagement;
        $this->cartRepository = $cartRepository;
        $this->orderRepository = $orderRepository;
        $this->customerRepository = $customerRepository;
        $this->customerRegistry = $customerRegistry;
        $this->encryptor = $encryptor;
        $this->paymentRepository = $paymentRepository;
        $this->validatorFactory = $validatorFactory;
        $this->countryCollectionFactory = $countryCollectionFactory;
        $this->magentoCheckoutSession = $magentoCheckoutSession;
        $this->amazonAddressFactory = $amazonAddressFactory;
        $this->addressHelper = $addressHelper;
        $this->amazonHelper = $amazonHelper;
        $this->amazonConfig = $amazonConfig;
        $this->amazonAdapter = $amazonAdapter;
        $this->asyncManagement = $asyncManagement;
        $this->asyncCharge = $asyncCharge;
        $this->transactionRepository = $transactionRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->maskedQuoteIdConverter = $maskedQuoteIdConverter;
        $this->paymentTokenRepository = $paymentTokenRepository;
        $this->paymentTokenFactory = $paymentTokenFactory;
        $this->paymentTokenManagement = $paymentTokenManagement;
        $this->subscriptionManager = $subscriptionManager;
        $this->customerHelper = $customerHelper;
        $this->matcher = $matcher;
        $this->tokenModelFactory = $tokenModelFactory;
        $this->userContext = $userContext;
        $this->logger = $logger;
        $this->session = $session;
        $this->translationRenderer = $translationRenderer;
        $this->updateCouponUsages = $updateCouponUsages;
    }

    /**
     * Get Amazon checkout session info from cache or API call
     *
     * @param mixed $amazonSessionId
     * @return mixed
     */
    protected function getAmazonSession($amazonSessionId)
    {
        if (!isset($this->amazonSessions[$amazonSessionId])) {
            $this->amazonSessions[$amazonSessionId] = $this->amazonAdapter->getCheckoutSession(
                $this->storeManager->getStore()->getId(),
                $amazonSessionId
            );
        }
        return $this->amazonSessions[$amazonSessionId];
    }

    /**
     * True if module is enabled, active, and cart contains no restricted products
     *
     * @param CartInterface $quote
     * @return bool
     */
    protected function canCheckoutWithAmazon($quote)
    {
        return $this->amazonConfig->isEnabled() &&
            !$this->amazonHelper->hasRestrictedProducts($quote);
    }

    /**
     * Check if quote ID is associated with another order
     *
     * In some particular cases an error occurs in Magento where an order with the same quoteId
     * is duplicated in a very short time difference.
     * This method checks if there is already an order created for that particular Quote.
     * https://github.com/magento/magento2/issues/13952
     *
     * @param CartInterface $quote
     * @return bool
     */
    protected function canSubmitQuote($quote)
    {
        if (!$quote->getIsActive()) {
            return false;
        }

        $orderCollection = $this->orderCollectionFactory->create()
            ->addFieldToSelect('increment_id')
            ->addFieldToFilter('quote_id', ['eq' => $quote->getId()])
            ->addFieldToFilter('status', ['neq' => \Magento\Sales\Model\Order::STATE_CANCELED]);

        return ($orderCollection->count() == 0);
    }

    /**
     * Get Amazon address data from checkout session
     *
     * @param mixed $amazonSessionId
     * @param bool $isShippingAddress
     * @param mixed $addressDataExtractor
     * @return mixed
     */
    protected function fetchAddress($amazonSessionId, $isShippingAddress, $addressDataExtractor)
    {
        $result = false;

        $session = $this->getAmazonSession($amazonSessionId);

        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        $addressData = call_user_func($addressDataExtractor, $session);
        if (!empty($addressData)) {
            $addressData['state'] = $addressData['stateOrRegion'];
            $addressData['phone'] = $addressData['phoneNumber'];

            $address = array_combine(
                array_map('ucfirst', array_keys($addressData)),
                array_values($addressData)
            );

            $result = $this->convertToMagentoAddress($address, $isShippingAddress);
            $result[0]['email'] = $session['buyer']['email'];
        }

        return $result;
    }

    /**
     * Format Amazon address data as Magento address
     *
     * @param array $address
     * @param boolean $isShippingAddress
     * @return array
     */
    protected function convertToMagentoAddress(array $address, $isShippingAddress = false)
    {
        $amazonAddress  = $this->amazonAddressFactory->create(['address' => $address]);
        $magentoAddress = $this->addressHelper->convertToMagentoEntity($amazonAddress);

        if ($isShippingAddress) {
            $countryCollection = $this->countryCollectionFactory->create();

            $collectionSize = $countryCollection->loadByStore()
                ->addFieldToFilter('country_id', ['eq' => $magentoAddress->getCountryId()])
                ->setPageSize(1)
                ->setCurPage(1)
                ->getSize();

            if (1 != $collectionSize) {
                throw new WebapiException(__('The store doesn\'t support the country that was entered. ' .
                    'To review allowed countries, go to General > General > Allow Countries list. Enter ' .
                    'a supported country and try again. '));
            }
        }

        return [$this->addressHelper->convertToArray($magentoAddress)];
    }

    /**
     * @inheritdoc
     */
    public function getConfig($cartId = null)
    {
        $result = [];
        $quote = $this->session->getQuoteFromIdOrSession($cartId);

        if ($this->canCheckoutWithAmazon($quote)) {
            $loginButtonPayload = $this->amazonAdapter->generateLoginButtonPayload();
            $checkoutButtonPayload = $this->amazonAdapter->generateCheckoutButtonPayload($quote);
            $config = [
                'merchant_id' => $this->amazonConfig->getMerchantId(),
                'currency' => $this->amazonConfig->getCurrencyCode(),
                'button_color' => $this->amazonConfig->getButtonColor(),
                'language' => $this->amazonConfig->getLanguage(),
                'sandbox' => $this->amazonConfig->isSandboxEnabled(),
                'login_payload' => $loginButtonPayload,
                'login_signature' => $this->amazonAdapter->signButton($loginButtonPayload),
                'checkout_payload' => $checkoutButtonPayload,
                'checkout_signature' => $this->amazonAdapter->signButton($checkoutButtonPayload),
                'public_key_id' => $this->amazonConfig->getPublicKeyId(),
            ];

            if ($quote) {
                // Ensure the totals are up to date, in case the checkout does something to update qty or shipping
                // without collecting totals
                $quote->collectTotals();
                $config['pay_only'] = $this->amazonHelper->isPayOnly($quote);

                $payNowButtonPayload = $this->amazonAdapter->generatePayNowButtonPayload(
                    $quote,
                    $this->amazonConfig->getPaymentAction()
                );
                $config['paynow_payload'] = $payNowButtonPayload;
                $config['paynow_signature'] = $this->amazonAdapter->signButton($payNowButtonPayload);
            }

            $result[] = $config;
        }

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function getShippingAddress($amazonSessionId)
    {
        $result =  $this->fetchAddress($amazonSessionId, true, function ($session) {
            return $session['shippingAddress'] ?? [];
        });

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function getBillingAddress($amazonSessionId)
    {
        $result = $this->fetchAddress($amazonSessionId, false, function ($session) {
            return $session['billingAddress'] ?? [];
        });

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function getPaymentDescriptor($amazonSessionId)
    {
        $session = $this->getAmazonSession($amazonSessionId);
        return $session['paymentPreferences'][0]['paymentDescriptor'] ?? '';
    }

    /**
     * @inheritDoc
     */
    public function updateCheckoutSession($amazonCheckoutSessionId, $cartId = null)
    {
        if (!$quote = $this->session->getQuoteFromIdOrSession($cartId)) {
            return [];
        }

        $result = null;
        $paymentIntent = Adapter\AmazonPayAdapter::PAYMENT_INTENT_AUTHORIZE;

        if ($this->canCheckoutWithAmazon($quote)) {
            $response = $this->amazonAdapter->updateCheckoutSession(
                $quote,
                $amazonCheckoutSessionId,
                $paymentIntent
            );
            if (!empty($response['webCheckoutDetails']['amazonPayRedirectUrl'])) {
                $result = $response['webCheckoutDetails']['amazonPayRedirectUrl'];
            } elseif (!empty($response) && $response['status'] == 404) {
                $result = ['status' => $response['reasonCode']];
            }
        }
        return $result;
    }

    /**
     * Load transaction.
     *
     * @param mixed $transactionId
     * @param \Magento\Sales\Api\Data\TransactionInterface $type
     * @return mixed
     */
    private function getTransaction($transactionId, $type = null)
    {
        $this->searchCriteriaBuilder->addFilter(Transaction::TXN_ID, $transactionId);

        if ($type) {
            $this->searchCriteriaBuilder->addFilter(Transaction::TXN_TYPE, $type);
        }

        $searchCriteria = $this->searchCriteriaBuilder->create();
        $transactionCollection = $this->transactionRepository->getList($searchCriteria);

        if (count($transactionCollection)) {
            return $transactionCollection->getFirstItem();
        } else {
            return null;
        }
    }

    /**
     * Update transaction ID associated with payment
     *
     * Swaps the checkoutSessionId that was originally stored on the sales_payment_transaction record with the
     * real payment charge (transaction) id. Also updates the payment's last transaction id to match.
     *
     * @param string $chargeId
     * @param \Magento\Sales\Api\Data\OrderPaymentInterface $payment
     * @param mixed $transaction
     * @return void
     */
    private function updateTransactionId($chargeId, $payment, $transaction)
    {
        $transaction->setTxnId($chargeId);
        $this->transactionRepository->save($transaction);

        $payment->setLastTransId($chargeId);
        $this->paymentRepository->save($payment);

        if ($invoice = $payment->getCreatedInvoice()) {
            $invoice->setTransactionId($chargeId)->save();
        }
    }

    /**
     * Set order as pending review
     *
     * @param Payment $payment
     */
    private function setPending($payment)
    {
        $order = $payment->getOrder();
        $payment->setIsTransactionPending(true);
        $order->setState(\Magento\Sales\Model\Order::STATE_PAYMENT_REVIEW)->setStatus(
            \Magento\Sales\Model\Order::STATE_PAYMENT_REVIEW
        );
        $this->orderRepository->save($order);
    }

    /**
     * Set order as processing
     *
     * @param Payment $payment
     * @param bool $payInvoice
     * @return void
     */
    protected function setProcessing($payment, $payInvoice = true)
    {
        $order = $payment->getOrder();
        $payment->setIsTransactionPending(false);
        $invoiceCollection = $order->getInvoiceCollection();
        if (!empty($invoiceCollection->getItems()) && $payInvoice) {
            $invoiceCollection->getFirstItem()->pay();
        }
        $state = \Magento\Sales\Model\Order::STATE_PROCESSING;
        $status = $order->getConfig()->getStateDefaultStatus($state);
        $order->setState($state)->setStatus($status);
        $this->orderRepository->save($order);
    }

    /**
     * Add capture comment to order
     *
     * @param Payment $payment
     * @param mixed $chargeId
     * @return void
     */
    protected function addCaptureComment($payment, $chargeId)
    {
        $order = $payment->getOrder();
        $formattedAmount = $order->getBaseCurrency()->formatTxt($order->getBaseGrandTotal());
        if ($order->getBaseCurrencyCode() != $order->getOrderCurrencyCode()) {
            $formattedAmount = $formattedAmount . ' [' . $order->formatPriceTxt($payment->getAmountOrdered()) . ']';
        }
        if ($this->amazonConfig->getPaymentAction() == PaymentAction::AUTHORIZE_AND_CAPTURE) {
            $message = __('Captured amount of %1 online.', $formattedAmount);
        } else {
            $message = __('Authorized amount of %1.', $formattedAmount);
        }
        $payment->addTransactionCommentsToOrder($chargeId, $message);
        $this->orderRepository->save($order);
    }

    /**
     * Cancel order
     *
     * @param OrderInterface $order
     * @param CartInterface $quote
     * @param string $reasonMessage
     * @return void
     */
    public function cancelOrder($order, $quote = null, $reasonMessage = '')
    {
        if (!$quote) {
            $quote = $this->getQuote($order);
        }

        // set order as cancelled
        $order->setState(\Magento\Sales\Model\Order::STATE_CANCELED)->setStatus(
            \Magento\Sales\Model\Order::STATE_CANCELED
        );
        // cancel associated items to account for inventory reservations
        foreach ($order->getAllItems() as $item) {
            $item->cancel();
        }
        $order->getPayment()->setIsTransactionClosed(true);

        // cancel invoices
        foreach ($order->getInvoiceCollection() as $invoice) {
            $invoice->setState(Invoice::STATE_CANCELED);
        }

        // decrement coupon usages if applicable
        $this->updateCouponUsages->execute($order, false);

        if (!$reasonMessage) {
            $reasonMessage = __('Something went wrong. Choose another payment method for checkout and try again.');
        }

        $order->addStatusHistoryComment($reasonMessage);

        $order->save();

        if ($this->subscriptionManager->hasSubscription($quote)) {
            $this->subscriptionManager->cancel($order);
        }
    }

    /**
     * Return result array with failure flag and message
     *
     * @param string $message
     * @param string $logEntryDetails
     * @return array
     */
    protected function handleCompleteCheckoutSessionError($message, $logEntryDetails = '')
    {
        $this->logger->error($message . ' ' . $logEntryDetails);
        $result = [
            'success' => false,
            'message' => $this->getTranslationString($message),
        ];
        return $result;
    }

    /**
     * @inheritDoc
     */
    public function completeCheckoutSession($amazonSessionId, $cartId = null, $orderId = null)
    {
        if (!$amazonSessionId) {
            return $this->handleCompleteCheckoutSessionError(
                self::GENERIC_COMPLETE_CHECKOUT_ERROR_MESSAGE,
                'Missing AmazonSessionId.'
            );
        }

        if (!$orderId) {
            $orderResult = $this->placeOrCollectOrder($amazonSessionId, $cartId);
            if (!$orderResult['success']) {
                return $orderResult;
            }
            $orderId = $orderResult['order_id'] ?? null;
            if (!$orderId) {
                throw new OrderFailureException('Missing order_id');
            }
        }

        $result = [
            'success' => false
        ];

        try {
            $order = $this->orderRepository->get($orderId);
            $quote = $this->getQuote($order);

            // @TODO: associate token with payment?
            $result['order_id'] = $orderId;
            $result['increment_id'] = $order->getIncrementId();
            // Order is canceled on failure
            $amazonCheckoutResult = $this->completeAmazonCheckoutSession($amazonSessionId, $order, $quote);
            if (!$amazonCheckoutResult['success']) {
                return $amazonCheckoutResult;
            }

            // Order is canceled on failure
            $paymentResult = $this->handlePayment($amazonSessionId, $amazonCheckoutResult, $order, $quote);
            if (!$paymentResult['success']) {
                return $paymentResult;
            }

            $result['success'] = true;

        } catch (\Exception $e) {
            if (isset($order)) {
                $this->closeChargePermission($amazonSessionId, $order, $e);
                $session = $this->getAmazonSession($amazonSessionId);
                $cancelledMessage = $this->getCanceledMessage($session);
                $this->cancelOrder($order, $quote, $cancelledMessage);
                $this->magentoCheckoutSession->restoreQuote();
            }

            throw $e;
        }
        return $result;
    }

    /**
     * Collect quote, check amazon checkout session, place order
     *
     * @param string $amazonSessionId
     * @param string $quoteId
     * @return array|false[]|true[]
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function placeOrder($amazonSessionId, $quoteId = null)
    {
        if (!$quote = $this->session->getQuoteFromIdOrSession($quoteId)) {
            $errorMsg = "Unable to complete Amazon Pay checkout. Quote not found.";
            if ($quoteId) {
                $errorMsg .= ' quoteId: ' . $quoteId . '.';
            }
            $this->logger->error($errorMsg);
            return [
                'success' => false,
                'message' => $this->getTranslationString('Unable to complete Amazon Pay checkout'),
            ];
        }

        if (!$this->canCheckoutWithAmazon($quote) || !$this->canSubmitQuote($quote)) {
            $this->logger->error("Unable to complete Amazon Pay checkout. Can't submit quote id: " . $quote->getId());
            return [
                'success' => false,
                'message' => $this->getTranslationString('Unable to complete Amazon Pay checkout'),
            ];
        }

        if (!$quote->getCustomer()->getId()) {
            $quote->setCheckoutMethod(\Magento\Quote\Api\CartManagementInterface::METHOD_GUEST);
        }

        // check the Amazon session one last time before placing the order
        $amazonSession = $this->amazonAdapter->getCheckoutSession(
            $quote->getStoreId(),
            $amazonSessionId
        );

        if ($amazonSession['statusDetails']['state'] == 'Canceled') {
            return [
                'success' => false,
                'message' => $this->getCanceledMessage($amazonSession),
            ];
        }

        if (empty($quote->getCustomerEmail())) {
            $quote->setCustomerEmail($amazonSession['buyer']['email']);
        }

        // get payment to load it in the session, so that a salesrule that relies on payment method conditions
        // can work as expected
        $payment = $quote->getPayment();

        // Some checkout flows (especially 3rd party) could get to this point without setting payment method
        if (empty($payment->getMethod())) {
            $payment->setMethod(Config::CODE);
        }

        // set amazon session id on payment object to be used in authorize
        $payment->setAdditionalInformation('amazon_session_id', $amazonSessionId);

        // collect quote totals before placing order (needed for 2.3.0 and lower)
        // https://github.com/amzn/amazon-payments-magento-2-plugin/issues/992
        $quote->collectTotals();

        $orderId = null;
        try {
            $orderId = $this->cartManagement->placeOrder($quote->getId());
        } catch (\Exception $e) {
            $errorMsg = 'Unable to place order for quoteId ' . $quote->getId();
            $this->logger->error($errorMsg . ': ' . $e->getMessage());
        }

        if (!$orderId) {
            $errorMsg = "Unable to complete Amazon Pay checkout. Unable to place order with quote id: ";
            $this->logger->error($errorMsg . $quote->getId());
            return [
                'success' => false,
                'message' => $this->getTranslationString(self::GENERIC_COMPLETE_CHECKOUT_ERROR_MESSAGE),
            ];
        }

        return [
            'success' => true,
            'order_id' => $orderId
        ];
    }

    /**
     * Get display message for failed payment
     *
     * @param mixed $amazonSession
     * @return \Magento\Framework\Phrase|mixed
     */
    protected function getCanceledMessage($amazonSession)
    {
        if ($amazonSession['statusDetails']['reasonCode'] == 'BuyerCanceled') {
            return $this->getTranslationString('This transaction was cancelled. Please try again.');
        } elseif ($amazonSession['statusDetails']['reasonCode'] == 'Declined') {
            return $this->getTranslationString(
                'This transaction was declined. Please try again using a different payment method.'
            );
        }

        return $amazonSession['statusDetails']['reasonDescription'];
    }

    /**
     * Update vault token
     *
     * @param string $amazonSessionId
     * @param string $chargePermissionId
     * @param CartInterface $quote
     * @param OrderInterface $order
     * @return void
     */
    protected function updateVaultToken($amazonSessionId, $chargePermissionId, $quote, $order)
    {
        if ($this->amazonConfig->isVaultEnabled() && $this->subscriptionManager->hasSubscription($quote)) {
            $token = $this->paymentTokenManagement->getByGatewayToken(
                $amazonSessionId,
                Config::CODE,
                $order->getCustomerId()
            );
            if ($token) {
                $token->setGatewayToken($chargePermissionId);
                $token->setIsVisible(true);
                $this->paymentTokenRepository->save($token);
            } else {
                $message = "Unable to update vault token. amazonSessionId: " . $amazonSessionId
                    . ' Customer Id: ' . $order->getCustomerId();
                $this->logger->debug($message);
            }
        }
    }

    /**
     * Get a translated string to return through the web API
     *
     * @param string $message
     * @return string
     */
    private function getTranslationString($message)
    {
        try {
            $translation = $this->translationRenderer->render([$message], []);
        } catch (\Exception $e) {
            $translation = $message;
        }

        return $translation;
    }

    /**
     * Sign in customer to Magento store via Amazon Sign In
     *
     * @param mixed $buyerToken
     * @return mixed
     */
    public function signIn($buyerToken)
    {
        if (!$this->amazonConfig->isLwaEnabled()) {
            $result = [
                'success' => false,
                'message' => __('Amazon Sign-in is disabled')
            ];

            return [$result];
        }

        try {
            $buyerInfo = $this->amazonAdapter->getBuyer($buyerToken);
            $amazonCustomer = $this->getAmazonCustomer($buyerInfo);

            if ($amazonCustomer) {
                $customer = $this->processAmazonCustomer($amazonCustomer);
                if ($customer && $customer instanceof \Magento\Customer\Model\Data\Customer) {
                    $customerToken = $this
                        ->tokenModelFactory
                        ->create()
                        ->createCustomerToken($customer->getId())
                        ->getToken();
                    $result = [
                        'success' => true,
                        'customer_id' => $customer->getId(),
                        'customer_email' => $customer->getEmail(),
                        'customer_firstname' => $customer->getFirstName(),
                        'customer_last' => $customer->getLastName(),
                        'customer_bearer_token' => $customerToken
                    ];

                } else {
                    // Magento customer exists with same email used for Sign in with Amazon
                    $result = [
                        'success' => false,
                        'customer_email' => $customer->getEmail(),
                        'message' => __('A shop account for this email address already exists. Please enter your ' .
                            'shop accounts password to log in without leaving the shop.')
                    ];
                }

            } else {
                $result = $this->getBuyerIdError($buyerToken);
            }

        } catch (\Exception $e) {
            $result = $this->getLoginError($e);
        }

        return [$result];
    }

    /**
     * Match amazon customer data to existing store customer or create new account
     *
     * @param AmazonCustomerInterface $amazonCustomer
     * @return \Amazon\Pay\Api\Data\AmazonCustomerInterface|\Magento\Customer\Api\Data\CustomerInterface|null
     */
    protected function processAmazonCustomer(AmazonCustomerInterface $amazonCustomer)
    {
        $customerData = $this->matcher->match($amazonCustomer);

        if (null === $customerData) {
            return $this->customerHelper->createCustomer($amazonCustomer);
        }

        if ($amazonCustomer->getId() != $customerData->getExtensionAttributes()->getAmazonId()) {
            return $amazonCustomer;
        }

        return $customerData;
    }

    /**
     * Get customer from Amazon buyerInfo
     *
     * @param mixed $buyerInfo
     * @return \Amazon\Pay\Domain\AmazonCustomer|bool
     */
    protected function getAmazonCustomer($buyerInfo)
    {
        return $this->customerHelper->getAmazonCustomer($buyerInfo);
    }

    /**
     * Get sign in errors related to empty buyer ID
     *
     * @param string $buyerToken
     * @return array
     */
    protected function getBuyerIdError($buyerToken)
    {
        $this->logger->error('Amazon buyerId is empty. Token: ' . $buyerToken);
        return [
            'success' => false,
            'message' => __('Amazon buyerId is empty')
        ];
    }

    /**
     * Get general errors associated with sign in failure
     *
     * @param \Exception $e
     * @return array
     */
    protected function getLoginError($e)
    {
        $this->logger->error('An error occurred while matching your Amazon account with ' .
            'your store account. : ' . $e->getMessage());
        return [
            'success' => false,
            'message' => __($e->getMessage())
        ];
    }

    /**
     * Link an amazon_customer to a Magento customer
     *
     * @param mixed $buyerToken
     * @param string $password
     * @return mixed
     */
    public function setCustomerLink($buyerToken, $password)
    {
        try {
            $buyerInfo = $this->amazonAdapter->getBuyer($buyerToken);
            $amazonCustomer = $this->getAmazonCustomer($buyerInfo);

            if ($amazonCustomer) {
                $magentoCustomer = $this->customerRepository->get($amazonCustomer->getEmail());
                $customerSecure = $this->customerRegistry->retrieveSecureData($magentoCustomer->getId());
                $hash = $customerSecure->getPasswordHash() ?? '';

                if ($this->encryptor->validateHash($password, $hash)) {
                    $this->customerHelper->updateCustomerLink($magentoCustomer->getId(), $amazonCustomer->getId());
                    return $this->signIn($buyerToken);
                } else {
                    return [
                        [
                            'success' => false,
                            'message' => __('The password supplied was incorrect')
                        ]
                    ];
                }
            } else {
                $result = $this->getBuyerIdError($buyerToken);
            }
        } catch (\Exception $e) {
            $result = $this->getLoginError($e);
        }

        return [$result];
    }

    /**
     * OrderId included in successful placement or collection
     *
     * @param string $amazonSessionId
     * @param string $cartId
     * @return array
     */
    private function placeOrCollectOrder($amazonSessionId, $cartId)
    {
        // If cartId passed, an order still needs to be placed
        if ($cartId) {
            try {
                $result = $this->placeOrder($amazonSessionId, $cartId);
            } catch (\Exception $e) {
                $logEntryDetails = 'amazonSessionId: ' . $amazonSessionId
                    . ' cartId: ' . $cartId
                    . ' Error: ' . $e->getMessage();
                return $this->handleCompleteCheckoutSessionError(
                    self::GENERIC_COMPLETE_CHECKOUT_ERROR_MESSAGE,
                    $logEntryDetails
                );
            }
        } else {
            try {
                $transaction = $this->getTransaction($amazonSessionId);
                // If no transaction for amazonSessionId, the order still needs placed (APB)
                if ($transaction) {
                    $result = [
                        'success' => true,
                        'order_id' => $transaction->getOrderId()
                    ];
                } else {
                    $result = $this->placeOrder($amazonSessionId);
                }
            } catch (\Exception $e) {
                $logEntryDetails =  'amazonSessionId: ' . $amazonSessionId . ' ' . $e->getMessage();
                return $this->handleCompleteCheckoutSessionError(
                    self::GENERIC_COMPLETE_CHECKOUT_ERROR_MESSAGE,
                    $logEntryDetails
                );
            }
        }
        if (!$result['success']) {
            $reason = $result['message'] ?? '';
            return $this->handleCompleteCheckoutSessionError(
                $reason,
                $reason
            );
        }

        return $result;
    }

    /**
     * Complete checkout session on amazon side
     *
     * @param string $amazonSessionId
     * @param OrderInterface $order
     * @param CartInterface $quote
     * @return array
     */
    private function completeAmazonCheckoutSession($amazonSessionId, $order, $quote)
    {
        $amazonCompleteCheckoutResult = $this->amazonAdapter->completeCheckoutSession(
            $order->getStoreId(),
            $amazonSessionId,
            $order->getGrandTotal(),
            $order->getOrderCurrencyCode()
        );

        $completeCheckoutStatus = $amazonCompleteCheckoutResult['status'] ?? '404';

        if (!preg_match('/^2\d\d$/', $completeCheckoutStatus)) {

            $session = $this->amazonAdapter->getCheckoutSession(
                $order->getStoreId(),
                $amazonSessionId
            );

            $cancelledMessage = $this->getCanceledMessage($session);

            // Something went wrong, but the order has already been placed, so cancelling it
            $this->cancelOrder($order, $quote, $cancelledMessage);
            $this->magentoCheckoutSession->restoreQuote();

            if (isset($session['chargePermissionId'])) {
                $this->amazonAdapter->closeChargePermission(
                    $order->getStoreId(),
                    $session['chargePermissionId'],
                    'Canceled due to checkout session failed to complete',
                    true
                );
            }

            if (!$cancelledMessage) {
                $cancelledMessage = 'Something went wrong. Choose another payment method for checkout and try again.';
            }

            return $this->handleCompleteCheckoutSessionError(
                $cancelledMessage,
                'Order cancelled due to Amazon checkout session failure: ' . $amazonCompleteCheckoutResult['message']
            );
        }

        return [
            'success' => true,
            'amazonCompleteCheckoutResult' => $amazonCompleteCheckoutResult
        ];
    }

    /**
     * Validate checkout success and handle based on state
     *
     * @param string $amazonSessionId
     * @param array $amazonCheckoutResult
     * @param OrderInterface $order
     * @param CartInterface $quote
     * @return array
     */
    private function handlePayment($amazonSessionId, $amazonCheckoutResult, $order, $quote)
    {
        try {
            // $amazonCheckoutResult holds success flag and actual result from api call
            $amazonCompleteCheckoutResult = $amazonCheckoutResult['amazonCompleteCheckoutResult'];
            $payment = $order->getPayment();
            $chargeId = $amazonCompleteCheckoutResult['chargeId'];
            $transaction = $this->getTransaction($amazonCompleteCheckoutResult['checkoutSessionId']);
            $completeCheckoutStatus = $amazonCompleteCheckoutResult['status'] ?? '404';

            if ($completeCheckoutStatus != '202' &&
                $this->amazonConfig->getPaymentAction() == PaymentAction::AUTHORIZE_AND_CAPTURE) {
                // capture on Amazon Pay
                $this->amazonAdapter->captureCharge(
                    $order->getStoreId(),
                    $chargeId,
                    $order->getGrandTotal(),
                    $order->getOrderCurrencyCode()
                );
                $this->setProcessing($payment, false);
                // capture and invoice on the Magento side
                if ($this->amazonConfig->getAuthorizationMode() == AuthorizationMode::SYNC) {
                    $this->asyncCharge->capture($order, $chargeId, $order->getGrandTotal());
                }
            }
            $amazonCharge = $this->amazonAdapter->getCharge($order->getStoreId(), $chargeId);

            // @TODO: for recurring, the order incremenet ID needs to be updated on the charge
            //Send merchantReferenceId to Amazon
            $this->amazonAdapter->updateChargePermission(
                $order->getStoreId(),
                $amazonCharge['chargePermissionId'],
                ['merchantReferenceId' => $order->getIncrementId()]
            );

            $chargeState = $amazonCharge['statusDetails']['state'];

            switch ($chargeState) {
                case 'AuthorizationInitiated':
                    $payment->setIsTransactionClosed(false);
                    $this->setPending($payment);
                    $transaction->setIsClosed(false);
                    $this->asyncManagement->queuePendingAuthorization($chargeId);
                    break;
                case 'Authorized':
                    $this->setProcessing($payment);
                    if ($this->amazonConfig->getAuthorizationMode() == AuthorizationMode::SYNC_THEN_ASYNC) {
                        $this->addCaptureComment($payment, $amazonCharge['chargePermissionId']);
                    }
                    break;
                case 'Captured':
                    $payment->setIsTransactionClosed(true);
                    $transaction->setIsClosed(true);
                    $this->setProcessing($payment, false);

                    if ($this->amazonConfig->getAuthorizationMode() == AuthorizationMode::SYNC_THEN_ASYNC) {
                        // capture and invoice on the Magento side
                        $this->asyncCharge->capture($order, $chargeId, $quote->getGrandTotal());
                    }
                    break;
            }

            // relies on updateTransactionId to save the $payment
            $payment->setAdditionalInformation(
                'charge_permission_id',
                $amazonCompleteCheckoutResult['chargePermissionId']
            );

            $this->updateTransactionId($chargeId, $payment, $transaction);
            $this->updateVaultToken(
                $amazonSessionId,
                $amazonCompleteCheckoutResult['chargePermissionId'],
                $quote,
                $order
            );
            return ['success'=>true];
        } catch (\Exception $e) {
            $this->closeChargePermission($amazonSessionId, $order, $e);

            $session = $this->amazonAdapter->getCheckoutSession(
                $order->getStoreId(),
                $amazonSessionId
            );

            $cancelledMessage = $this->getCanceledMessage($session);
            $this->cancelOrder($order, $quote, $cancelledMessage);
            $this->magentoCheckoutSession->restoreQuote();

            $logEntryDetails = 'amazonSessionId: ' . $amazonSessionId
                . ' quoteId: ' . $quote->getId()
                . ' Error: ' . $e->getMessage();
            return $this->handleCompleteCheckoutSessionError(
                self::GENERIC_COMPLETE_CHECKOUT_ERROR_MESSAGE,
                $logEntryDetails
            );
        }
    }

    /**
     * Cleanup after an error
     *
     * @param string $amazonSessionId
     * @param OrderInterface $order
     * @param \Exception $e
     * @return void
     */
    private function closeChargePermission($amazonSessionId, OrderInterface $order, \Exception $e)
    {
        $session = $this->amazonAdapter->getCheckoutSession(
            $order->getStoreId(),
            $amazonSessionId
        );

        if (isset($session['chargePermissionId'])) {
            $this->amazonAdapter->closeChargePermission(
                $order->getStoreId(),
                $session['chargePermissionId'],
                'Canceled due to technical issue: ' . $e->getMessage(),
                true
            );
        }
    }

    /**
     * Set order status to payment review
     *
     * @param mixed $orderId
     * @return void
     */
    public function setOrderPendingPaymentReview($orderId)
    {
        try {
            if (!$orderId) {
                throw new \InvalidArgumentException('orderId missing');
            }
            $order = $this->orderRepository->get($orderId);
            // Update status to Pending Payment Review to support order placement before auth
            $payment = $order->getPayment();
            $this->setPending($payment);
        } catch (\Exception $e) {
            $this->logger->error('Unable to set payment review order status. ' . $e->getMessage());
        }
    }

    /**
     * Get order by quote
     *
     * @param OrderInterface $order
     * @return CartInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getQuote(OrderInterface $order)
    {
        $quoteId = $order->getQuoteId();
        return $this->cartRepository->get($quoteId);
    }
}
