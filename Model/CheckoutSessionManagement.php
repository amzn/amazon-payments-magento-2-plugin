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
use Amazon\Pay\Model\AsyncManagement;
use Amazon\Pay\Helper\Customer as CustomerHelper;
use Amazon\Pay\Model\Customer\CompositeMatcher as Matcher;
use Amazon\Pay\Api\Data\AmazonCustomerInterface;
use http\Exception\UnexpectedValueException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\NotFoundException;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Framework\Validator\Exception as ValidatorException;
use Magento\Framework\Webapi\Exception as WebapiException;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Framework\Encryption\Encryptor;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order\Payment;
use Magento\Quote\Model\MaskedQuoteIdToQuoteIdInterface;
use Magento\Sales\Api\Data\TransactionInterface as Transaction;
use Magento\Integration\Model\Oauth\TokenFactory as TokenModelFactory;
use Magento\Authorization\Model\UserContextInterface as UserContext;

class CheckoutSessionManagement implements \Amazon\Pay\Api\CheckoutSessionManagementInterface
{
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
     * CheckoutSessionManagement constructor.
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
     * @param CustomerHelper $customerHelper
     * @param Matcher $matcher
     * @param TokenModelFactory $tokenModelFactory
     * @param UserContext $userContext
     * @param \Amazon\Pay\Logger\Logger $logger
     * @param Session $session
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
        CustomerHelper $customerHelper,
        Matcher $matcher,
        TokenModelFactory $tokenModelFactory,
        UserContext $userContext,
        \Amazon\Pay\Logger\Logger $logger,
        Session $session
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
        $this->customerHelper = $customerHelper;
        $this->matcher = $matcher;
        $this->tokenModelFactory = $tokenModelFactory;
        $this->userContext = $userContext;
        $this->logger = $logger;
        $this->session = $session;
    }

    /**
     * @param mixed $amazonCheckoutSessionId
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
     * @return bool
     */
    protected function canCheckoutWithAmazon($quote)
    {
        return $this->amazonConfig->isEnabled() &&
            !$this->amazonHelper->hasRestrictedProducts($quote);
    }

    /**
     * In some particular cases an error occurs in Magento where an order with the same quoteId
     * is duplicated in a very short time difference.
     * This method checks if there is already an order created for that particular Quote.
     * https://github.com/magento/magento2/issues/13952
     * @param Quote $quote
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
     * @param mixed $amazonCheckoutSessionId
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
                throw new WebapiException(__('the country for your address is not allowed for this store'));
            }
        }

        return [$this->addressHelper->convertToArray($magentoAddress)];
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig($cartId = null)
    {
        $result = [];
        $loginButtonPayload = $this->amazonAdapter->generateLoginButtonPayload();
        $checkoutButtonPayload = $this->amazonAdapter->generateCheckoutButtonPayload();
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

        $quote = $this->session->getQuoteFromIdOrSession($cartId);

        if ($quote) {
            // Ensure the totals are up to date, in case the checkout does something to update qty or shipping without
            // collecting totals
            $quote->collectTotals();

            if ($this->canCheckoutWithAmazon($quote)) {
                $payNowButtonPayload = $this->amazonAdapter->generatePayNowButtonPayload(
                    $quote,
                    $this->amazonConfig->getPaymentAction()
                );

                $config['pay_only'] = $this->amazonHelper->isPayOnly($quote);
                $config['paynow_payload'] = $payNowButtonPayload;
                $config['paynow_signature'] = $this->amazonAdapter->signButton($payNowButtonPayload);
            }
        }
        $result[] = $config;
        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getShippingAddress($amazonSessionId)
    {
        $result =  $this->fetchAddress($amazonSessionId, true, function ($session) {
            return $session['shippingAddress'] ?? [];
        });

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getBillingAddress($amazonSessionId)
    {
        $result = $this->fetchAddress($amazonSessionId, false, function ($session) {
            return $session['billingAddress'] ?? [];
        });

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getPaymentDescriptor($amazonSessionId)
    {
        $session = $this->getAmazonSession($amazonSessionId);
        return $session['paymentPreferences'][0]['paymentDescriptor'] ?? '';
    }

    /**
     * {@inheritdoc}
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
     * @param $transactionId
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
        }
    }

    /**
     * Swaps the checkoutSessionId that was originally stored on the sales_payment_transaction record with the
     * real payment charge (transaction) id. Also updates the payment's last transaction id to match.
     *
     * @param $chargeId
     * @param $payment
     * @param $transaction
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
     */
    protected function setProcessing($payment)
    {
        $order = $payment->getOrder();
        $payment->setIsTransactionPending(false);
        $invoiceCollection = $order->getInvoiceCollection();
        if (!empty($invoiceCollection->getItems())) {
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
     * @param $quote
     * @param $chargeId
     */
    protected function addCaptureComment($payment, $quote, $chargeId)
    {
        $order = $payment->getOrder();
        $formattedAmount = $order->getBaseCurrency()->formatTxt($quote->getBaseGrandTotal());
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
     * @param $order
     */
    private function cancelOrder($order)
    {
        // set order as cancelled
        $order->setState(\Magento\Sales\Model\Order::STATE_CANCELED)->setStatus(
            \Magento\Sales\Model\Order::STATE_CANCELED
        );
        $order->getPayment()->setIsTransactionClosed(true);

        // cancel invoices
        foreach ($order->getInvoiceCollection() as $invoice) {
            $invoice->setState(Invoice::STATE_CANCELED);
        }

        // delete order comments and add new one
        foreach ($order->getStatusHistories() as $history) {
            $history->delete();
        }
        $order->addStatusHistoryComment(
            __('Payment was unable to be successfully captured, the checkout session failed to complete.')
        );

        $order->save();
    }

    /**
     * {@inheritdoc}
     */
    public function completeCheckoutSession($amazonSessionId, $cartId = null)
    {
        if (!$quote = $this->session->getQuoteFromIdOrSession($cartId)) {
            return ['success' => false];
        }

        if (empty($amazonSessionId) || !$this->canCheckoutWithAmazon($quote) || !$this->canSubmitQuote($quote)) {
            $this->logger->debug("Unable to complete Amazon Pay checkout. Can't submit quote id: " . $quote->getId());
            return [
                'success' => false,
                'message' => __("Unable to complete Amazon Pay checkout"),
            ];
        }
        try {
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

            if ($amazonSession['productType'] == 'PayOnly') {
                $addressData = $amazonSession['billingAddress'];

                $addressData['state'] = $addressData['stateOrRegion'];
                $addressData['phone'] = $addressData['phoneNumber'];

                $address = array_combine(
                    array_map('ucfirst', array_keys($addressData)),
                    array_values($addressData)
                );
                $amazonAddress  = $this->amazonAddressFactory->create(['address' => $address]);

                $customerAddress = $this->addressHelper->convertToMagentoEntity($amazonAddress);
                $quote->getBillingAddress()->importCustomerAddressData($customerAddress);
                if (empty($quote->getCustomerEmail())) {
                    $quote->setCustomerEmail($amazonSession['buyer']['email']);
                }
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

            $orderId = $this->cartManagement->placeOrder($quote->getId());
            $order = $this->orderRepository->get($orderId);
            $result = [
                'success' => true,
                'order_id' => $orderId,
                'increment_id' => $order->getIncrementId()
            ];

            $amazonCompleteCheckoutResult = $this->amazonAdapter->completeCheckoutSession(
                $quote->getStoreId(),
                $amazonSessionId,
                $quote->getGrandTotal(),
                $quote->getQuoteCurrencyCode()
            );
            $completeCheckoutStatus = $amazonCompleteCheckoutResult['status'] ?? '404';
            if (!preg_match('/^2\d\d$/', $completeCheckoutStatus)) {
                // Something went wrong, but the order has already been placed, so cancelling it
                $this->cancelOrder($order);

                $session = $this->amazonAdapter->getCheckoutSession(
                    $quote->getStoreId(),
                    $amazonSessionId
                );
                if (isset($session['chargePermissionId'])) {
                    $this->amazonAdapter->closeChargePermission(
                        $quote->getStoreId(),
                        $session['chargePermissionId'],
                        'Canceled due to checkout session failed to complete',
                        true
                    );
                }

                return [
                    'success' => false,
                    'message' => __(
                        'Payment was unable to be successfully captured, the checkout session failed to complete.'
                    ),
                ];
            }

            $payment = $order->getPayment();
            $chargeId = $amazonCompleteCheckoutResult['chargeId'];
            $transaction = $this->getTransaction($amazonCompleteCheckoutResult['checkoutSessionId']);

            if ($completeCheckoutStatus != '202' &&
                $this->amazonConfig->getPaymentAction() == PaymentAction::AUTHORIZE_AND_CAPTURE) {
                // capture on Amazon Pay
                $this->amazonAdapter->captureCharge(
                    $quote->getStoreId(),
                    $chargeId,
                    $quote->getGrandTotal(),
                    $quote->getQuoteCurrencyCode()
                );
                // capture and invoice on the Magento side
                $this->asyncCharge->capture($order, $chargeId, $quote->getGrandTotal());
            }
            $amazonCharge = $this->amazonAdapter->getCharge($quote->getStoreId(), $chargeId);

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
                    if ($this->amazonConfig->getAuthorizationMode() == AuthorizationMode::SYNC_THEN_ASYNC) {
                        $this->setProcessing($payment);
                        $this->addCaptureComment($payment, $quote, $amazonCharge['chargePermissionId']);
                    }
                    break;
                case 'Captured':
                    $payment->setIsTransactionClosed(true);
                    $transaction->setIsClosed(true);

                    if ($this->amazonConfig->getAuthorizationMode() == AuthorizationMode::SYNC_THEN_ASYNC) {
                        $this->setProcessing($payment);
                        $this->addCaptureComment($payment, $quote, $chargeId);
                    }
                    break;
            }

            // relies on updateTransactionId to save the $payment
            $payment->setAdditionalInformation(
                'charge_permission_id',
                $amazonCompleteCheckoutResult['chargePermissionId']
            );
            $this->updateTransactionId($chargeId, $payment, $transaction);

        } catch (\Exception $e) {
            $session = $this->amazonAdapter->getCheckoutSession(
                $quote->getStoreId(),
                $amazonSessionId
            );

            if (isset($session['chargePermissionId'])) {
                $this->amazonAdapter->closeChargePermission(
                    $quote->getStoreId(),
                    $session['chargePermissionId'],
                    'Canceled due to technical issue: ' . $e->getMessage(),
                    true
                );
            }

            // cancel order
            if (isset($order)) {
                $this->cancelOrder($order);
            }

            throw $e;
        }
        return $result;
    }

    /**
     * @param $amazonSession
     * @return \Magento\Framework\Phrase|mixed
     */
    protected function getCanceledMessage($amazonSession)
    {
        if ($amazonSession['statusDetails']['reasonCode'] == 'BuyerCanceled') {
            return __("This transaction was cancelled. Please try again.");
        } elseif ($amazonSession['statusDetails']['reasonCode'] == 'Declined') {
            return __("This transaction was declined. Please try again using a different payment method.");
        }

        return $amazonSession['statusDetails']['reasonDescription'];
    }

    /**
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

    protected function getAmazonCustomer($buyerInfo)
    {
        return $this->customerHelper->getAmazonCustomer($buyerInfo);
    }

    protected function getBuyerIdError($buyerToken)
    {
        $this->logger->error('Amazon buyerId is empty. Token: ' . $buyerToken);
        return [
            'success' => false,
            'message' => __('Amazon buyerId is empty')
        ];
    }

    protected function getLoginError($e)
    {
        $this->logger->error('Error processing Amazon Login: ' . $e->getMessage());
        return [
            'success' => false,
            'message' => __($e->getMessage())
        ];
    }

    /**
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
}
