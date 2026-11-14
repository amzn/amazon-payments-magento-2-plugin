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
declare(strict_types=1);

namespace Amazon\Pay\Test\Unit\Model\AsyncManagement;

use Amazon\Pay\Model\AsyncManagement\AbstractOperation;
use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\TransactionRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;
use Magento\Sales\Model\Order\Payment\Transaction;
use Magento\Sales\Model\ResourceModel\Order\Payment\Transaction\Collection as TransactionCollection;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;

class AbstractOperationTest extends TestCase
{
    /**
     * @var TransactionRepositoryInterface|MockObject
     */
    private $transactionRepository;

    /**
     * @var AbstractOperation
     */
    private $operation;

    protected function setUp(): void
    {
        $searchCriteriaBuilder = $this->createStub(SearchCriteriaBuilder::class);
        $searchCriteriaBuilder->method('addFilter')->willReturnSelf();
        $searchCriteriaBuilder->method('create')->willReturn($this->createStub(SearchCriteria::class));
        $this->transactionRepository = $this->createMock(TransactionRepositoryInterface::class);

        $this->operation = new class(
            $this->createStub(OrderRepositoryInterface::class),
            $this->transactionRepository,
            $searchCriteriaBuilder
        ) extends AbstractOperation {
            public function close($order)
            {
                $this->closeLastTransaction($order);
            }
        };
    }

    public function testClosesFoundTransaction(): void
    {
        $transaction = $this->createMock(Transaction::class);
        $transaction->expects($this->once())->method('setIsClosed')->with(true)->willReturnSelf();
        $transaction->expects($this->once())->method('save')->willReturnSelf();
        $this->transactionRepository->expects($this->once())
            ->method('getList')
            ->willReturn($this->createCollection($transaction));

        $this->operation->close($this->createOrder('P01-1234567-1234567-C123456'));
    }

    public function testSkipsMissingTransaction(): void
    {
        $collection = $this->createMock(TransactionCollection::class);
        $collection->method('count')->willReturn(0);
        $collection->expects($this->never())->method('getFirstItem');
        $this->transactionRepository->expects($this->once())->method('getList')->willReturn($collection);

        $this->operation->close($this->createOrder('0b6c5f1e-7c2a-4d8e-9f3b-2a1d4c5e6f70'));
    }

    /**
     * @param string|null $lastTransId
     */
    #[DataProvider('emptyLastTransIdProvider')]
    public function testSkipsLookupForEmptyLastTransId($lastTransId): void
    {
        $this->transactionRepository->expects($this->never())->method('getList');

        $this->operation->close($this->createOrder($lastTransId));
    }

    public static function emptyLastTransIdProvider(): array
    {
        return [
            'null' => [null],
            'empty string' => [''],
        ];
    }

    /**
     * @param Transaction|null $transaction
     * @return TransactionCollection|Stub
     */
    private function createCollection($transaction)
    {
        $collection = $this->createStub(TransactionCollection::class);
        $collection->method('count')->willReturn($transaction ? 1 : 0);
        $collection->method('getFirstItem')->willReturn($transaction);

        return $collection;
    }

    /**
     * @param string|null $lastTransId
     * @return Order|Stub
     */
    private function createOrder($lastTransId)
    {
        $payment = $this->createStub(Payment::class);
        $payment->method('getLastTransId')->willReturn($lastTransId);
        $order = $this->createStub(Order::class);
        $order->method('getPayment')->willReturn($payment);

        return $order;
    }
}
