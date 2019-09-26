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

use Magento\Payment\Gateway\Request\BuilderInterface;
use Amazon\PayV2\Gateway\Helper\SubjectReader;

class SettlementRequestBuilder implements BuilderInterface
{
    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * AuthorizationRequestBuilder constructor.
     * @param SubjectReader $subjectReader
     */
    public function __construct(
        SubjectReader $subjectReader
    ) {
        $this->subjectReader = $subjectReader;
    }

    /**
     * @inheritdoc
     */
    public function build(array $buildSubject)
    {
        // Used for Settlement and Refund

        $paymentDO = $this->subjectReader->readPayment($buildSubject);
        $orderDO = $paymentDO->getOrder();
        $storeId = $orderDO->getStoreId();

        $currencyCode = $orderDO->getCurrencyCode();
        $total = $buildSubject['amount'];

        $data = [
            'store_id' => $storeId,
            'charge_id' => rtrim($paymentDO->getPayment()->getParentTransactionId(), '-capture'),
            'amount' => $total,
            'currency_code' => $currencyCode,
        ];

        return $data;
    }
}
