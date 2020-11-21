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

namespace Amazon\PayV2\Model;

use Amazon\PayV2\Api\Data\CheckoutSessionInterface;
use Amazon\PayV2\Model\Config\Source\AuthorizationMode;
use Amazon\PayV2\Model\Config\Source\PaymentAction;
use Amazon\PayV2\Model\AsyncManagement;
use http\Exception\UnexpectedValueException;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Framework\Validator\Exception as ValidatorException;
use Magento\Framework\Webapi\Exception as WebapiException;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order\Payment;
use Magento\Sales\Api\Data\TransactionInterface as Transaction;

class CheckoutSessionManagement implements \Amazon\PayV2\Api\CheckoutSessionManagementInterface
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
     * @var \Amazon\PayV2\Domain\AmazonAddressFactory
     */
    private $amazonAddressFactory;

    /**
     * @var \Amazon\PayV2\Helper\Address
     */
    private $addressHelper;

    /**
     * @var \Amazon\PayV2\Api\Data\CheckoutSessionInterfaceFactory
     */
    private $checkoutSessionFactory;

    /**
     * @var \Amazon\PayV2\Api\CheckoutSessionRepositoryInterface
     */
    private $checkoutSessionRepository;

    /**
     * @var \Amazon\PayV2\Helper\Data
     */
    private $amazonHelper;

    /**
     * @var AmazonConfig
     */
    private $amazonConfig;

    /**
     * @var Adapter\AmazonPayV2Adapter
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
     * CheckoutSessionManagement constructor.
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Quote\Model\QuoteIdMaskFactory $quoteIdMaskFactory
     * @param \Magento\Quote\Api\CartManagementInterface $cartManagement
     * @param \Magento\Quote\Api\CartRepositoryInterface $cartRepository
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Sales\Api\OrderPaymentRepositoryInterface $paymentRepository
     * @param \Magento\Framework\Validator\Factory $validatorFactory ,
     * @param \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory ,
     * @param \Amazon\PayV2\Domain\AmazonAddressFactory $amazonAddressFactory ,
     * @param \Amazon\PayV2\Helper\Address $addressHelper ,
     * @param \Amazon\PayV2\Api\Data\CheckoutSessionInterfaceFactory $checkoutSessionFactory
     * @param \Amazon\PayV2\Api\CheckoutSessionRepositoryInterface $checkoutSessionRepository
     * @param \Amazon\PayV2\Helper\Data $amazonHelper
     * @param AmazonConfig $amazonConfig
     * @param Adapter\AmazonPayV2Adapter $amazonAdapter
     * @param AsyncManagement $asyncManagement
     * @param \Amazon\PayV2\Model\AsyncManagement\Charge $asyncCharge
     * @param \Magento\Sales\Api\TransactionRepositoryInterface $transactionRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Quote\Model\QuoteIdMaskFactory $quoteIdMaskFactory,
        \Magento\Quote\Api\CartManagementInterface $cartManagement,
        \Magento\Quote\Api\CartRepositoryInterface $cartRepository,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Sales\Api\OrderPaymentRepositoryInterface $paymentRepository,
        \Magento\Framework\Validator\Factory $validatorFactory,
        \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory,
        \Amazon\PayV2\Domain\AmazonAddressFactory $amazonAddressFactory,
        \Amazon\PayV2\Helper\Address $addressHelper,
        \Amazon\PayV2\Api\Data\CheckoutSessionInterfaceFactory $checkoutSessionFactory,
        \Amazon\PayV2\Api\CheckoutSessionRepositoryInterface $checkoutSessionRepository,
        \Amazon\PayV2\Helper\Data $amazonHelper,
        \Amazon\PayV2\Model\AmazonConfig $amazonConfig,
        \Amazon\PayV2\Model\Adapter\AmazonPayV2Adapter $amazonAdapter,
        \Amazon\PayV2\Model\AsyncManagement $asyncManagement,
        \Amazon\PayV2\Model\AsyncManagement\Charge $asyncCharge,
        \Magento\Sales\Api\TransactionRepositoryInterface $transactionRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
    )
    {
        $this->storeManager = $storeManager;
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->cartManagement = $cartManagement;
        $this->cartRepository = $cartRepository;
        $this->orderRepository = $orderRepository;
        $this->paymentRepository = $paymentRepository;
        $this->validatorFactory = $validatorFactory;
        $this->countryCollectionFactory = $countryCollectionFactory;
        $this->amazonAddressFactory = $amazonAddressFactory;
        $this->addressHelper = $addressHelper;
        $this->checkoutSessionFactory = $checkoutSessionFactory;
        $this->checkoutSessionRepository = $checkoutSessionRepository;
        $this->amazonHelper = $amazonHelper;
        $this->amazonConfig = $amazonConfig;
        $this->amazonAdapter = $amazonAdapter;
        $this->asyncManagement = $asyncManagement;
        $this->asyncCharge = $asyncCharge;
        $this->transactionRepository = $transactionRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * @param mixed $cartId
     * @return CartInterface
     */
    protected function getCart($cartId)
    {
        if ($cartId instanceof CartInterface) {
            $result = $cartId;
        } else {
            if (!isset($this->carts[$cartId])) {
                if (is_numeric($cartId)) {
                    $cart = $this->cartRepository->getActive($cartId);
                } else {
                    $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
                    $cart = $this->cartRepository->getActive($quoteIdMask->getQuoteId());
                }
                $this->carts[$cartId] = $cart;
            }
            $result = $this->carts[$cartId];
        }
        return $result;
    }

    /**
     * @param mixed $cartId
     * @return CheckoutSessionInterface
     */
    protected function getCheckoutSessionForCart($cartId)
    {
        return $this->checkoutSessionRepository->getActiveForCart($this->getCart($cartId));
    }

    /**
     * @param mixed $cartId
     * @param CheckoutSessionInterface $checkoutSession
     * @return bool
     */
    protected function canComplete($cartId, $checkoutSession)
    {
        return $this->getCart($cartId)->getIsActive() && $checkoutSession->getUpdatedAt();
    }

    /**
     * @param mixed $cartId
     * @return mixed
     */
    protected function getAmazonSession($cartId)
    {
        $amazonSessionId = $this->getCheckoutSession($cartId);
        if (!isset($this->amazonSessions[$amazonSessionId])) {
            $this->amazonSessions[$amazonSessionId] = $this->amazonAdapter->getCheckoutSession($this->storeManager->getStore()->getId(), $amazonSessionId);
        }
        return $this->amazonSessions[$amazonSessionId];
    }

    /**
     * @param mixed $cartId
     * @return bool
     */
    protected function isAvailable($cartId)
    {
        $result = false;
        if ($this->amazonConfig->isEnabled()) {
            $cart = $this->getCart($cartId);
            $result = !$this->amazonHelper->hasRestrictedProducts($cart);
        }
        return $result;
    }

    /**
     * @param mixed $cartId
     * @param bool $isShippingAddress
     * @param mixed $addressDataExtractor
     * @return mixed
     */
    protected function fetchAddress($cartId, $isShippingAddress, $addressDataExtractor)
    {
        $result = false;
        if ($this->isAvailable($cartId)) {
            $session = $this->getAmazonSession($cartId);

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
    public function getConfig($cartId)
    {
        $result = [];
        if ($this->isAvailable($cartId)) {
            $buttonPayload = $this->amazonAdapter->generateButtonPayload();

            $result = [
                'merchant_id' => $this->amazonConfig->getMerchantId(),
                'currency' => $this->amazonConfig->getCurrencyCode(),
                'button_color' => $this->amazonConfig->getButtonColor(),
                'language' => $this->amazonConfig->getLanguage(),
                'pay_only' => $this->amazonHelper->isPayOnly($this->getCart($cartId)),
                'sandbox' => $this->amazonConfig->isSandboxEnabled(),
                'payload' => $buttonPayload,
                'signature' => $this->amazonAdapter->signButton($buttonPayload),
                'public_key_id' => $this->amazonConfig->getPublicKeyId(),
            ];
        }
        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function createCheckoutSession($cartId)
    {
        $result = [];
        $this->cancelCheckoutSession($cartId);
        if ($this->isAvailable($cartId)) {
            $result = $this->amazonAdapter->createCheckoutSession($this->storeManager->getStore()->getId());
            if (isset($result['checkoutSessionId'])) {
                $checkoutSession = $this->checkoutSessionFactory->create([
                    'data' => [
                        CheckoutSessionInterface::KEY_QUOTE_ID => $this->getCart($cartId)->getId(),
                        CheckoutSessionInterface::KEY_SESSION_ID => $result['checkoutSessionId'],
                    ]
                ]);
                $this->checkoutSessionRepository->save($checkoutSession);
            }
        }
        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getShippingAddress($cartId)
    {
        return $this->fetchAddress($cartId, true, function ($session) {
            return $session['shippingAddress'] ?? [];
        });
    }

    /**
     * {@inheritdoc}
     */
    public function getBillingAddress($cartId)
    {
        return $this->fetchAddress($cartId, false, function ($session) {
            return $session['billingAddress'] ?? [];
        });
    }

    /**
     * {@inheritdoc}
     */
    public function getPaymentDescriptor($cartId)
    {
        $session = $this->getAmazonSession($cartId);
        return $session['paymentPreferences'][0]['paymentDescriptor'] ?? '';
    }

    /**
     * {@inheritdoc}
     */
    public function cancelCheckoutSession($cartId)
    {
        if ($this->isAvailable($cartId)) {
            $checkoutSession = $this->getCheckoutSessionForCart($cartId);
            if ($checkoutSession) {
                $checkoutSession->cancel();
                $this->checkoutSessionRepository->save($checkoutSession);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getCheckoutSession($cartId)
    {
        $result = null;
        if ($this->isAvailable($cartId)) {
            $checkoutSession = $this->getCheckoutSessionForCart($cartId);
            if ($checkoutSession) {
                $result = $checkoutSession->getSessionId();
            }
        }
        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function updateCheckoutSession($cartId)
    {
        $result = null;
        $checkoutSession = null;
        $paymentIntent = Adapter\AmazonPayV2Adapter::PAYMENT_INTENT_AUTHORIZE;
        $cart = $this->getCart($cartId);
        if ($this->isAvailable($cartId)) {
            $checkoutSession = $this->getCheckoutSessionForCart($cart);
        }
        if ($checkoutSession && $cart->getIsActive()) {
            $response = $this->amazonAdapter->updateCheckoutSession($cart, $checkoutSession->getSessionId(), $paymentIntent);
            if (!empty($response['webCheckoutDetails']['amazonPayRedirectUrl'])) {
                $result = $response['webCheckoutDetails']['amazonPayRedirectUrl'];
                $checkoutSession->setUpdated();
                $this->checkoutSessionRepository->save($checkoutSession);
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
        $order->setState(\Magento\Sales\Model\Order::STATE_PROCESSING)->setStatus(
            \Magento\Sales\Model\Order::STATE_PROCESSING
        );
        $this->orderRepository->save($order);
    }

    /**
     * Add capture comment to order
     *
     * @param Payment $payment
     * @param $cart
     * @param $chargeId
     */
    protected function addCaptureComment($payment, $cart, $chargeId)
    {
        $order = $payment->getOrder();
        $formattedAmount = $order->getBaseCurrency()->formatTxt($cart->getBaseGrandTotal());
        if ($order->getBaseCurrencyCode() != $order->getOrderCurrencyCode()) {
            $formattedAmount = $formattedAmount .' ['. $order->formatPriceTxt($payment->getAmountOrdered()) .']';
        }
        if ($this->amazonConfig->getPaymentAction() == PaymentAction::AUTHORIZE_AND_CAPTURE) {
            $message = __('Captured amount of %1 online.', $formattedAmount);
        }
        else {
            $message = __('Authorized amount of %1.', $formattedAmount);
        }
        $payment->addTransactionCommentsToOrder($chargeId, $message);
        $this->orderRepository->save($order);
    }

    /**
     * {@inheritdoc}
     */
    public function completeCheckoutSession($cartId)
    {
        $result = null;
        $checkoutSession = null;
        $cart = $this->getCart($cartId);
        if ($this->isAvailable($cartId)) {
            $checkoutSession = $this->getCheckoutSessionForCart($cart);
        }
        if ($checkoutSession && $this->canComplete($cart, $checkoutSession)) {
            try {
                if (!$cart->getCustomer()->getId()) {
                    $cart->setCheckoutMethod(\Magento\Quote\Api\CartManagementInterface::METHOD_GUEST);
                }

                // check the Amazon session one last time before placing the order
                $amazonSession = $this->amazonAdapter->getCheckoutSession($cart->getStoreId(), $checkoutSession->getSessionId());
                if ($amazonSession['statusDetails']['state'] == 'Canceled') {
                    return [
                        'success' => false,
                        'message' => $this->getCanceledMessage($amazonSession),
                    ];
                }

                $orderId = $this->cartManagement->placeOrder($cart->getId());
                $order = $this->orderRepository->get($orderId);
                $result = [
                    'success' => true,
                    'order_id' => $orderId,
                ];

                $amazonResult = $this->amazonAdapter->completeCheckoutSession($cart->getStoreId(), $checkoutSession->getSessionId(), $cart->getGrandTotal() , $cart->getQuoteCurrencyCode());
                $completeCheckoutStatus = $amazonResult['status'] ?? '404';
                if (!preg_match('/^2\d\d$/', $completeCheckoutStatus)){
                    // Something went wrong, but the order has already been placed
                    return [
                        'success' => false,
                        'message' => $amazonResult['message'],
                    ];
                }

                $payment = $order->getPayment();
                $chargeId = $amazonResult['chargeId'];
                $transaction = $this->getTransaction($amazonResult['checkoutSessionId']);
                $this->updateTransactionId($chargeId, $payment, $transaction);
                $payment->setAdditionalInformation('charge_permission_id', $amazonResult['chargePermissionId']);

                if ($completeCheckoutStatus != '202' && $this->amazonConfig->getPaymentAction() == PaymentAction::AUTHORIZE_AND_CAPTURE) {
                    // capture on Amazon Pay
                    $this->amazonAdapter->captureCharge($cart->getStoreId(), $chargeId, $cart->getGrandTotal(), $cart->getQuoteCurrencyCode());
                    // capture and invoice on the Magento side
                    $this->asyncCharge->capture($order, $chargeId, $cart->getGrandTotal());
                }
                $amazonCharge = $this->amazonAdapter->getCharge($cart->getStoreId(), $chargeId);

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
                            $this->addCaptureComment($payment, $cart, $amazonCharge['chargePermissionId']);
                        }
                        break;
                    case 'Captured':
                        $payment->setIsTransactionClosed(true);
                        $transaction->setIsClosed(true);

                        if ($this->amazonConfig->getAuthorizationMode() == AuthorizationMode::SYNC_THEN_ASYNC) {
                            $this->setProcessing($payment);
                            $this->addCaptureComment($payment, $cart, $chargeId);
                        }
                        break;
                }

                $checkoutSession->complete();
                $this->checkoutSessionRepository->save($checkoutSession);
            } catch (\Exception $e) {
                $session = $this->amazonAdapter->getCheckoutSession($cart->getStoreId(), $checkoutSession->getSessionId());
                if (isset($session['chargePermissionId'])) {
                    $response = $this->amazonAdapter->closeChargePermission($cart->getStoreId(), $session['chargePermissionId'], 'Canceled due to technical issue: ' . $e->getMessage(), true);
                }
                $this->cancelCheckoutSession($cartId);
                throw $e;
            }
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
        }
        else if ($amazonSession['statusDetails']['reasonCode'] == 'Declined') {
            return __("This transaction was declined. Please try again using a different payment method.");
        }

        return $amazonSession['statusDetails']['reasonDescription'];
    }
}
