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

namespace Amazon\Payment\Model\PaymentManagement;

use Amazon\Core\Client\ClientFactoryInterface;
use Amazon\Payment\Api\Data\PendingAuthorizationInterface;
use Amazon\Payment\Api\Data\PendingAuthorizationInterfaceFactory;
use Amazon\Payment\Model\Adapter\AmazonPaymentAdapter;
use Amazon\Payment\Model\PaymentManagement;
use Amazon\Payment\Domain\AmazonAuthorizationDetailsResponseFactory;
use Amazon\Payment\Domain\AmazonGetOrderDetailsResponseFactory;
use Amazon\Payment\Domain\AmazonOrderStatus;
use Amazon\Payment\Domain\Details\AmazonAuthorizationDetails;
use Amazon\Payment\Domain\Details\AmazonOrderDetails;
use Amazon\Payment\Domain\Validator\AmazonAuthorization;
use Amazon\Payment\Exception\SoftDeclineException;
use Exception;
use Magento\Backend\Model\UrlInterface;
use Magento\Framework\Api\SearchCriteriaBuilderFactory;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Notification\NotifierInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Api\Data\TransactionInterface;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use Magento\Sales\Api\OrderPaymentRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order\Payment\Transaction;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;
use Amazon\Payment\Exception\TransactionTimeoutException;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Authorization extends AbstractOperation
{
    /**
     * @var PendingAuthorizationInterfaceFactory
     */
    private $pendingAuthorizationFactory;

    /**
     * @var ClientFactoryInterface
     */
    private $clientFactory;

    /**
     * @var AmazonAuthorizationDetailsResponseFactory
     */
    private $amazonAuthorizationDetailsResponseFactory;

    /**
     * @var AmazonAuthorization
     */
    private $amazonAuthorizationValidator;

    /**
     * @var OrderPaymentRepositoryInterface
     */
    private $orderPaymentRepository;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var ManagerInterface
     */
    private $eventManager;

    /**
     * @var AmazonGetOrderDetailsResponseFactory
     */
    private $amazonGetOrderDetailsResponseFactory;

    /**
     * @var PaymentManagement
     */
    private $paymentManagement;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var bool
     */
    private $throwExceptions = false;

    private $amazonPaymentAdapter;

    /**
     * Authorization constructor.
     *
     * @param NotifierInterface $notifier
     * @param UrlInterface $urlBuilder
     * @param SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory
     * @param InvoiceRepositoryInterface $invoiceRepository
     * @param ClientFactoryInterface $clientFactory
     * @param PendingAuthorizationInterfaceFactory $pendingAuthorizationFactory
     * @param AmazonAuthorizationDetailsResponseFactory $amazonAuthorizationDetailsResponseFactory
     * @param AmazonAuthorization $amazonAuthorizationValidator
     * @param OrderPaymentRepositoryInterface $orderPaymentRepository
     * @param OrderRepositoryInterface $orderRepository
     * @param ManagerInterface $eventManager
     * @param AmazonGetOrderDetailsResponseFactory $amazonGetOrderDetailsResponseFactory
     * @param PaymentManagement $paymentManagement
     * @param LoggerInterface $logger
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        NotifierInterface $notifier,
        UrlInterface $urlBuilder,
        SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory,
        InvoiceRepositoryInterface $invoiceRepository,
        ClientFactoryInterface $clientFactory,
        PendingAuthorizationInterfaceFactory $pendingAuthorizationFactory,
        AmazonAuthorizationDetailsResponseFactory $amazonAuthorizationDetailsResponseFactory,
        AmazonAuthorization $amazonAuthorizationValidator,
        OrderPaymentRepositoryInterface $orderPaymentRepository,
        OrderRepositoryInterface $orderRepository,
        ManagerInterface $eventManager,
        AmazonGetOrderDetailsResponseFactory $amazonGetOrderDetailsResponseFactory,
        PaymentManagement $paymentManagement,
        LoggerInterface $logger,
        AmazonPaymentAdapter $adapter
    ) {
        $this->clientFactory = $clientFactory;
        $this->pendingAuthorizationFactory = $pendingAuthorizationFactory;
        $this->amazonAuthorizationDetailsResponseFactory = $amazonAuthorizationDetailsResponseFactory;
        $this->amazonAuthorizationValidator = $amazonAuthorizationValidator;
        $this->orderPaymentRepository = $orderPaymentRepository;
        $this->orderRepository = $orderRepository;
        $this->eventManager = $eventManager;
        $this->amazonGetOrderDetailsResponseFactory = $amazonGetOrderDetailsResponseFactory;
        $this->paymentManagement = $paymentManagement;
        $this->logger = $logger;
        $this->amazonPaymentAdapter = $adapter;

        parent::__construct($notifier, $urlBuilder, $searchCriteriaBuilderFactory, $invoiceRepository);
    }

    /**
     * {@inheritdoc}
     */
    public function setThrowExceptions($throwExceptions)
    {
        $this->throwExceptions = $throwExceptions;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function updateAuthorization(
        $pendingAuthorizationId,
        AmazonAuthorizationDetails $authorizationDetails = null,
        AmazonOrderDetails $orderDetails = null
    ) {
        try {
            $pendingAuthorization = $this->pendingAuthorizationFactory->create();
            $pendingAuthorization->getResource()->beginTransaction();
            $pendingAuthorization->setLockOnLoad(true);
            $pendingAuthorization->load($pendingAuthorizationId);

            if ($pendingAuthorization->getOrderId()) {
                if ($pendingAuthorization->isProcessed()) {
                    $this->processNewAuthorization($pendingAuthorization, $orderDetails);
                } else {
                    $this->processUpdateAuthorization($pendingAuthorization, $authorizationDetails);
                }
            }

            $pendingAuthorization->getResource()->commit();
        } catch (Exception $e) {
            $this->logger->error($e);
            $pendingAuthorization->getResource()->rollBack();

            if ($this->throwExceptions) {
                throw $e;
            }
        }
    }

    /**
     * Processes Authorization during cron
     *
     * @param PendingAuthorizationInterface $pendingAuthorization
     * @param AmazonAuthorizationDetails|null $authorizationDetails
     * @throws TransactionTimeoutException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function processUpdateAuthorization(
        PendingAuthorizationInterface $pendingAuthorization,
        AmazonAuthorizationDetails $authorizationDetails = null
    ) {
        $order = $this->orderRepository->get($pendingAuthorization->getOrderId());
        $payment = $this->orderPaymentRepository->get($pendingAuthorization->getPaymentId());
        $order->setPayment($payment);
        $order->setData(OrderInterface::PAYMENT, $payment);

        $storeId = $order->getStoreId();

        $authorizationId = $pendingAuthorization->getAuthorizationId();

        if (null === $authorizationDetails) {
            $responseParser = $this->clientFactory->create($storeId)->getAuthorizationDetails(
                [
                    'amazon_authorization_id' => $authorizationId
                ]
            );

            $response = $this->amazonAuthorizationDetailsResponseFactory->create(['response' => $responseParser]);
            $authorizationDetails = $response->getDetails();
        }

        $capture = $authorizationDetails->hasCapture();

        $validation = $this->amazonAuthorizationValidator->validate($authorizationDetails);

        if (isset($validation['result'])) {
            if ($validation['result'] && !$authorizationDetails->isPending()) {
                $this->completePendingAuthorization($order, $payment, $pendingAuthorization, $capture);
            } else {
                if (!$validation['result']) {
                    switch ($validation['reason']) {
                        case 'temporary':
                            $this->resubmitPendingAuthorization($order, $payment, $pendingAuthorization, $capture);
                            break;
                        case 'hard_decline':
                            $this->hardDeclinePendingAuthorization($order, $payment, $pendingAuthorization, $capture);
                            break;
                        case 'soft_decline':
                            $this->softDeclinePendingAuthorization($order, $payment, $pendingAuthorization, $capture);
                            break;
                    }
                }
            }
        }
    }

    /**
     *  Updates pending authorization during cron job
     *
     * @param OrderInterface $order
     * @param OrderPaymentInterface $payment
     * @param PendingAuthorizationInterface $pendingAuthorization
     * @param $capture
     * @param TransactionInterface|null $newTransaction
     * @throws Exception
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function completePendingAuthorization(
        OrderInterface $order,
        OrderPaymentInterface $payment,
        PendingAuthorizationInterface $pendingAuthorization,
        $capture,
        TransactionInterface $newTransaction = null
    ) {
        $transactionId = ($capture) ? $pendingAuthorization->getCaptureId()
            : $pendingAuthorization->getAuthorizationId();

        $this->setProcessing($order);

        if ($capture) {
            $invoice = $this->getInvoiceAndSetPaid($transactionId, $order);

            if (!$newTransaction) {
                $this->paymentManagement->closeTransaction($transactionId, $payment, $order);
            } else {
                $invoice->setTransactionId($newTransaction->getTxnId());
            }

            $formattedAmount = $order->getBaseCurrency()->formatTxt($invoice->getBaseGrandTotal());
            $message = __('Captured amount of %1 online', $formattedAmount);
            $payment->setDataUsingMethod(
                'base_amount_paid_online',
                $payment->formatAmount($invoice->getBaseGrandTotal())
            );
        } else {
            $formattedAmount = $order->getBaseCurrency()->formatTxt($payment->getBaseAmountAuthorized());
            $message = __('Authorized amount of %1 online', $formattedAmount);
        }

        $transaction = ($newTransaction) ?: $this->paymentManagement->getTransaction($transactionId, $payment, $order);
        $payment->addTransactionCommentsToOrder($transaction, $message);

        $pendingAuthorization->delete();
        $order->save();
    }

    /**
     * Handles reauthorization in the case of a soft failure
     *
     * @param OrderInterface $order
     * @param OrderPaymentInterface $payment
     * @param PendingAuthorizationInterface $pendingAuthorization
     * @param $capture
     * @throws Exception
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function resubmitPendingAuthorization(
        OrderInterface $order,
        OrderPaymentInterface $payment,
        PendingAuthorizationInterface $pendingAuthorization,
        $capture
    ) {
        $transactionId = ($capture) ? $pendingAuthorization->getCaptureId()
            : $pendingAuthorization->getAuthorizationId();

        if ($capture) {
            $invoice = $this->getInvoice($transactionId, $order);
            $this->setPaymentReview($order);
            $formattedAmount = $order->getBaseCurrency()->formatTxt($invoice->getBaseGrandTotal());
        } else {
            $formattedAmount = $order->getBaseCurrency()->formatTxt($payment->getBaseAmountAuthorized());
        }

        $this->paymentManagement->closeTransaction($transactionId, $payment, $order);

        $this->processNewAuthorization($pendingAuthorization);

        $pendingAuthorization->setProcessed(true);
        $pendingAuthorization->save();
        $order->save();
    }

    /**
     * Handles authorization soft decline during cron
     *
     * @param OrderInterface $order
     * @param OrderPaymentInterface $payment
     * @param PendingAuthorizationInterface $pendingAuthorization
     * @param $capture
     * @throws Exception
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function softDeclinePendingAuthorization(
        OrderInterface $order,
        OrderPaymentInterface $payment,
        PendingAuthorizationInterface $pendingAuthorization,
        $capture
    ) {
        $transactionId = ($capture) ? $pendingAuthorization->getCaptureId()
            : $pendingAuthorization->getAuthorizationId();

        if ($capture) {
            $invoice = $this->getInvoice($transactionId, $order);
            $this->setPaymentReview($order);
            $formattedAmount = $order->getBaseCurrency()->formatTxt($invoice->getBaseGrandTotal());
        } else {
            $formattedAmount = $order->getBaseCurrency()->formatTxt($payment->getBaseAmountAuthorized());
        }

        $message = __('Declined amount of %1 online', $formattedAmount);
        $transaction = $this->paymentManagement->getTransaction($transactionId, $payment, $order);
        $payment->addTransactionCommentsToOrder($transaction, $message);
        $this->paymentManagement->closeTransaction($transactionId, $payment, $order);

        $pendingAuthorization->setProcessed(true);
        $pendingAuthorization->save();
        $order->save();

        $this->eventManager->dispatch(
            'amazon_payment_pending_authorization_soft_decline_after',
            [
                'order' => $order,
                'pendingAuthorization' => $pendingAuthorization,
            ]
        );
    }

    /**
     * Handles hard decline during cron
     * @param OrderInterface $order
     * @param OrderPaymentInterface $payment
     * @param PendingAuthorizationInterface $pendingAuthorization
     * @param $capture
     * @throws Exception
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function hardDeclinePendingAuthorization(
        OrderInterface $order,
        OrderPaymentInterface $payment,
        PendingAuthorizationInterface $pendingAuthorization,
        $capture
    ) {
        $transactionId = ($capture) ? $pendingAuthorization->getCaptureId()
            : $pendingAuthorization->getAuthorizationId();

        if ($capture) {
            $invoice = $this->getInvoiceAndSetCancelled($transactionId, $order);
            $formattedAmount = $order->getBaseCurrency()->formatTxt($invoice->getBaseGrandTotal());
            $this->addCaptureDeclinedNotice($order);
        } else {
            $formattedAmount = $order->getBaseCurrency()->formatTxt($payment->getBaseAmountAuthorized());
        }

        $message = __('Declined amount of %1 online', $formattedAmount);
        $this->setOnHold($order);
        $transaction = $this->paymentManagement->getTransaction($transactionId, $payment, $order);
        $payment->addTransactionCommentsToOrder($transaction, $message);
        $this->paymentManagement->closeTransaction($transactionId, $payment, $order);

        $pendingAuthorization->delete();
        $order->save();

        $this->eventManager->dispatch(
            'amazon_payment_pending_authorization_hard_decline_after',
            [
                'order' => $order,
                'pendingAuthorization' => $pendingAuthorization,
            ]
        );
    }

    /**
     * Processes new authorization during cron
     *
     * @param PendingAuthorizationInterface $pendingAuthorization
     * @param AmazonOrderDetails|null $orderDetails
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function processNewAuthorization(
        PendingAuthorizationInterface $pendingAuthorization,
        AmazonOrderDetails $orderDetails = null
    ) {
        $order = $this->orderRepository->get($pendingAuthorization->getOrderId());
        $payment = $this->orderPaymentRepository->get($pendingAuthorization->getPaymentId());
        $order->setPayment($payment);
        $order->setData(OrderInterface::PAYMENT, $payment);

        $storeId = $order->getStoreId();

        if (null === $orderDetails) {
            $responseParser = $this->clientFactory->create($storeId)->getOrderReferenceDetails(
                [
                    'amazon_order_reference_id' => $order->getExtensionAttributes()
                        ->getAmazonOrderReferenceId()
                        ->getAmazonOrderReferenceId()
                ]
            );

            $response = $this->amazonGetOrderDetailsResponseFactory->create(['response' => $responseParser]);
            $orderDetails = $response->getDetails();
        }

        if (AmazonOrderStatus::STATE_OPEN == $orderDetails->getStatus()->getState()) {
            $capture = $pendingAuthorization->isCapture();
            $this->requestNewAuthorization($order, $payment, $pendingAuthorization, $capture);
        }
    }

    /**
     * Attempts to request new authorization during cron for pending authorization items.
     *
     * @param OrderInterface $order
     * @param OrderPaymentInterface $payment
     * @param PendingAuthorizationInterface $pendingAuthorization
     * @param $capture
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function requestNewAuthorization(
        OrderInterface $order,
        OrderPaymentInterface $payment,
        PendingAuthorizationInterface $pendingAuthorization,
        $capture = false
    ) {
        try {
            if ($capture) {
                $invoice = $this->getInvoice($pendingAuthorization->getCaptureId(), $order);
                $baseAmount = $payment->formatAmount($invoice->getBaseGrandTotal());
            } else {
                $baseAmount = $payment->formatAmount($payment->getBaseAmountAuthorized());
            }

            $data = [
                'amazon_order_reference_id' => $order->getExtensionAttributes()
                    ->getAmazonOrderReferenceId()
                    ->getAmazonOrderReferenceId(),
                'amount' => $baseAmount,
                'currency_code' => $order->getOrderCurrencyCode()
            ];
            $response = $this->amazonPaymentAdapter->authorize($data, $capture, 1);
            if ($response['response_code'] != 'TransactionTimedOut') {
                throw new Exception('Unexpected Authorization status for asynchronous authorization');
            }
            $newAuthorization = $this->pendingAuthorizationFactory->create()
                ->setAuthorizationId($response['authorize_transaction_id'])
                ->setCapture($capture);
            if ($capture) {
                $newAuthorization->setCaptureId($response['capture_transaction_id']);
                $invoice->setTransactionId($response['capture_transaction_id'])
                    ->save();
                $payment->setTransactionId($response['capture_transaction_id']);
                $payment->setParentTransactionId($response['authorize_transaction_id']);
                $payment->addTransaction(Transaction::TYPE_CAPTURE, $invoice, true)
                    ->save();
            } else {
                $payment->setTransactionId($response['authorize_transaction_id']);
                $payment->addTransaction(Transaction::TYPE_AUTH, null, true)
                    ->save();
            }
            $payment->save();
            $newAuthorization->save();
            $pendingAuthorization->delete();

        } catch (\Exception $e) {
            $this->hardDeclinePendingAuthorization($order, $payment, $pendingAuthorization, $capture);
        }
    }
}
