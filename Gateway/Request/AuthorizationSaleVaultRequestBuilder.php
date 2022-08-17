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

namespace Amazon\Pay\Gateway\Request;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Amazon\Pay\Gateway\Helper\SubjectReader;

class AuthorizationSaleVaultRequestBuilder implements BuilderInterface
{
    /**
     * @var \Amazon\Pay\Api\CheckoutSessionManagementInterface
     */
    private $sessionManagement;

    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @var \Magento\Vault\Api\PaymentTokenManagementInterface
     */
    private $paymentTokenManagement;

    /**
     * AuthorizationRequestBuilder constructor.
     * @param \Amazon\Pay\Api\CheckoutSessionManagementInterface $sessionManagement
     * @param SubjectReader $subjectReader
     */
    public function __construct(
        \Amazon\Pay\Api\CheckoutSessionManagementInterface $sessionManagement,
        SubjectReader $subjectReader,
        \Magento\Vault\Api\PaymentTokenManagementInterface $paymentTokenManagement
    ) {
        $this->sessionManagement = $sessionManagement;
        $this->subjectReader = $subjectReader;
        $this->paymentTokenManagement = $paymentTokenManagement;
    }

    /**
     * @inheritdoc
     */
    public function build(array $buildSubject)
    {
        $payment = $this->subjectReader->readPayment($buildSubject)->getPayment();

        $publicHash = $payment->getAdditionalInformation('public_hash');
        $customerId = $payment->getAdditionalInformation('customer_id');
        $token = $this->paymentTokenManagement->getByPublicHash($publicHash, $customerId);
        
        if (!$token || !$token->getIsActive()) return [];

        if ($payment->getAmazonDisplayInvoiceAmount()) {
            $total = $payment->getAmazonDisplayInvoiceAmount();
        } else {
            $total = $payment->getAmountOrdered();
        }

        /* @var $payment \Magento\Sales\Model\Order\Payment */
        return [
            'quote_id' => $payment->getOrder()->getQuoteId(),
            'increment_id' => $payment->getOrder()->getIncrementId(),
            'charge_permission_id' => $token->getGatewayToken(),
            'amount' => $total,
        ];
    }
}
