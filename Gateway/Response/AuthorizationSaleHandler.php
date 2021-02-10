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

namespace Amazon\Pay\Gateway\Response;

use Amazon\Pay\Gateway\Helper\SubjectReader;
use Amazon\Pay\Model\AsyncManagement;
use Amazon\Pay\Model\Config\Source\AuthorizationMode;
use Magento\Framework\App\Config\ScopeConfigInterface;
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
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * AuthorizationHandler constructor.
     * @param SubjectReader $subjectReader
     * @param AsyncManagement $asyncManagement
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        SubjectReader $subjectReader,
        AsyncManagement $asyncManagement,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->subjectReader = $subjectReader;
        $this->asyncManagement = $asyncManagement;
        $this->scopeConfig = $scopeConfig;
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

            $transactionId = $response['chargeId'] ?? $response['checkoutSessionId'];
            $payment->setTransactionId($transactionId);
            $payment->setIsTransactionClosed($handlingSubject['partial_capture'] ?? false);

            if ($this->scopeConfig->getValue('payment/amazon_payment/authorization_mode') ==
                AuthorizationMode::SYNC_THEN_ASYNC
                && !($handlingSubject['partial_capture'] ?? false)) {
                $payment->setIsTransactionPending(true);
            }

            // Subsequent charges on separate shipping will land here. Handle for CaptureInitiated in that case
            switch ($response['statusDetails']['state']) {
                case 'CaptureInitiated':
                    $payment->setIsTransactionPending(true);
                    $payment->setIsTransactionClosed(false);
                    $this->asyncManagement->queuePendingAuthorization($response['chargeId']);
                    break;
                default:
                    break;
            }
        }
    }
}
