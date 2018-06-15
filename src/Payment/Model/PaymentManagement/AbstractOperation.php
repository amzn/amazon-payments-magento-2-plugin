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

use Magento\Backend\Model\UrlInterface;
use Magento\Framework\Api\SearchCriteriaBuilderFactory;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Notification\NotifierInterface;
use Magento\Payment\Model\InfoInterface as PaymentInfoInterface;
use Magento\Sales\Api\Data\InvoiceInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Api\Data\TransactionInterface;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use Magento\Sales\Api\OrderPaymentRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\TransactionRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment\Transaction;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

abstract class AbstractOperation
{
    /**
     * @var SearchCriteriaBuilderFactory
     */
    private $searchCriteriaBuilderFactory;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var NotifierInterface
     */
    private $notifier;

    /**
     * @var InvoiceRepositoryInterface
     */
    private $invoiceRepository;

    /**
     * AbstractOperation constructor.
     * @param \Magento\Framework\Notification\NotifierInterface   $notifier
     * @param \Magento\Backend\Model\UrlInterface                 $urlBuilder
     * @param \Magento\Framework\Api\SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory
     * @param \Magento\Sales\Api\InvoiceRepositoryInterface       $invoiceRepository
     */
    public function __construct(
        NotifierInterface $notifier,
        UrlInterface $urlBuilder,
        SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory,
        InvoiceRepositoryInterface $invoiceRepository
    ) {
        $this->notifier                     = $notifier;
        $this->urlBuilder                   = $urlBuilder;
        $this->searchCriteriaBuilderFactory = $searchCriteriaBuilderFactory;
        $this->invoiceRepository            = $invoiceRepository;
    }

    /**
     * @param                                        $transactionId
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @return \Magento\Sales\Api\Data\InvoiceInterface|mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getInvoice($transactionId, OrderInterface $order)
    {
        $searchCriteriaBuilder = $this->searchCriteriaBuilderFactory->create();

        $searchCriteriaBuilder->addFilter(
            InvoiceInterface::TRANSACTION_ID,
            $transactionId
        );

        $searchCriteriaBuilder->addFilter(
            InvoiceInterface::ORDER_ID,
            $order->getId()
        );

        $searchCriteria = $searchCriteriaBuilder
            ->setPageSize(1)
            ->setCurrentPage(1)
            ->create();

        $invoiceList = $this->invoiceRepository->getList($searchCriteria);

        if (count($items = $invoiceList->getItems())) {
            $invoice = current($items);
            $invoice->setOrder($order);
            return $invoice;
        }

        throw new NoSuchEntityException();
    }

    /**
     * @param                                        $transactionId
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @return \Magento\Sales\Api\Data\InvoiceInterface|mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getInvoiceAndSetPaid($transactionId, OrderInterface $order)
    {
        $invoice = $this->getInvoice($transactionId, $order);
        $invoice->pay();
        $order->addRelatedObject($invoice);

        return $invoice;
    }

    /**
     * @param                                        $transactionId
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @return \Magento\Sales\Api\Data\InvoiceInterface|mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getInvoiceAndSetCancelled($transactionId, OrderInterface $order)
    {
        $invoice = $this->getInvoice($transactionId, $order);
        $invoice->cancel();
        $order->addRelatedObject($invoice);

        return $invoice;
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     */
    protected function setOnHold(OrderInterface $order)
    {
        $this->setOrderState($order, Order::STATE_HOLDED);
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     */
    protected function setProcessing(OrderInterface $order)
    {
        $this->setOrderState($order, Order::STATE_PROCESSING);
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     */
    protected function setPaymentReview(OrderInterface $order)
    {
        $this->setOrderState($order, Order::STATE_PAYMENT_REVIEW);
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @param                                        $state
     */
    protected function setOrderState(OrderInterface $order, $state)
    {
        $status = $order->getConfig()->getStateDefaultStatus($state);
        $order->setState($state)->setStatus($status);
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     */
    protected function addCaptureDeclinedNotice(OrderInterface $order)
    {
        $orderUrl = $this->urlBuilder->getUrl('sales/order/view', ['order_id' => $order->getId()]);

        $this->notifier->addNotice(
            __('Capture declined'),
            __('Capture declined for Order #%1', $order->getIncrementId()),
            $orderUrl
        );
    }
}
