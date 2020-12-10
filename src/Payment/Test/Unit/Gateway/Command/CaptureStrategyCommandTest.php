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
namespace Amazon\Payment\Test\Unit\Gateway\Command;

use Amazon\Payment\Gateway\Command\CaptureStrategyCommand;
use Amazon\Core\Helper\Data;
use Amazon\Payment\Gateway\Data\Order\OrderAdapterFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\SearchCriteria;
use Magento\Payment\Gateway\Command\CommandPoolInterface;
use Magento\Payment\Gateway\Command\GatewayCommand;
use Magento\Payment\Gateway\Data\OrderAdapterInterface;
use Magento\Payment\Gateway\Data\PaymentDataObject;
use Magento\Sales\Api\TransactionRepositoryInterface;
use Magento\Sales\Model\Order\Payment;
use Magento\Sales\Model\ResourceModel\Order\Payment\Transaction\CollectionFactory;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Class CaptureStrategyCommandTest
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 *
 * @deprecated As of February 2021, this Legacy Amazon Pay plugin has been
 * deprecated, in favor of a newer Amazon Pay version available through GitHub
 * and Magento Marketplace. Please download the new plugin for automatic
 * updates and to continue providing your customers with a seamless checkout
 * experience. Please see https://pay.amazon.com/help/E32AAQBC2FY42HS for details
 * and installation instructions.
 */
class CaptureStrategyCommandTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var CaptureStrategyCommand
     */
    private $strategyCommand;

    /**
     * @var CommandPoolInterface|MockObject
     */
    private $commandPool;

    /**
     * @var TransactionRepositoryInterface|MockObject
     */
    private $transactionRepository;

    /**
     * @var FilterBuilder|MockObject
     */
    private $filterBuilder;

    /**
     * @var SearchCriteriaBuilder|MockObject
     */
    private $searchCriteriaBuilder;

    /**
     * @var Payment|MockObject
     */
    private $payment;

    /**
     * @var GatewayCommand|MockObject
     */
    private $command;

    /**
     * @var Data|MockObject
     */
    private $coreHelper;

    /**
     * @var OrderAdapterFactory|MockObject
     */
    private $orderAdapterFactory;

    /**
     * Sets up base classes needed to mock the command strategy class
     */
    protected function setUp(): void
    {
        $this->commandPool = $this->getMockBuilder(CommandPoolInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['get', '__wakeup'])
            ->getMock();

        $this->initCommandMock();
        $this->initTransactionRepositoryMock();
        $this->initFilterBuilderMock();
        $this->initSearchCriteriaBuilderMock();
        $this->initOrderAdapterFactoryMock();

        $this->coreHelper = $this->getMockBuilder(\Amazon\Core\Helper\Data::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->strategyCommand = new CaptureStrategyCommand(
            $this->commandPool,
            $this->transactionRepository,
            $this->searchCriteriaBuilder,
            $this->filterBuilder,
            $this->coreHelper,
            $this->orderAdapterFactory
        );
    }

    /**
     * Tests if command strategy class returns correct command value when item is authorized but not captured
     * @throws \Magento\Payment\Gateway\Command\CommandException
     */
    public function testSaleExecute()
    {
        $paymentData = $this->getPaymentDataObjectMock();
        $subject['payment'] = $paymentData;

        $this->payment->method('getAuthorizationTransaction')
            ->willReturn(false);

        $this->payment->method('getId')
            ->willReturn(1);

        $this->coreHelper->method('getPaymentAction')->willReturn('authorize_capture');

        $this->buildSearchCriteria();

        $this->transactionRepository->method('getTotalCount')
            ->willReturn(0);

        $this->commandPool->method('get')
            ->with(CaptureStrategyCommand::SALE)
            ->willReturn($this->command);

        $this->strategyCommand->execute($subject);
    }

    /**
     * Tests if command strategy class returns correct command value when item is to be authorized and captured
     * @throws \Magento\Payment\Gateway\Command\CommandException
     */
    public function testCaptureExecute()
    {
        $paymentData = $this->getPaymentDataObjectMock();
        $subject['payment'] = $paymentData;
        $lastTransId = 'transaction_id';

        $this->payment->method('getAuthorizationTransaction')
            ->willReturn(true);

        $this->payment->method('getLastTransId')
            ->willReturn($lastTransId);

        $this->payment->method('getId')
            ->willReturn(1);

        $this->buildSearchCriteria();

        $this->transactionRepository->method('getTotalCount')
            ->willReturn(0);

        $this->commandPool->method('get')
            ->with(CaptureStrategyCommand::CAPTURE)
            ->willReturn($this->command);

        $this->strategyCommand->execute($subject);
    }


    /**
     * Creates mock for payment data object and order payment
     * @return MockObject
     */
    private function getPaymentDataObjectMock()
    {
        $this->payment = $this->getMockBuilder(Payment::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mock = $this->getMockBuilder(PaymentDataObject::class)
            ->setMethods(['getPayment', 'getOrder'])
            ->disableOriginalConstructor()
            ->getMock();

        $mock->method('getPayment')
            ->willReturn($this->payment);

        $order = $this->getMockBuilder(OrderAdapterInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mock->method('getOrder')
            ->willReturn($order);

        return $mock;
    }

    /**
     * Creates mock for gateway command object
     */
    private function initCommandMock()
    {
        $this->command = $this->getMockBuilder(GatewayCommand::class)
            ->disableOriginalConstructor()
            ->setMethods(['execute'])
            ->getMock();

        $this->command->method('execute')
            ->willReturn([]);
    }

    /**
     * Creates mock for filter object
     */
    private function initFilterBuilderMock()
    {
        $this->filterBuilder = $this->getMockBuilder(FilterBuilder::class)
            ->disableOriginalConstructor()
            ->setMethods(['setField', 'setValue', 'create', '__wakeup'])
            ->getMock();
    }

    /**
     * Builds search criteria
     */
    private function buildSearchCriteria()
    {
        $this->filterBuilder->expects(self::exactly(2))
            ->method('setField')
            ->willReturnSelf();
        $this->filterBuilder->expects(self::exactly(2))
            ->method('setValue')
            ->willReturnSelf();

        $searchCriteria = new SearchCriteria();
        $this->searchCriteriaBuilder->expects(self::exactly(2))
            ->method('addFilters')
            ->willReturnSelf();
        $this->searchCriteriaBuilder->method('create')
            ->willReturn($searchCriteria);

        $this->transactionRepository->method('getList')
            ->with($searchCriteria)
            ->willReturnSelf();
    }

    /**
     * Create mock for search criteria object
     */
    private function initSearchCriteriaBuilderMock()
    {
        $this->searchCriteriaBuilder = $this->getMockBuilder(SearchCriteriaBuilder::class)
            ->disableOriginalConstructor()
            ->setMethods(['addFilters', 'create', '__wakeup'])
            ->getMock();
    }

    /**
     * Create mock for transaction repository
     */
    private function initTransactionRepositoryMock()
    {
        $this->transactionRepository = $this->getMockBuilder(TransactionRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getList', 'getTotalCount', 'delete', 'get', 'save', 'create', '__wakeup'])
            ->getMock();
    }

    /**
     * Create mock for Order Adapter Factory
     */
    public function initOrderAdapterFactoryMock()
    {
        $this->orderAdapterFactory = $this->getMockBuilder(OrderAdapterFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $orderMock = $this->getMockBuilder(OrderAdapterInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAmazonOrderID'])
            ->getMock();

        $orderMock->method('getAmazonOrderID')
            ->willReturn('123456');

        $this->orderAdapterFactory->method('create')
            ->willReturn($orderMock);
    }
}
