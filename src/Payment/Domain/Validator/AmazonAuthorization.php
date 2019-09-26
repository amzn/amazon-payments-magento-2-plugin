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

namespace Amazon\Payment\Domain\Validator;

use Amazon\Payment\Domain\AmazonAuthorizationStatus;
use Amazon\Payment\Domain\Details\AmazonAuthorizationDetails;

/**
 * Class AmazonAuthorization
 * validates Amazon Pay status during cron updates.
 */
class AmazonAuthorization
{
    /**
     * @param AmazonAuthorizationDetails $details
     * @return array
     */
    public function validate(AmazonAuthorizationDetails $details)
    {
        $status = $details->getStatus();

        switch ($status->getState()) {
            case AmazonAuthorizationStatus::STATE_CLOSED:
                switch ($status->getReasonCode()) {
                    case AmazonAuthorizationStatus::REASON_MAX_CAPTURES_PROCESSED:
                        return [
                            'result' => true,
                            'reason' => AmazonAuthorizationStatus::REASON_MAX_CAPTURES_PROCESSED
                        ];
                }
                break;
            case AmazonAuthorizationStatus::STATE_OPEN:
            case AmazonAuthorizationStatus::STATE_PENDING:
                return ['result' => true, 'reason' => $status->getState()];
            case AmazonAuthorizationStatus::STATE_DECLINED:
                return ['result' => false, 'reason' => $this->getReasonCode($status)];
        }

        return ['result' => false, 'reason' => $status->getState()];
    }

    /**
     * Need to ensure three specific reason codes come through during processing.
     *
     * @param AmazonAuthorizationStatus $status
     * @return null|string
     */
    protected function getReasonCode(AmazonAuthorizationStatus $status)
    {
        switch ($status->getReasonCode()) {
            case AmazonAuthorizationStatus::REASON_TRANSACTION_TIMEOUT:
                return 'timeout';
            case AmazonAuthorizationStatus::REASON_AMAZON_REJECTED:
            case AmazonAuthorizationStatus::REASON_PROCESSING_FAILURE:
                return 'hard_decline';
            case AmazonAuthorizationStatus::REASON_INVALID_PAYMENT_METHOD:
                return 'soft_decline';
        }
        return $status->getReasonCode();
    }
}
