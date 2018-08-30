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

class CaptureStrategyCommand implements CommandInterface
{

    const SALE = 'sale';

    const CAPTURE = 'settlement';

    const AUTHORIZE_CAPTURE = 'capture';

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
     * @var Data
     */
    private $coreHelper;

    public function __construct(
        CommandPoolInterface $commandPool,
        TransactionRepositoryInterface $transactionRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        FilterBuilder $filterBuilder,
        Data $coreHelper
    ) {
        $this->commandPool = $commandPool;
        $this->transactionRepository = $transactionRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->coreHelper = $coreHelper;
    }

    /**
     * @inheritdoc
     */
    public function execute(array $commandSubject)
    {
        if (isset($commandSubject['payment'])) {
            $paymentDO = $commandSubject['payment'];
            $paymentInfo = $paymentDO->getPayment();
            ContextHelper::assertOrderPayment($paymentInfo);

            $command = $this->getCommand($paymentInfo);
            if ($command) {
                $this->commandPool->get($command)->execute($commandSubject);
            }
        }
    }

    /**
     * Get execution command name
     *
     * @param  OrderPaymentInterface $payment
     * @return string
     */
    private function getCommand(OrderPaymentInterface $payment)
    {
        $isCaptured = $this->captureTransactionExists($payment);

        // check if a transaction has happened and is captured
        if (!$payment->getAuthorizationTransaction() && !$isCaptured) {

            if ($this->coreHelper->getPaymentAction() == 'authorize_capture') {
                // charge on order
                return self::SALE;
            }
            else {
                // charge on invoice/shipment
                return self::AUTHORIZE_CAPTURE;
            }
        }

        // capture on settlement/invoice
        if (!$isCaptured && $payment->getAuthorizationTransaction()) {
            return self::CAPTURE;
        }

        // failed to determine action from prior tests, so use module settings
        if ($this->coreHelper->getPaymentAction() == 'authorize_capture') {
            self::SALE;
        }

        return self::AUTHORIZE_CAPTURE;
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
        return (boolean) $count;
    }
}
