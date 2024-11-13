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

use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Framework\Message\ManagerInterface;
use Amazon\Pay\Gateway\Helper\SubjectReader;
use Amazon\Pay\Model\AsyncManagement;

class RefundHandler implements HandlerInterface
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
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * SettlementHandler constructor.
     *
     * @param SubjectReader $subjectReader
     * @param AsyncManagement $asyncManagement
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        SubjectReader $subjectReader,
        AsyncManagement $asyncManagement,
        ManagerInterface $messageManager
    ) {
        $this->subjectReader = $subjectReader;
        $this->asyncManagement = $asyncManagement;
        $this->messageManager = $messageManager;
    }

    /**
     * Handle payment refund
     *
     * @param array $handlingSubject
     * @param array $response
     */
    public function handle(array $handlingSubject, array $response)
    {
        $paymentDO = $this->subjectReader->readPayment($handlingSubject);
        $payment = $paymentDO->getPayment();

        if (isset($response['refundId'])) {
            $payment->setTransactionId($response['refundId']);

            // Verify refund via async
            $this->asyncManagement->queuePendingRefund($payment->getOrder()->getId(), $response['refundId']);

            $this->messageManager->addSuccessMessage(__('The refund through Amazon Pay was successful.'));
        }
    }
}
