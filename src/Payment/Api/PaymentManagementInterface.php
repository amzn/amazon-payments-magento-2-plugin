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
namespace Amazon\Payment\Api;

use Amazon\Payment\Domain\Details\AmazonAuthorizationDetails;
use Amazon\Payment\Domain\Details\AmazonCaptureDetails;
use Amazon\Payment\Domain\Details\AmazonRefundDetails;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Payment\Model\InfoInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\TransactionInterface;

interface PaymentManagementInterface
{
    /**
     * Queue pending capture
     *
     * @param AmazonCaptureDetails $details
     * @param integer              $paymentId
     * @param integer              $orderId
     *
     * @return void
     */
    public function queuePendingCapture(AmazonCaptureDetails $details, $paymentId, $orderId);

    /**
     * Queue pending authorization
     *
     * @param AmazonAuthorizationDetails $details
     * @param OrderInterface             $order
     *
     * @return void
     */
    public function queuePendingAuthorization(AmazonAuthorizationDetails $details, OrderInterface $order);

    /**
     * Queue pending refund
     *
     * @param AmazonRefundDetails $details
     * @param InfoInterface       $payment
     *
     * @return void
     */
    public function queuePendingRefund(AmazonRefundDetails $details, InfoInterface $payment);

    /**
     * Close transaction
     *
     * @param string         $transactionId
     * @param InfoInterface  $payment
     * @param OrderInterface $order
     *
     * @return void
     */
    public function closeTransaction($transactionId, InfoInterface $payment, OrderInterface $order);

    /**
     * Get transaction
     *
     * @param string         $transactionId
     * @param InfoInterface  $payment
     * @param OrderInterface $order
     *
     * @return TransactionInterface
     * @throws NoSuchEntityException
     */
    public function getTransaction($transactionId, InfoInterface $payment, OrderInterface $order);
}
