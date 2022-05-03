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

namespace Amazon\Pay\Model\AsyncManagement;

use Amazon\Pay\Model\Config\Source\PaymentAction;
use Magento\Sales\Api\Data\TransactionInterface as Transaction;
use Magento\Sales\Api\Data\InvoiceInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order;
use Magento\Framework\Event\ManagerInterface;

class Charge extends AbstractOperation
{
    /**
     * @var \Amazon\Pay\Model\Adapter\AmazonPayAdapter
     */
    private $amazonAdapter;

    /**
     * @var \Amazon\Pay\Logger\AsyncIpnLogger
     */
    private $asyncLogger;

    /**
     * @var \Magento\Sales\Api\InvoiceRepositoryInterface
     */
    private $invoiceRepository;

    /**
     * @var \Magento\Sales\Model\Service\InvoiceService
     */
    private $invoiceService;

    /**
     * @var \Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface
     */
    private $transactionBuilder;

    /**
     * @var \Magento\Framework\Notification\NotifierInterface
     */
    private $notifier;

    /**
     * @var \Magento\Backend\Model\UrlInterface
     */
    private $urlBuilder;

    /**
     * @var \Amazon\Pay\Model\AmazonConfig
     */
    private $amazonConfig;

    /**
     * @var EventManagerInterface
     */
    private $eventManager;

    /**
     * Charge constructor.
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Sales\Api\TransactionRepositoryInterface $transactionRepository
     * @param \Amazon\Pay\Model\Adapter\AmazonPayAdapter $amazonAdapter
     * @param \Amazon\Pay\Logger\AsyncIpnLogger $asyncLogger
     * @param \Magento\Sales\Api\InvoiceRepositoryInterface $invoiceRepository
     * @param \Magento\Sales\Model\Service\InvoiceService $invoiceService
     * @param \Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface $transactionBuilder
     * @param \Magento\Framework\Notification\NotifierInterface $notifier
     * @param \Magento\Backend\Model\UrlInterface $urlBuilder
     * @param \Amazon\Pay\Model\AmazonConfig $amazonConfig
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     */
    public function __construct(
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Sales\Api\TransactionRepositoryInterface $transactionRepository,
        \Amazon\Pay\Model\Adapter\AmazonPayAdapter $amazonAdapter,
        \Amazon\Pay\Logger\AsyncIpnLogger $asyncLogger,
        \Magento\Sales\Api\InvoiceRepositoryInterface $invoiceRepository,
        \Magento\Sales\Model\Service\InvoiceService $invoiceService,
        \Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface $transactionBuilder,
        \Magento\Framework\Notification\NotifierInterface $notifier,
        \Magento\Backend\Model\UrlInterface $urlBuilder,
        \Amazon\Pay\Model\AmazonConfig $amazonConfig,
        \Magento\Framework\Event\ManagerInterface $eventManager
    ) {
        parent::__construct($orderRepository, $transactionRepository, $searchCriteriaBuilder);
        $this->amazonAdapter = $amazonAdapter;
        $this->asyncLogger = $asyncLogger;
        $this->invoiceRepository = $invoiceRepository;
        $this->invoiceService = $invoiceService;
        $this->transactionBuilder = $transactionBuilder;
        $this->notifier = $notifier;
        $this->urlBuilder = $urlBuilder;
        $this->amazonConfig = $amazonConfig;
        $this->eventManager = $eventManager;
    }

    /**
     * @param string $chargeId
     * @param OrderInterface $order
     * @return \Magento\Sales\Model\Order\Invoice
     */
    protected function loadInvoice($chargeId, OrderInterface $order)
    {
        $this->searchCriteriaBuilder->addFilter(InvoiceInterface::TRANSACTION_ID, $chargeId . '%', 'like');
        $this->searchCriteriaBuilder->addFilter(InvoiceInterface::ORDER_ID, $order->getEntityId());
        $this->searchCriteriaBuilder->setPageSize(1);
        $this->searchCriteriaBuilder->setCurrentPage(1);
        $searchCriteria = $this->searchCriteriaBuilder->create();
        $invoices = $this->invoiceRepository->getList($searchCriteria)->getItems();
        return count($invoices) ? current($invoices) : null;
    }

