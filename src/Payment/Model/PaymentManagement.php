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
namespace Amazon\Payment\Model;

use Amazon\Payment\Api\Data\PendingAuthorizationInterfaceFactory;
use Amazon\Payment\Api\Data\PendingCaptureInterfaceFactory;
use Amazon\Payment\Api\Data\PendingRefundInterfaceFactory;
use Amazon\Payment\Domain\Details\AmazonAuthorizationDetails;
use Amazon\Payment\Domain\Details\AmazonCaptureDetails;
use Amazon\Payment\Domain\Details\AmazonRefundDetails;
use Magento\Framework\Api\SearchCriteriaBuilderFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Payment\Model\InfoInterface as PaymentInfoInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\TransactionInterface;
use Magento\Sales\Api\OrderPaymentRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\TransactionRepositoryInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 *
 * @deprecated As of February 2021, this Legacy Amazon Pay plugin has been
 * deprecated, in favor of a newer Amazon Pay version available through GitHub
 * and Magento Marketplace. Please download the new plugin for automatic
 * updates and to continue providing your customers with a seamless checkout
 * experience. Please see https://pay.amazon.com/help/E32AAQBC2FY42HS for details
 * and installation instructions.
 */
class PaymentManagement
{
    /**
     * @var PendingCaptureInterfaceFactory
     */
    private $pendingCaptureFactory;

    /**
     * @var PendingAuthorizationInterfaceFactory
     */
    private $pendingAuthorizationFactory;

    /**
     * @var PendingRefundInterfaceFactory
     */
    private $pendingRefundFactory;

    /**
     * @var SearchCriteriaBuilderFactory
     */
    private $searchCriteriaBuilderFactory;

    /**
     * @var OrderPaymentRepositoryInterface
     */
    private $orderPaymentRepository;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var TransactionRepositoryInterface
     */
    private $transactionRepository;

    /**
     * PaymentManagement constructor.
     *
     * @param PendingCaptureInterfaceFactory       $pendingCaptureFactory
     * @param PendingAuthorizationInterfaceFactory $pendingAuthorizationFactory
     * @param PendingRefundInterfaceFactory        $pendingRefundFactory
     * @param SearchCriteriaBuilderFactory         $searchCriteriaBuilderFactory
     * @param OrderPaymentRepositoryInterface      $orderPaymentRepository
     * @param OrderRepositoryInterface             $orderRepository
     * @param TransactionRepositoryInterface       $transactionRepository
     */
    public function __construct(
        PendingCaptureInterfaceFactory $pendingCaptureFactory,
        PendingAuthorizationInterfaceFactory $pendingAuthorizationFactory,
        PendingRefundInterfaceFactory $pendingRefundFactory,
        SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory,
        OrderPaymentRepositoryInterface $orderPaymentRepository,
        OrderRepositoryInterface $orderRepository,
        TransactionRepositoryInterface $transactionRepository
    ) {
        $this->pendingCaptureFactory        = $pendingCaptureFactory;
        $this->pendingAuthorizationFactory  = $pendingAuthorizationFactory;
        $this->pendingRefundFactory         = $pendingRefundFactory;
        $this->searchCriteriaBuilderFactory = $searchCriteriaBuilderFactory;
        $this->orderPaymentRepository       = $orderPaymentRepository;
        $this->orderRepository              = $orderRepository;
        $this->transactionRepository        = $transactionRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function queuePendingCapture(AmazonCaptureDetails $details, $paymentId, $orderId)
    {
        $this->pendingCaptureFactory->create()
            ->setCaptureId($details->getTransactionId())
            ->setPaymentId($paymentId)
            ->setOrderId($orderId)
            ->save();
    }

    /**
     * {@inheritdoc}
     */
    public function queuePendingAuthorization(AmazonAuthorizationDetails $details, OrderInterface $order)
    {
        $pendingAuthorization = $this->pendingAuthorizationFactory->create()
            ->setAuthorizationId($details->getAuthorizeTransactionId());

        if ($details->hasCapture()) {
            $pendingAuthorization->setCaptureId($details->getCaptureTransactionId())
                ->setCapture(true);
        }

        $order->addRelatedObject($pendingAuthorization);
    }

    /**
     * {@inheritdoc}
     */
    public function queuePendingRefund(AmazonRefundDetails $details, PaymentInfoInterface $payment)
    {
        $this->pendingRefundFactory->create()
            ->setRefundId($details->getRefundId())
            ->setPaymentId($payment->getId())
            ->setOrderId($payment->getOrder()->getId())
            ->save();
    }

    /**
     * {@inheritdoc}
     */
    public function closeTransaction($transactionId, PaymentInfoInterface $payment, OrderInterface $order)
    {
        $this->getTransaction($transactionId, $payment, $order)->setIsClosed(1)->save();
    }

    /**
     * {@inheritdoc}
     */
    public function getTransaction($transactionId, PaymentInfoInterface $payment, OrderInterface $order)
    {
        $searchCriteriaBuilder = $this->searchCriteriaBuilderFactory->create();

        $searchCriteriaBuilder->addFilter(
            TransactionInterface::TXN_ID,
            $transactionId
        );

        $searchCriteriaBuilder->addFilter(
            TransactionInterface::ORDER_ID,
            $order->getId()
        );

        $searchCriteriaBuilder->addFilter(
            TransactionInterface::PAYMENT_ID,
            $payment->getId()
        );

        $searchCriteria = $searchCriteriaBuilder
            ->setPageSize(1)
            ->setCurrentPage(1)
            ->create();

        $transactionList = $this->transactionRepository->getList($searchCriteria);

        if (count($items = $transactionList->getItems())) {
            $transaction = current($items);
            $transaction
                ->setPayment($payment)
                ->setOrder($order);

            return $transaction;
        }

        throw new NoSuchEntityException();
    }
}
