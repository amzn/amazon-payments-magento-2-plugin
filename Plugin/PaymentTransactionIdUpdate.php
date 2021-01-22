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

use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Model\Order\Payment\Transaction;
use Magento\Sales\Model\Order\Payment\Transaction\ManagerInterface;

class PaymentTransactionIdUpdate
{
    /**
     * @param ManagerInterface $subject
     * @param OrderPaymentInterface $payment
     * @param $type
     * @param bool $transactionBasedOn
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeGenerateTransactionId(
        ManagerInterface $subject,
        OrderPaymentInterface $payment,
        $type,
        $transactionBasedOn = false
    ) {
        $paymentMethodTitle = $payment->getAdditionalInformation('method_title') ?? '';
        if (str_contains($paymentMethodTitle, 'Amazon Pay') && $type == Transaction::TYPE_VOID) {
            $chargePermissionId = $payment->getAdditionalInformation('charge_permission_id');
            if (empty($chargePermissionId)) {
                $transactionId = explode('-', $payment->getParentTransactionId());
                $chargePermissionId = implode('-', array_slice($transactionId, 0, 3));
            }
            $payment->setTransactionId($chargePermissionId . '-void');
        }

        return [
            $payment,
            $type,
            $transactionBasedOn
        ];
    }
}