    /**
     * Process charge state change
     */
    public function processStateChange($chargeId)
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $this->loadOrder($chargeId);

        if ($order) {
            $invoice = $this->loadInvoice($chargeId, $order);
            if ($invoice && $invoice->getState() != Order\Invoice::STATE_OPEN) {
                // Could happen with duplicate IPN messages, don't want to try to capture unless not invoiced
                $this->asyncLogger->info('Duplicate IPN received after capture for Order #' . $order->getIncrementId());
                return true;
            }

            $charge = $this->amazonAdapter->getCharge($order->getStoreId(), $chargeId);

            // Compare Charge State with Order State
            if (isset($charge['statusDetails'])) {
                $state = $charge['statusDetails']['state'];
                if ($this->amazonConfig->getPaymentAction() == PaymentAction::AUTHORIZE_AND_CAPTURE &&
                    $state == 'Authorized') {
                    $this->amazonAdapter->captureCharge(
                        $order->getStoreId(),
                        $chargeId,
                        $order->getGrandTotal(),
                        $order->getOrderCurrencyCode()
                    );
                    $charge = $this->amazonAdapter->getCharge($order->getStoreId(), $chargeId);
                    $state = $charge['statusDetails']['state'];
                }

                $complete = false;

                switch ($state) {
                    case 'Declined':
                        $this->decline($order, $chargeId, $charge['statusDetails']);
                        $complete = true;
                        break;
                    case 'Canceled':
                        $this->setProcessing($order);
                        $this->cancel($order, $charge['statusDetails']);
                        $complete = true;
                        break;
                    case 'Authorized':
                        $this->authorize($order, $chargeId);
                        $complete = true;
                        break;
                    case 'Captured':
                        $this->setProcessing($order);
                        $this->capture($order, $chargeId, $charge['captureAmount']['amount']);
                        $complete = true;
                        break;
                    default:
                        break;
                }

                return $complete;
            }
        }

