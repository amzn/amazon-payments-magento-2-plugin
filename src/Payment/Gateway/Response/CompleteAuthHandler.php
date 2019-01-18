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
use Amazon\Payment\Api\Data\PendingAuthorizationInterfaceFactory;

class CompleteAuthHandler implements HandlerInterface
{

    /**
     * @var Data
     */
    private $coreHelper;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @var PendingAuthorizationInterfaceFactory
     */
    private $pendingAuthorizationFactory;

    /**
     * CompleteAuthHandler constructor.
     *
     * @param Logger $logger
     * @param SubjectReader $subjectReader
     * @param PendingAuthorizationInterfaceFactory $pendingAuthorizationFactory
     * @param Data $coreHelper
     */
    public function __construct(
        Logger $logger,
        SubjectReader $subjectReader,
        PendingAuthorizationInterfaceFactory $pendingAuthorizationFactory,
        Data $coreHelper
    ) {
        $this->logger = $logger;
        $this->subjectReader = $subjectReader;
        $this->coreHelper = $coreHelper;
        $this->pendingAuthorizationFactory = $pendingAuthorizationFactory;
    }

    /**
     * @param array $handlingSubject
     * @param array $response
     * @throws \Exception
     */
    public function handle(array $handlingSubject, array $response)
    {

        $paymentDO = $this->subjectReader->readPayment($handlingSubject);
        $payment = $paymentDO->getPayment();
        $order = $this->subjectReader->getOrder();

        if ($response['status']) {
            $payment->setTransactionId($response['authorize_transaction_id']);


            if ($response['timeout']) {
                $payment->setIsTransactionPending(true);
                $order->setState(\Magento\Sales\Model\Order::STATE_PAYMENT_REVIEW)->setStatus(\Magento\Sales\Model\Order::STATE_PAYMENT_REVIEW);
                $this->pendingAuthorizationFactory->create()
                    ->setAuthorizationId($response['authorize_transaction_id'])
                    ->save();
            }
            $payment->setIsTransactionClosed(false);
            $quoteLink = $this->subjectReader->getQuoteLink();
            $quoteLink->setConfirmed(true)->save();
        }
    }
}
