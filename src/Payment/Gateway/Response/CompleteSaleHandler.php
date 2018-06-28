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

class CompleteSaleHandler implements HandlerInterface
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
     * CompleteAuthHandler constructor.
     *
     * @param Logger        $logger
     * @param SubjectReader $subjectReader
     * @param Data          $coreHelper
     */
    public function __construct(
        Logger $logger,
        SubjectReader $subjectReader,
        Data $coreHelper
    ) {
        $this->logger = $logger;
        $this->subjectReader = $subjectReader;
        $this->coreHelper = $coreHelper;
    }

    /**
     * @param array $handlingSubject
     * @param array $response
     * @throws \Exception
     */
    public function handle(array $handlingSubject, array $response)
    {

        $paymentDO = $this->subjectReader->readPayment($handlingSubject);

        $amazonId = $this->subjectReader->getAmazonId();

        $payment = $paymentDO->getPayment();

        $order = $this->subjectReader->getOrder();

        if ($response['status']) {
            $payment->setTransactionId($response['capture_transaction_id']);
            $payment->setParentTransactionId($response['authorize_transaction_id']);
            $payment->setIsTransactionClosed(true);

            $quoteLink = $this->subjectReader->getQuoteLink();
            $quoteLink->setConfirmed(true)->save();

            $message = __('Captured amount of %1 online', $order->getGrandTotal());
            $message .= ' ' . __('Transaction ID: "%1"', $amazonId);

            $order->addStatusHistoryComment($message);

        }
    }

}
