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

use Magento\Sales\Api\Data\TransactionInterface as Transaction;

class Refund extends AbstractOperation
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
     * Refund constructor.
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Sales\Api\TransactionRepositoryInterface $transactionRepository
     * @param \Amazon\Pay\Model\Adapter\AmazonPayAdapter $amazonAdapter
     * @param \Amazon\Pay\Logger\AsyncIpnLogger $asyncLogger
     * @param \Magento\Sales\Model\Service\InvoiceService $invoiceService
     * @param \Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface $transactionBuilder
     * @param \Magento\Framework\Notification\NotifierInterface $notifier
     * @param \Magento\Backend\Model\UrlInterface $urlBuilder
     */
    public function __construct(
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Sales\Api\TransactionRepositoryInterface $transactionRepository,
        \Amazon\Pay\Model\Adapter\AmazonPayAdapter $amazonAdapter,
        \Amazon\Pay\Logger\AsyncIpnLogger $asyncLogger,
        \Magento\Sales\Model\Service\InvoiceService $invoiceService,
        \Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface $transactionBuilder,
        \Magento\Framework\Notification\NotifierInterface $notifier,
        \Magento\Backend\Model\UrlInterface $urlBuilder
    ) {
        parent::__construct($orderRepository, $transactionRepository, $searchCriteriaBuilder);
        $this->amazonAdapter = $amazonAdapter;
        $this->asyncLogger = $asyncLogger;
        $this->invoiceService = $invoiceService;
        $this->transactionBuilder = $transactionBuilder;
        $this->notifier = $notifier;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * Verify refund
     */
    public function processRefund($refundId)
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $this->loadOrder($refundId);

        if ($order) {
            $refund = $this->amazonAdapter->getRefund($order->getStoreId(), $refundId);
            if (isset($refund['statusDetails']) && $refund['statusDetails']['state'] == 'Declined') {

                $order->addStatusHistoryComment($refund['statusDetails']['reasonDescription']);
                $order->save();

                $this->notifier->addNotice(
                    __('Refund declined'),
                    __('Refund declined for Order #%1', $order->getIncrementId()),
                    $this->urlBuilder->getUrl('sales/order/view', ['order_id' => $order->getId()])
                );

                $this->asyncLogger->info('Refund declined for Order #' . $order->getIncrementId());

                return true;
            }
        }
    }
}
