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

namespace Amazon\PayV2\Gateway\Response;

use Amazon\PayV2\Gateway\Helper\SubjectReader;
use Amazon\PayV2\Model\AsyncManagement;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Sales\Model\Order\Payment;

class AuthorizationSaleHandler implements HandlerInterface
{
    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @var AsyncManagement
     */
    private $asyncManagement;

    /**
     * AuthorizationHandler constructor.
     * @param SubjectReader $subjectReader
     * @param AsyncManagement $asyncManagement
     */
    public function __construct(
        SubjectReader $subjectReader,
        AsyncManagement $asyncManagement
    ) {
        $this->subjectReader = $subjectReader;
        $this->asyncManagement = $asyncManagement;
    }

    /**
     * Handles response
     *
     * @param array $handlingSubject
     * @param array $response
     * @return void
     */
    public function handle(array $handlingSubject, array $response)
    {
        $paymentDO = $this->subjectReader->readPayment($handlingSubject);

        if ($paymentDO->getPayment() instanceof Payment) {
            /** @var Payment $payment */
            $payment = $paymentDO->getPayment();

            $payment->setTransactionId($response['chargeId']);

            $chargeState = $response['charge']['statusDetail']['state'];

            switch ($chargeState) {
                case 'Authorized':
                    $payment->setIsTransactionClosed(false);
                    break;
                case 'AuthorizationInitiated':
                    $payment->setIsTransactionClosed(false);
                    $this->setPending($payment);
                    $this->asyncManagement->queuePendingAuthorization($response['chargeId']);
                    break;
                case 'Captured':
                    $payment->setIsTransactionClosed(true);
                    break;
                case 'CaptureInitiated':
                    $payment->setIsTransactionClosed(false);
                    break;
            }
        }
    }

    /**
     * Set order as pending review
     *
     * @param Payment $payment
     */
    private function setPending($payment)
    {
        $order = $payment->getOrder();
        $payment->setIsTransactionPending(true);
        $order->setState(\Magento\Sales\Model\Order::STATE_PAYMENT_REVIEW)->setStatus(
            \Magento\Sales\Model\Order::STATE_PAYMENT_REVIEW
        );
    }
}
