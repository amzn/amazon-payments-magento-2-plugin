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

namespace Amazon\Payment\Gateway\Command;

use Magento\Payment\Gateway\CommandInterface;
use Magento\Payment\Gateway\Command\CommandPoolInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Payment\Gateway\Helper\ContextHelper;
use Magento\Sales\Api\TransactionRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\FilterBuilder;
use Magento\Sales\Api\Data\TransactionInterface;
use Amazon\Core\Helper\Data;
use Amazon\Payment\Gateway\Data\Order\OrderAdapterFactory;

class CaptureStrategyCommand implements CommandInterface
{

    const SALE = 'sale';

    const CAPTURE = 'settlement';

    const PARTIAL_CAPTURE = 'partial_capture';

    /**
     * @var CommandPoolInterface
     */
    private $commandPool;

    /**
     * @var TransactionRepositoryInterface
     */
    private $transactionRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var FilterBuilder
     */
    private $filterBuilder;

    /**
     * @var OrderAdapterFactory
     */
    private $orderAdapterFactory;

    /**
     * @var Data
     */
    private $coreHelper;

    /**
     * CaptureStrategyCommand constructor.
     *
     * @param CommandPoolInterface $commandPool
     * @param TransactionRepositoryInterface $transactionRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param FilterBuilder $filterBuilder
     * @param Data $coreHelper
     * @param OrderAdapterFactory $orderAdapterFactory
     */
    public function __construct(
        CommandPoolInterface $commandPool,
        TransactionRepositoryInterface $transactionRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        FilterBuilder $filterBuilder,
        Data $coreHelper,
        OrderAdapterFactory $orderAdapterFactory
    ) {
        $this->commandPool = $commandPool;
        $this->transactionRepository = $transactionRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->coreHelper = $coreHelper;
        $this->orderAdapterFactory = $orderAdapterFactory;
    }

    /**
     * @inheritdoc
     */
    public function execute(array $commandSubject)
    {
        if (isset($commandSubject['payment'])) {
            $paymentDO = $commandSubject['payment'];
            $paymentInfo = $paymentDO->getPayment();

            // The magento order adapter doesn't expose everything we need to send a request to the AP API so we
            // need to use our own version with the details we need exposed in custom methods.
            $orderAdapter = $this->orderAdapterFactory->create(
                ['order' => $paymentInfo->getOrder()]
            );

            $commandSubject['partial_capture'] = false;
            $commandSubject['amazon_order_id'] = $orderAdapter->getAmazonOrderID();
            $commandSubject['multicurrency'] = $orderAdapter->getMulticurrencyDetails($commandSubject['amount']);

            ContextHelper::assertOrderPayment($paymentInfo);

            $command = $this->getCommand($paymentInfo);
            if ($command) {
                if ($command == self::PARTIAL_CAPTURE) {
                    $commandSubject['partial_capture'] = true;
                    $command = self::SALE;
                }
                $this->commandPool->get($command)->execute($commandSubject);
            }
        }
    }

    /**
     * Get execution command name - if there's an authorization, this is just a settlement, if not, could be
     * a partial capture situation where we need to completely auth and capture again against the same order
     *
     * @param  OrderPaymentInterface $payment
     * @return string
     */
    private function getCommand(OrderPaymentInterface $payment)
    {
        $isCaptured = $this->captureTransactionExists($payment);

        // If an authorization exists, we're going to settle it with a capture
        if (!$isCaptured && $payment->getAuthorizationTransaction()) {
            return self::CAPTURE;
        }

        // Item has already been captured - need to reauthorize and capture (partial capture)
        if ($isCaptured) {
            return self::PARTIAL_CAPTURE;
        }

        // We're in a situation where we need a reauth and capture.
        return self::SALE;
    }

    /**
     * Check if capture transaction already exists
     *
     * @param  OrderPaymentInterface $payment
     * @return bool
     */
    private function captureTransactionExists(OrderPaymentInterface $payment)
    {
        $this->searchCriteriaBuilder->addFilters(
            [
                $this->filterBuilder
                    ->setField('payment_id')
                    ->setValue($payment->getId())
                    ->create(),
            ]
        );

        $this->searchCriteriaBuilder->addFilters(
            [
                $this->filterBuilder
                    ->setField('txn_type')
                    ->setValue(TransactionInterface::TYPE_CAPTURE)
                    ->create(),
            ]
        );

        $searchCriteria = $this->searchCriteriaBuilder->create();

        $count = $this->transactionRepository->getList($searchCriteria)->getTotalCount();
        return (boolean)$count;
    }
}
