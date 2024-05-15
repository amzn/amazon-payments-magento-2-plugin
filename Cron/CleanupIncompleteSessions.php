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
    public const SESSION_STATUS_STATE_CANCELED = 'Canceled';
    public const SESSION_STATUS_STATE_OPEN = 'Open';
    public const SESSION_STATUS_STATE_COMPLETED = 'Completed';

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
        $transactionDataList = $this->transactionHelper->getIncompleteTransactions();

        // Process each transaction
        foreach ($transactionDataList as $transactionData) {
            $checkoutSessionId = $transactionData['checkout_session_id'];
            $order = $transactionData['order'];
            $orderId = $order->getId();
            $this->logger->info('Cleaning up checkout session id: '. $checkoutSessionId);

            // check current state of Amazon checkout session
            $amazonSession = $this->amazonPayAdapter->getCheckoutSession($order->getStoreId(), $checkoutSessionId);

            if ($amazonSession['statusDetails']['state'] == self::SESSION_STATUS_STATE_CANCELED) {
                $this->checkoutSessionManagement->cancelOrder($order);
                $this->transactionHelper->closeTransaction($transactionData['transaction']);
            } elseif ($amazonSession['statusDetails']['state'] == self::SESSION_STATUS_STATE_OPEN) {
                if ($amazonSession['chargePermissionId'] == null) {
                    // something failed after place order, but before authorization in payment gateway
                    // todo verify transaction cleanup candidates won't include those post
                    //    decline in the process of choosing a new payment method on Amazon page
                    $this->checkoutSessionManagement->cancelOrder($order);
                    $this->transactionHelper->closeTransaction($transactionData['transaction']);
                } else {
                    // Something prevented redirect back to Magento after authorization in payment gateway
                    $this->checkoutSessionManagement->completeCheckoutSession($checkoutSessionId, null, $orderId);
                }
            } elseif ($amazonSession['statusDetails']['state'] == self::SESSION_STATUS_STATE_COMPLETED) {
                // No need to handle, only a customization could cause failure here.
            }

        }

        $this->logger->info('Cleanup Incomplete Sessions cron job executed successfully.');
    }
}
