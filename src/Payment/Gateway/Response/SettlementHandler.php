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

namespace Amazon\Payment\Gateway\Response;

use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Payment\Model\Method\Logger;
use Amazon\Payment\Gateway\Helper\SubjectReader;
use Amazon\Core\Helper\Data;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Quote\Api\CartRepositoryInterface;

class SettlementHandler implements HandlerInterface
{

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @var Data
     */
    private $coreHelper;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * SettlementHandler constructor.
     *
     * @param Logger                   $logger
     * @param SubjectReader            $subjectReader
     * @param Data                     $coreHelper
     * @param OrderRepositoryInterface $orderRepository
     * @param CartRepositoryInterface  $quoteRepository
     */
    public function __construct(
        Logger $logger,
        SubjectReader $subjectReader,
        Data $coreHelper,
        OrderRepositoryInterface $orderRepository,
        CartRepositoryInterface $quoteRepository
    ) {
        $this->logger = $logger;
        $this->subjectReader = $subjectReader;
        $this->coreHelper = $coreHelper;
        $this->orderRepository = $orderRepository;
        $this->quoteRepository = $quoteRepository;
    }

    /**
     * @param array $handlingSubject
     * @param array $response
     */
    public function handle(array $handlingSubject, array $response)
    {
        $paymentDO = $this->subjectReader->readPayment($handlingSubject);

        $payment = $paymentDO->getPayment();

        // if reauthorized, treat as end of auth + capture process
        if ($response['reauthorized']) {

            if ($response['status']) {

                $orderDO = $paymentDO->getOrder();
                $order = $this->orderRepository->get($orderDO->getId());

                $payment->setTransactionId($response['capture_transaction_id']);
                $payment->setParentTransactionId($response['authorize_transaction_id']);
                $payment->setIsTransactionClosed(true);

                $quote = $this->quoteRepository->get($order->getQuoteId());
                $quoteLink = $this->subjectReader->getQuoteLink($quote->getId());
                $quoteLink->setConfirmed(true)->save();
            }
        }
        else {
            // finish capture
            $payment->setTransactionId($response['transaction_id']);
        }
    }

}
