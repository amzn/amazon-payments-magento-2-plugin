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

use Magento\Payment\Gateway\Response\HandlerInterface;
use Amazon\PayV2\Gateway\Helper\SubjectReader;
use Amazon\PayV2\Model\AsyncManagement;

class SettlementHandler implements HandlerInterface
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
     * SettlementHandler constructor.
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
     * @param array $handlingSubject
     * @param array $response
     */
    public function handle(array $handlingSubject, array $response)
    {
        $paymentDO = $this->subjectReader->readPayment($handlingSubject);
        $payment = $paymentDO->getPayment();

        if (isset($response['chargeId'])) {
            $payment->setTransactionId($response['chargeId'].'-capture');
            $payment->setParentTransactionId($response['chargeId']);

            switch ($response['statusDetails']['state']) {
                case 'CaptureInitiated':
                    $payment->setIsTransactionPending(true);
                    $payment->setIsTransactionClosed(false);
                    $this->asyncManagement->queuePendingAuthorization($response['chargeId']);
                    break;
                default:
                    $payment->setIsTransactionClosed(true);
                    break;
            }
        }
    }
}
