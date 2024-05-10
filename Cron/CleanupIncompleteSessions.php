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
use Psr\Log\LoggerInterface;

class CleanupIncompleteSessions
{
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
     * @param TransactionHelper $transactionHelper
     * @param LoggerInterface $logger
     * @param AmazonPayAdapter $amazonPayAdapter
     */
    public function __construct(
        TransactionHelper $transactionHelper,
        LoggerInterface $logger,
        AmazonPayAdapter $amazonPayAdapter
    ) {
        $this->transactionHelper = $transactionHelper;
        $this->logger = $logger;
        $this->amazonPayAdapter = $amazonPayAdapter;
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
            // todo process transactions
            // states that potentially need handled
            // Order placed, but payment not authorized, probably needs canceled.
            //     failure before redirect to amazon
            // Order placed, authorized, but complete checkout session still needs called.
            //     failure to redirect back to magento
            //     checkout_session_id still needs replaced with chargeId
            $checkoutSessionId = $transaction['checkout_session_id'];
            $order = $transaction['order'];

            // todo - check for completed state (may be handled by checkoutSessionManagement->completeCheckoutSession)
            $this->amazonPayAdapter->getCheckoutSession($order->getStoreId(), $checkoutSessionId);

            // todo - call complete checkout session on the management class, modify to accept orderId

            $this->logger->info('Processing transaction: ' . $transaction->getId());
        }

        $this->logger->info('Cleanup Incomplete Sessions cron job executed successfully.');
    }
}
