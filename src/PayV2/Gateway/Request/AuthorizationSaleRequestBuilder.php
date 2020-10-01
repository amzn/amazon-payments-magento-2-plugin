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

namespace Amazon\PayV2\Gateway\Request;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Amazon\PayV2\Gateway\Helper\SubjectReader;

class AuthorizationSaleRequestBuilder implements BuilderInterface
{
    /**
     * @var \Amazon\PayV2\Api\CheckoutSessionManagementInterface
     */
    private $sessionManagement;

    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * AuthorizationRequestBuilder constructor.
     * @param \Amazon\PayV2\Api\CheckoutSessionManagementInterface $sessionManagement
     * @param SubjectReader $subjectReader
     */
    public function __construct(
        \Amazon\PayV2\Api\CheckoutSessionManagementInterface $sessionManagement,
        SubjectReader $subjectReader
    ) {
        $this->sessionManagement = $sessionManagement;
        $this->subjectReader = $subjectReader;
    }

    /**
     * @inheritdoc
     */
    public function build(array $buildSubject)
    {
        $payment = $this->subjectReader->readPayment($buildSubject)->getPayment();
        try {
            $amazonCheckoutSessionId = $this->sessionManagement->getCheckoutSession($payment->getOrder()->getQuoteId());
        } catch (NoSuchEntityException $e) {
            $amazonCheckoutSessionId = null;
        }

        if ($payment->getAmazonDisplayInvoiceAmount()) {
            $total = $payment->getAmazonDisplayInvoiceAmount();
        }
        else {
            $total = $payment->getAmountOrdered();
        }

        /* @var $payment \Magento\Sales\Model\Order\Payment */
        return [
            'quote_id' => $payment->getOrder()->getQuoteId(),
            'amazon_checkout_session_id' => $amazonCheckoutSessionId,
            'charge_permission_id' => $payment->getAdditionalInformation('charge_permission_id'),
            'amount' => $total,
        ];
    }
}
