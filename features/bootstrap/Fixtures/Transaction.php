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
namespace Fixtures;

use Bex\Behat\Magento2InitExtension\Fixtures\BaseFixture;
use Fixtures\Order as OrderFixture;
use Magento\Sales\Api\TransactionRepositoryInterface;

class Transaction extends BaseFixture
{
    /**
     * @var OrderFixture
     */
    private $orderFixture;

    public function __construct()
    {
        parent::__construct();
        $this->orderFixture = new OrderFixture;
    }

    public function getByTransactionId($transactionId, $paymentId, $orderId)
    {
        $repository = $this->createMagentoObject(TransactionRepositoryInterface::class);
        return $repository->getByTransactionId($transactionId, $paymentId, $orderId);
    }

    public function getByTransactionType($transactionType, $paymentId, $orderId)
    {
        $repository = $this->createMagentoObject(TransactionRepositoryInterface::class);
        return $repository->getByTransactionType($transactionType, $paymentId, $orderId);
    }

    public function getLastTransactionForLastOrder($email)
    {
        $lastOrder = $this->orderFixture->getLastOrderForCustomer($email);

        $transactionId = $lastOrder->getPayment()->getLastTransId();
        $paymentId     = $lastOrder->getPayment()->getId();
        $orderId       = $lastOrder->getId();

        $transaction = $this->getByTransactionId($transactionId, $paymentId, $orderId);

        if ( ! $transaction) {
            throw new \Exception('Last transaction not found for ' . $email);
        }

        return $transaction;
    }
}