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

namespace Amazon\Pay\Cron;

use Amazon\Pay\Helper\Transaction as TransactionHelper;
use Amazon\Pay\Model\Adapter\AmazonPayAdapter;
use Amazon\Pay\Model\CheckoutSessionManagement;
use Psr\Log\LoggerInterface;

class CleanupIncompleteSessions
{
    const SESSION_STATUS_STATE_CANCELED = 'Canceled';
    const SESSION_STATUS_STATE_OPEN = 'Open';
    const SESSION_STATUS_STATE_CLOSED = 'Closed';

    /**
     * @var TransactionHelper
     */
    protected $transactionHelper;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var AmazonPayAdapter
     */
    private $amazonPayAdapter;

    /**
     * @var CheckoutSessionManagement
     */
    private $checkoutSessionManagement;

    /**
     * @param TransactionHelper $transactionHelper
     * @param LoggerInterface $logger
     * @param AmazonPayAdapter $amazonPayAdapter
     * @param CheckoutSessionManagement $checkoutSessionManagement
     */
    public function __construct(
        TransactionHelper $transactionHelper,
        LoggerInterface $logger,
        AmazonPayAdapter $amazonPayAdapter,
        CheckoutSessionManagement $checkoutSessionManagement
    ) {
        $this->transactionHelper = $transactionHelper;
        $this->logger = $logger;
        $this->amazonPayAdapter = $amazonPayAdapter;
        $this->checkoutSessionManagement = $checkoutSessionManagement;
    }

    /**
     * Execute cleanup
     *
     * @return void
     */
    public function execute()
    {
        // Get transactions
        $transactions = $this->transactionHelper->getIncompleteTransactions();

        // Process each transaction
        foreach ($transactions as $transaction) {
            $checkoutSessionId = $transaction['checkout_session_id'];
            $order = $transaction['order'];
            $this->logger->info('Cleaning up checkout session id: '. $checkoutSessionId);

            // check current state of Amazon checkout session
            $amazonSession = $this->amazonPayAdapter->getCheckoutSession($order->getStoreId(), $checkoutSessionId);

            // todo: states that potentially still need handled
            // Order placed, but payment not authorized, redirect to payment gateway failed, needs canceled.

            if ($amazonSession['statusDetails']['state'] == 'Canceled') {
                // todo test
                $this->checkoutSessionManagement->cancelOrder($order);
            } elseif ($amazonSession['statusDetails']['state'] == 'Open') {
                // Something prevented redirect back to magento after authorization in payment gateway
                $this->checkoutSessionManagement->completeCheckoutSession($checkoutSessionId, null, $order->getId());
            } elseif ($amazonSession['statusDetails']['state'] == 'Closed') {
                // is completed, replace checkoutsessionId with txn id
                // todo implement and test
            }

        }

        $this->logger->info('Cleanup Incomplete Sessions cron job executed successfully.');
    }
}
