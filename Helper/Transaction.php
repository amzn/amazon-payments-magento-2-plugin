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

use Magento\Sales\Api\TransactionRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Sales\Model\Order;
use Magento\Framework\Stdlib\DateTime\DateTime;

class Transaction
{
    /**
     * @var TransactionRepositoryInterface
     */
    protected $transactionRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @var int
     */
    private $limit;

    /**
     * @param TransactionRepositoryInterface $transactionRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param DateTime $dateTime
     * @param $limit
     */
    public function __construct(
        TransactionRepositoryInterface $transactionRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        DateTime $dateTime,
        $limit = 100
    ) {
        $this->transactionRepository = $transactionRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->dateTime = $dateTime;
    }

    /**
     * Query for possible incomplete transactions
     *
     * @return array
     */
    public function getIncompleteTransactions()
    {
        // Get current timestamp and timestamp from 24 hours ago
        $twentyFourHoursAgo = $this->dateTime->gmtDate(null, '-24 hours');
        $fiveMinutesAgo = $this->dateTime->gmtDate(null, '-5 minutes');

        // Prepare criteria for the search
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('is_closed', 0)
            ->addFilter('txn_type', 'authorization')
            ->addFilter('created_at', $twentyFourHoursAgo, 'gteq')
            ->addFilter('created_at', $fiveMinutesAgo, 'lteq')
            ->setPageSize($this->limit)
            ->create();


        // Fetch transactions
        $transactionList = $this->transactionRepository->getList($searchCriteria)->getItems();

        // todo test if necessary to filter out records queued for async processing
//        $transactionList = $this->filterAsyncOrders($transactionList);

        // Filter transactions by order status 'payment_review' and not charge id
        $filteredTransactions = [];
        foreach ($transactionList as $transaction) {
            if($this->isChargeId($transaction->getTxnId())) {
                continue;
            }
            $order = $transaction->getOrder();
            if ($order && $order->getState() == Order::STATE_PAYMENT_REVIEW) {
                $filteredTransactions[] = [
                    'checkout_session_id' => $transaction->getTxnId(),
                    'order' => $order
                ];
            }
        }

        return $filteredTransactions;
    }

    /**
     * Check if the first 3 characters of txn_id start with either S01 or P01
     *
     * @param string $txnId
     * @return bool
     */
    protected function isChargeId($txnId)
    {
        // todo verify this is an adequate check
        $prefix = substr($txnId, 0, 3);
        return $prefix === 'S01' || $prefix === 'P01';
    }

//    private function filterAsyncOrders(array $transactionList)
//    {
//
//
//        return $filteredTransactions;
//    }
}
