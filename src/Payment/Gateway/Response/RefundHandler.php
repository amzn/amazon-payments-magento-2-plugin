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
use Amazon\Core\Helper\Data;
use Magento\Payment\Model\Method\Logger;
use Amazon\Payment\Gateway\Helper\SubjectReader;
use Magento\Framework\Message\ManagerInterface;
use Amazon\Payment\Api\Data\PendingRefundInterfaceFactory;

/**
 * Class RefundHandler
 * Handles refund behavior for Amazon Pay
 */
class RefundHandler implements HandlerInterface
{

    /**
     * @var ManagerInterface
     */
    private $messageManager;

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
     * @var PendingRefundInterfaceFactory 
     */
    private $pendingRefundFactory;

    /**
     * RefundHandler constructor.
     *
     * @param Logger                        $logger
     * @param SubjectReader                 $subjectReader
     * @param Data                          $coreHelper
     * @param ManagerInterface              $messageManager
     * @param PendingRefundInterfaceFactory $pendingRefundFactory
     */
    public function __construct(
        Logger $logger,
        SubjectReader $subjectReader,
        Data $coreHelper,
        ManagerInterface $messageManager,
        PendingRefundInterfaceFactory $pendingRefundFactory
    ) {
        $this->logger = $logger;
        $this->subjectReader = $subjectReader;
        $this->coreHelper = $coreHelper;
        $this->messageManager = $messageManager;
        $this->pendingRefundFactory = $pendingRefundFactory;
    }

    /**
     * @param array $handlingSubject
     * @param array $response
     */
    public function handle(array $handlingSubject, array $response)
    {

        if (isset($response['status']) && !$response['status']) {
            $this->messageManager->addErrorMessage(
                __('The refund amount or the Amazon Order ID is incorrect.')
            );
        } else {
            $paymentDO = $this->subjectReader->readPayment($handlingSubject);

            $payment = $paymentDO->getPayment();

            $payment->setTransactionId($response['refund_id']);

            if ($response['state'] == 'Pending') {
                $this->pendingRefundFactory->create()
                    ->setRefundId($response['refund_id'])
                    ->setPaymentId($payment->getEntityId())
                    ->setOrderId($payment->getOrder()->getId())
                    ->save();
            }

            $this->messageManager->addSuccessMessage(__('Amazon Pay refund successful.'));
        }
    }
}
