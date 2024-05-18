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
use Amazon\Pay\Model\AsyncManagement\Charge;
use Amazon\Pay\Model\CheckoutSessionManagement;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Psr\Log\LoggerInterface;
use Amazon\Pay\Model\AsyncManagement\Charge as AsyncCharge;

class CleanUpIncompleteSessions
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
    protected $amazonPayAdapter;

    /**
     * @var CheckoutSessionManagement
     */
    protected $checkoutSessionManagement;

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var AsyncCharge
     */
    protected $asyncCharge;

    /**
     * @param TransactionHelper $transactionHelper
     * @param LoggerInterface $logger
     * @param AmazonPayAdapter $amazonPayAdapter
     * @param CheckoutSessionManagement $checkoutSessionManagement
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        TransactionHelper $transactionHelper,
        LoggerInterface $logger,
        AmazonPayAdapter $amazonPayAdapter,
        CheckoutSessionManagement $checkoutSessionManagement,
        OrderRepositoryInterface $orderRepository,
        AsyncCharge $asyncCharge
    ) {
        $this->transactionHelper = $transactionHelper;
        $this->logger = $logger;
        $this->amazonPayAdapter = $amazonPayAdapter;
        $this->checkoutSessionManagement = $checkoutSessionManagement;
        $this->orderRepository = $orderRepository;
        $this->asyncCharge = $asyncCharge;
    }

    /**
     * Execute cleanup
     *
     * @return void
     */
    public function execute()
    {
        // Get transactions
        $incompleteTransactionList = $this->transactionHelper->getIncomplete();

        // Process each transaction
        foreach ($incompleteTransactionList as $transactionData) {
            $this->processTransaction($transactionData);
        }

        $this->logger->info('Cleanup Incomplete Sessions cron job executed successfully.');
    }

    /**
     * Process a single transaction
     *
     * @param array $transactionData
     * @return void
     */
    private function processTransaction(array $transactionData)
    {
        $checkoutSessionId = $transactionData['checkout_session_id'];
        $orderId = $transactionData['order_id'];
        $storeId = $transactionData['store_id'];

        $this->logger->info('Cleaning up checkout session id: ' . $checkoutSessionId);

        try {

            // Check current state of Amazon checkout session
            $amazonSession = $this->amazonPayAdapter->getCheckoutSession(null, $checkoutSessionId);

            switch ($amazonSession['statusDetails']['state']) {
                case self::SESSION_STATUS_STATE_CANCELED:
                    $this->cancelOrder($orderId);
                    $this->transactionHelper->closeTransaction($transactionData['transaction_id']);
                    break;
                case self::SESSION_STATUS_STATE_OPEN:
                    if ($amazonSession['chargePermissionId'] == null) {
                        $this->cancelOrder($orderId);
                        $this->transactionHelper->closeTransaction($transactionData['transaction_id']);
                    } else {
                        $this->checkoutSessionManagement->completeCheckoutSession($checkoutSessionId, null, $orderId);
                    }
                    break;
                case self::SESSION_STATUS_STATE_COMPLETED:
                    $chargeId = $amazonSession['chargeId'];
                    // not sure why, but 2-3 local orders fell into this category, one of which was charged
                    // They may have been artifacts of intense manipulation while trying to reproduce, so possibly can ignore
                    // May need to process with retrofitted processStateChange
                    // or set chargeId and chargePermissionId, then make the call (also may need to create an async record)
                    // $this->asyncCharge->processStateChange($chargeId);
                    break;
            }
        } catch (\Exception $e) {
            $errorMessage = 'Unable to process checkoutSessionId: ' . $checkoutSessionId;
            $this->logger->error($errorMessage . '. ' . $e->getMessage());
        }
    }

    /**
     * Cancel the order
     *
     * @param int $orderId
     * @return void
     */
    private function cancelOrder($orderId)
    {
        $order = $this->loadOrder($orderId);

        if ($order) {
            $this->checkoutSessionManagement->cancelOrder($order);
        } else {
            $this->logger->error('Order not found for ID: ' . $orderId);
        }
    }

    /**
     * Load order by ID
     *
     * @param int $orderId
     * @return OrderInterface
     */
    private function loadOrder($orderId)
    {
        try {
            return $this->orderRepository->get($orderId);
        } catch (\Exception $e) {
            $this->logger->error('Error loading order: ' . $e->getMessage());
            return null;
        }
    }
}
