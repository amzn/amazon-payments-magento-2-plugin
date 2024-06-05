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

    protected const LOG_PREFIX = 'AmazonCleanUpIncompleteSesssions: ';

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
     * @param AsyncCharge $asyncCharge
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
    }

    /**
     * Process a single transaction
     *
     * @param array $transactionData
     * @return void
     */
    protected function processTransaction(array $transactionData)
    {
        $checkoutSessionId = $transactionData['checkout_session_id'];
        $orderId = $transactionData['order_id'];

        $this->logger->info(self::LOG_PREFIX . 'Cleaning up checkout session id: ' . $checkoutSessionId);

        try {

            // Check current state of Amazon checkout session
            $amazonSession = $this->amazonPayAdapter->getCheckoutSession(null, $checkoutSessionId);
            $state = $amazonSession['statusDetails']['state'] ?? false;
            switch ($state) {
                case self::SESSION_STATUS_STATE_CANCELED:
                    $logMessage = 'Checkout session Canceled, cancelling order and closing transaction: ';
                    $logMessage .= $checkoutSessionId;
                    $this->logger->info(self::LOG_PREFIX . $logMessage);
                    $this->cancelOrder($orderId);
                    $this->transactionHelper->closeTransaction($transactionData['transaction_id']);
                    break;
                case self::SESSION_STATUS_STATE_OPEN:
                    $logMessage = 'Checkout session Open, completing: ';
                    $logMessage .= $checkoutSessionId;
                    $this->logger->info(self::LOG_PREFIX . $logMessage);
                    $this->checkoutSessionManagement->completeCheckoutSession($checkoutSessionId, null, $orderId);
                    break;
                case self::SESSION_STATUS_STATE_COMPLETED:
                    $logMessage = 'Checkout session Completed, nothing more needed: ';
                    $logMessage .= $checkoutSessionId;
                    $this->logger->info(self::LOG_PREFIX . $logMessage);
                    break;
            }
        } catch (\Exception $e) {
            $errorMessage = 'Unable to process checkoutSessionId: ' . $checkoutSessionId;
            $this->logger->error(self::LOG_PREFIX . $errorMessage . '. ' . $e->getMessage());
        }
    }

    /**
     * Cancel the order
     *
     * @param int $orderId
     * @return void
     */
    protected function cancelOrder($orderId)
    {
        $order = $this->loadOrder($orderId);

        if ($order) {
            $this->checkoutSessionManagement->cancelOrder($order);
        } else {
            $this->logger->error(self::LOG_PREFIX . 'Order not found for ID: ' . $orderId);
        }
    }

    /**
     * Load order by ID
     *
     * @param int $orderId
     * @return OrderInterface
     */
    protected function loadOrder($orderId)
    {
        try {
            return $this->orderRepository->get($orderId);
        } catch (\Exception $e) {
            $this->logger->error(self::LOG_PREFIX . 'Error loading order: ' . $e->getMessage());
            return null;
        }
    }
}
