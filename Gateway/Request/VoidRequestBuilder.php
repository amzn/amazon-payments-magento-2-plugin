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

use Magento\Payment\Gateway\Request\BuilderInterface;
use Amazon\Pay\Gateway\Helper\SubjectReader;

class VoidRequestBuilder implements BuilderInterface
{
    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * VoidRequestBuilder constructor.
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
        $data = [];

        $paymentDO = $this->subjectReader->readPayment($buildSubject);
        $orderDO = $paymentDO->getOrder();

        $chargePermissionId = $paymentDO->getPayment()->getAdditionalInformation('charge_permission_id');

        // If we do not have a charge permission on the payment, try the first 3 sections of transaction ID
        if (empty($chargePermissionId)) {
            $transactionId = explode('-', $paymentDO->getPayment()->getAuthorizationTransaction()->getTxnId());
            $chargePermissionId = implode('-', array_slice($transactionId, 0, 3));
        }

        if ($orderDO) {
            $data = [
                'store_id' => $orderDO->getStoreId(),
                'charge_permission_id' => $chargePermissionId,
            ];
        }
        return $data;
    }
}
