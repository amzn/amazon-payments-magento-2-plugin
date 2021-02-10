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
namespace Amazon\Pay\Plugin;

use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;
use Magento\Sales\Model\Order\Payment\Transaction\Repository as TransactionRepository;

class LegacyPaymentHandler
{
    /**
     * @var TransactionRepository
     */
    private $transactionRepository;

    public function __construct(TransactionRepository $transactionRepository)
    {
        $this->transactionRepository = $transactionRepository;
    }

    /**
     * @param Order $subject
     * @param Payment $result
     * @return Payment
     */
    public function afterGetPayment(Order $subject, Payment $result)
    {
        if (!empty($result) && $result->getMethod() == 'amazon_payment' && $result->getAuthorizationTransaction()) {
            $result->setMethod('amazon_payment_v2');
            $result->setIsLegacyAmazonPayment(true);

            $authTransaction = $result->getAuthorizationTransaction();
            $oldTxnId = $authTransaction->getTxnId();
            $newTxnId = substr_replace($oldTxnId, 'C', 20, 1);

            // Shuffle transaction IDs, in API v2 the "-C" version is the charge ID and should be used from now on
            if ($authTransaction->getTxnId() != $newTxnId) {
                if ($legacyTransaction = $this->transactionRepository->getByTransactionId(
                    $newTxnId,
                    $authTransaction->getPaymentId(),
                    $authTransaction->getOrderId()
                )) {
                    // update legacy captures, where the first capture got assigned the charge ID
                    $legacyTransaction->setTxnId($newTxnId . '-legacy')->save();
                }
                $authTransaction->setTxnId($newTxnId)->save();
            }
        }

        return $result;
    }
}
