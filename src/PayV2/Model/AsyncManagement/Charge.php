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

namespace Amazon\PayV2\Model\AsyncManagement;

use Magento\Sales\Api\Data\TransactionInterface as Transaction;
use Magento\Sales\Api\Data\InvoiceInterface;
use Magento\Sales\Api\Data\OrderInterface;

class Charge extends AbstractOperation
{
    /**
     * @var \Amazon\PayV2\Model\Adapter\AmazonPayV2Adapter
     */
    private $amazonAdapter;

    /**
     * @var \Amazon\PayV2\Logger\AsyncIpnLogger
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
     * Charge constructor.
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Sales\Api\TransactionRepositoryInterface $transactionRepository
     * @param \Amazon\PayV2\Model\Adapter\AmazonPayV2Adapter $amazonAdapter
     * @param \Magento\Sales\Api\InvoiceRepositoryInterface $invoiceRepository
     * @param \Magento\Sales\Model\Service\InvoiceService $invoiceService
     * @param \Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface $transactionBuilder
     * @param \Magento\Framework\Notification\NotifierInterface $notifier
     * @param \Magento\Backend\Model\UrlInterface $urlBuilder
     */
    public function __construct(
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Sales\Api\TransactionRepositoryInterface $transactionRepository,
        \Amazon\PayV2\Model\Adapter\AmazonPayV2Adapter $amazonAdapter,
        \Amazon\PayV2\Logger\AsyncIpnLogger $asyncLogger,
        \Magento\Sales\Api\InvoiceRepositoryInterface $invoiceRepository,
        \Magento\Sales\Model\Service\InvoiceService $invoiceService,
        \Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface $transactionBuilder,
        \Magento\Framework\Notification\NotifierInterface $notifier,
        \Magento\Backend\Model\UrlInterface $urlBuilder
    ) {
        parent::__construct($orderRepository, $transactionRepository, $searchCriteriaBuilder);
        $this->amazonAdapter = $amazonAdapter;
        $this->asyncLogger = $asyncLogger;
        $this->invoiceRepository = $invoiceRepository;
        $this->invoiceService = $invoiceService;
        $this->transactionBuilder = $transactionBuilder;
        $this->notifier = $notifier;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * @param string $chargeId
     * @param OrderInterface $order
     * @return \Magento\Sales\Model\Order\Invoice
     */
    protected function loadInvoice($chargeId, OrderInterface $order)
    {
        $this->searchCriteriaBuilder->addFilter(InvoiceInterface::TRANSACTION_ID, $chargeId . '-capture');
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
            $charge = $this->amazonAdapter->getCharge($order->getStoreId(), $chargeId);

            // Compare Charge State with Order State
            if (isset($charge['statusDetails'])) {
                switch ($charge['statusDetails']['state']) {
                    case 'Declined':
                        $this->decline($order, $chargeId, $charge['statusDetails']['reasonDescription']);
                        break;
                    case 'Canceled':
                        $this->cancel($order, $charge['statusDetails']);
                        break;
                    case 'Authorized':
                        $this->authorize($order, $chargeId);
                        break;
                    case 'Captured':
                        $this->capture($order, $chargeId, $charge['captureAmount']['amount']);
                        break;
                }
            }
        }
    }

    /**
     * Decline charge
     *
     * @param \Magento\Sales\Model\Order $order
     * @param string $chargeId
     * @param string $reason
     */
    public function decline($order, $chargeId, $reason)
    {
        $invoice = $this->loadInvoice($chargeId, $order);
        if ($invoice) {
            $invoice->cancel();
            $order->addRelatedObject($invoice);
        }
        if ($order->canHold() || $order->isPaymentReview()) {
            $this->setOnHold($order);
            $this->closeLastTransaction($order);
            $order->addStatusHistoryComment($reason);
            $order->save();

            $this->notifier->addNotice(
                __('Charge declined'),
                __('Charge declined for Order #%1', $order->getIncrementId()),
                $this->urlBuilder->getUrl('sales/order/view', ['order_id' => $order->getId()])
            );

            $this->asyncLogger->info('Charge declined for Order #' . $order->getIncrementId());
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
     * @param \Magento\Sales\Model\Order $order
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
        if ($invoice) {
            $payment = $order->getPayment();

            $invoice->pay();
            $order->addRelatedObject($invoice);

            $transaction = $this->transactionBuilder->setPayment($payment)
                ->setOrder($order)
                ->setTransactionId($chargeId . '-capture')
                ->build(Transaction::TYPE_CAPTURE);

            $formattedAmount = $order->getBaseCurrency()->formatTxt($chargeAmount);
            $message = __('Captured amount of %1 online.', $formattedAmount);

            $payment->setDataUsingMethod('base_amount_paid_online', $chargeAmount);
            $payment->addTransactionCommentsToOrder($transaction, $message);
            $this->setProcessing($order);
            $order->save();
            $this->asyncLogger->info('Captured Order #' . $order->getIncrementId());
        }
    }
}
