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

use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Api\Data\TransactionInterface as Transaction;

abstract class AbstractOperation
{
    /**
     * @var \Magento\Sales\Api\TransactionRepositoryInterface
     */
    private $transactionRepository;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * AbstractOperation constructor.
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Sales\Api\TransactionRepositoryInterface $transactionRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Sales\Api\TransactionRepositoryInterface $transactionRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->orderRepository = $orderRepository;
        $this->transactionRepository = $transactionRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    protected function setOnHold(OrderInterface $order)
    {
        $this->setOrderState($order, Order::STATE_HOLDED);
    }

    protected function setProcessing(OrderInterface $order)
    {
        $this->setOrderState($order, Order::STATE_PROCESSING);
    }

    protected function setPaymentReview(OrderInterface $order)
    {
        $this->setOrderState($order, Order::STATE_PAYMENT_REVIEW);
    }

    protected function setOrderState(OrderInterface $order, $state)
    {
        $status = $order->getConfig()->getStateDefaultStatus($state);
        $order->setState($state)->setStatus($status);
    }

    /**
     * Load transaction.
     *
     * @param $transactionId
     * @param \Magento\Sales\Api\Data\TransactionInterface $type
     * @return mixed
     */
    protected function getTransaction($transactionId, $type = null)
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
     * Load order by transaction ID (chargeId)
     *
     * @param $transactionId
     * @return \Magento\Sales\Model\Order $order
     */
    protected function loadOrder($transactionId)
    {
        $transaction = $this->getTransaction($transactionId);
        if ($transaction) {
            return $this->orderRepository->get($transaction->getOrderId());
        }
    }

    /**
     * Close last transaction
     *
     * @param \Magento\Sales\Model\Order $order
     */
    protected function closeLastTransaction($order)
    {
        $transactionId = $order->getPayment()->getLastTransId();
        $transaction = $this->getTransaction($transactionId);
        $transaction->setIsClosed(true)->save();
    }
}