        return false;
    }

    /**
     * Decline charge
     *
     * @param \Magento\Sales\Model\Order $order
     * @param string $chargeId
     * @param string $reason
     */
    public function decline($order, $chargeId, $detail)
    {
        $invoice = $this->loadInvoice($chargeId, $order);
        if ($invoice) {
            $invoice->cancel();
            $order->addRelatedObject($invoice);
        }
        if ($order->canHold() || $order->isPaymentReview()) {
            $this->closeLastTransaction($order);
            $this->amazonAdapter->closeChargePermission(
                $order->getStoreId(),
                array_key_exists('charge_permission_id', $order->getPayment()->getAdditionalInformation()) ? $order->getPayment()->getAdditionalInformation()['charge_permission_id'] : "",
                'Canceled due to capture declined.',
                true
            );
            $this->setOrderState($order, 'canceled');
            $payment = $order->getPayment();
            $transaction = $this->transactionBuilder->setPayment($payment)
                ->setOrder($order)
                ->setTransactionId($chargeId)
                ->setFailSafe(true)
                ->build(Transaction::TYPE_AUTH);
            $payment->addTransactionCommentsToOrder($transaction, __('Capture declined') . '.');
            $order->save();

            $this->notifier->addNotice(
                __('Charge declined'),
                __('Charge declined for Order #%1', $order->getIncrementId()),
                $this->urlBuilder->getUrl('sales/order/view', ['order_id' => $order->getId()])
            );

            $this->asyncLogger->info('Charge declined for Order #' . $order->getIncrementId());
            $this->eventManager->dispatch('amazon_pay_async_payment_declined', ['order' => $order]);
        }
    }

    /**
     * Cancel charge
     *
     * @param \Magento\Sales\Model\Order $order
     */
    public function cancel($order, $detail)
    {
        if (!$order->isCanceled()) {
            $order->addStatusHistoryComment($detail['reasonCode'] . ' - ' . $detail['reasonDescription']);
            $order->cancel();
            $order->save();
            $this->asyncLogger->info('Canceled Order #' . $order->getIncrementId());
        }
    }

    /**
     * Authorize pending charge (AuthorizationInitiated)
     * @param \Magento\Sales\Model\Order $order
     * @param $chargeId
     */
    public function authorize($order, $chargeId)
    {
        if ($order->isPaymentReview()) {
            $this->setProcessing($order);
            $payment = $order->getPayment();

            $transaction = $this->transactionBuilder->setPayment($payment)
                ->setOrder($order)
                ->setTransactionId($chargeId)
                ->setFailSafe(true)
                ->build(Transaction::TYPE_AUTH);

            $formattedAmount = $order->getBaseCurrency()->formatTxt($payment->getBaseAmountAuthorized());
            if ($order->getBaseCurrencyCode() != $order->getOrderCurrencyCode()) {
                $formattedAmount = $formattedAmount .' ['. $order->formatPriceTxt($payment->getAmountOrdered()) .']';
            }
            $message = __('Authorized amount of %1.', $formattedAmount);
            $payment->addTransactionCommentsToOrder($transaction, $message);
            $payment->setIsTransactionClosed(false);
            $payment->setParentTransactionId($chargeId);

            $order->save();
            $this->asyncLogger->info('Set Processing for Order #' . $order->getIncrementId());
        }
    }

    /**
     * Capture charge
     *
     * @param \Magento\Sales\Model\Order|\Magento\Sales\Api\Data\OrderInterface $order
     * @param string $chargeId
     * @param float $chargeAmount
     */
    public function capture($order, $chargeId, $chargeAmount)
    {
        $invoice = $this->loadInvoice($chargeId, $order);
        if (!$invoice && $order->canInvoice()) {
            $invoice = $this->invoiceService->prepareInvoice($order);
            $invoice->register();
        }

        if ($invoice && ($invoice->canCapture() || $invoice->getOrder()->getStatus() == Order::STATE_PAYMENT_REVIEW)) {
            $order = $invoice->getOrder();
            $this->setProcessing($order);
            $payment = $order->getPayment();
            $invoice->setTransactionId($chargeId);

            $invoice->pay();
            $order->addRelatedObject($invoice);

            $transaction = $this->transactionBuilder->setPayment($payment)
                ->setOrder($order)
                ->setTransactionId($chargeId . '-capture')
                ->build(Transaction::TYPE_CAPTURE);

            $formattedAmount = $order->getBaseCurrency()->formatTxt($chargeAmount);
            if ($order->getBaseCurrencyCode() != $order->getOrderCurrencyCode()) {
                $formattedAmount = $formattedAmount .' ['. $order->formatPriceTxt($payment->getAmountOrdered()) .']';
            }
            $message = __('Captured amount of %1 online.', $formattedAmount);

            $payment->setDataUsingMethod('base_amount_paid_online', $chargeAmount);
            $payment->addTransactionCommentsToOrder($transaction, $message);
            $transaction->setIsClosed(true);
            $order->save();
            $this->asyncLogger->info('Captured Order #' . $order->getIncrementId());
        } else {
            if (!$invoice) {
                $errorMessage = 'Cannot create invoice for Order #' . $order->getIncrementId();
            } else {
                $errorMessage = 'Invoice cannot be captured -'
                                . ' Order #' . $order->getIncrementId()
                                . ' Order Status: ' . $invoice->getOrder()->getStatus();
            }

            $this->asyncLogger->error($errorMessage);
            $order->addStatusHistoryComment($errorMessage);
            $order->save();

        }
    }
}
