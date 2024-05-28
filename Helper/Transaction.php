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

namespace Amazon\Pay\Helper;

use Amazon\Pay\Gateway\Config\Config;
use Magento\Framework\App\ResourceConnection;
use Magento\Sales\Api\Data\TransactionInterface;
use Magento\Sales\Api\TransactionRepositoryInterface;
use Magento\Sales\Model\Order;

class Transaction
{

    protected const REGEX_PATTERN_UUID = '.{8}-.{4}-.{4}-.{4}-.{12}';

    // Length of time in minutes we wait before cleaning up the transaction
    protected const MIN_ORDER_AGE_MINUTES = 30;

    /**
     * @var int
     */
    private $limit;

    /**
     * @var ResourceConnection
     */
    private ResourceConnection $resourceConnection;

    /**
     * @var TransactionRepositoryInterface
     */
    private TransactionRepositoryInterface $transactionRepository;

    /**
     * @param ResourceConnection $resourceConnection
     * @param TransactionRepositoryInterface $transactionRepository
     * @param int $limit
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        TransactionRepositoryInterface $transactionRepository,
        int $limit = 100
    ) {
        $this->limit = $limit;
        $this->resourceConnection = $resourceConnection;
        $this->transactionRepository = $transactionRepository;
    }

    /**
     * Query for possible incomplete transactions
     *
     * @return array
     */
    public function getIncomplete()
    {
        // Do not process recent orders, synchronous ones need time to be
        // resolved in payment gateway on auth decline
        // todo confirm timeout length in gateway
        $maxOrderPlacedTime = $this->getMaxOrderPlacedTime();

        $connection = $this->resourceConnection->getConnection();

        // tables used to determine stalled order status
        $salesOrderTable = $connection->getTableName('sales_order');
        $salesOrderPaymentTable = $connection->getTableName('sales_order_payment');
        $salesPaymentTransaction = $connection->getTableName('sales_payment_transaction');
        $amazonPayAsyncTable = $connection->getTableName('amazon_payv2_async');

        // specifying(limiting) columns is unnecessary, but helpful for debugging
        // pending actions:
        // captures for "charge when order is placed" payment action
        // authorizations for "charge when shipped" payment action
        $tableFields = [
            'sales_order' => ['order_id' => 'entity_id', 'store_id', 'increment_id', 'created_at', 'state'],
            'sales_order_payment' => ['method'],
            'sales_payment_transaction' => ['transaction_id', 'checkout_session_id' => 'txn_id', 'is_closed'],
            'amazon_payv2_async' => ['pending_action', 'is_pending']
        ];

        $select = $connection->select()
            ->from(['so' => $salesOrderTable], $tableFields['sales_order'])
            ->joinLeft(
                ['sop' => $salesOrderPaymentTable],
                'so.entity_id = sop.parent_id',
                $tableFields['sales_order_payment']
            )
            ->joinLeft(
                ['spt' => $salesPaymentTransaction],
                'sop.entity_id = spt.payment_id',
                $tableFields['sales_payment_transaction']
            )
            ->joinLeft(
                ['apa' => $amazonPayAsyncTable],
                'spt.txn_id = apa.pending_id',
                $tableFields['amazon_payv2_async']
            )
            // No async record pending
            ->where('apa.pending_action IS NULL')
            // Order awaiting payment
            ->where("so.status = ?", Order::STATE_PAYMENT_REVIEW)
            // A transaction is not complete
            ->where('spt.is_closed <> ?', 1)
            // Delay processing new orders
            ->where('so.created_at <= ?', $maxOrderPlacedTime)
            // Amazon Pay orders only
            ->where('sop.method = ?', Config::CODE)
            // Only transactions that have not yet been swapped out with charge id
            // (checkoutSessionIds only)
            ->where('spt.txn_id REGEXP ?', self::REGEX_PATTERN_UUID)
            // Meter to reduce load
            ->limit($this->limit);

        // Return stalled orders
        return $connection->fetchAll($select);
    }

    /**
     * Close transaction and save
     *
     * @param mixed $transactionId
     * @return void
     */
    public function closeTransaction(mixed $transactionId)
    {
        $transaction = $this->transactionRepository->get($transactionId);
        $transaction->setIsClosed(true);
        $this->transactionRepository->save($transaction);
    }

    /**
     * Use db time to reduce likelihood of server/db time mismatch
     *
     * @return string
     */
    private function getMaxOrderPlacedTime()
    {
        // phpcs:ignore Magento2.SQL.RawQuery
        $query = 'SELECT NOW() - INTERVAL ' . self::MIN_ORDER_AGE_MINUTES . ' MINUTE';
        return $this->resourceConnection->getConnection()->fetchOne($query);
    }
}
