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
namespace Amazon\Payment\Domain;

class AmazonAuthorizationStatus extends AbstractAmazonStatus
{
    const STATE_OPEN = 'Open';
    const STATE_PENDING = 'Pending';
    const STATE_DECLINED = 'Declined';
    const STATE_CLOSED = 'Closed';

    const REASON_INVALID_PAYMENT_METHOD = 'InvalidPaymentMethod';
    const REASON_PROCESSING_FAILURE = 'ProcessingFailure';
    const REASON_AMAZON_REJECTED = 'AmazonRejected';
    const REASON_TRANSACTION_TIMEOUT = 'TransactionTimedOut';
    const REASON_MAX_CAPTURES_PROCESSED = 'MaxCapturesProcessed';
    const REASON_SELLER_CLOSED = 'SellerClosed';
    const REASON_EXPIRED_UNUSED = 'ExpiredUnused';

    const CODE_HARD_DECLINE = 4273;
    const CODE_SOFT_DECLINE = 7638;
}
