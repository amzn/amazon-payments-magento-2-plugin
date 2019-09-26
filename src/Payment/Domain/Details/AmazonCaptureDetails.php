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
namespace Amazon\Payment\Domain\Details;

use Amazon\Payment\Domain\AmazonCaptureStatus;
use Amazon\Payment\Domain\AmazonCaptureStatusFactory;

class AmazonCaptureDetails
{
    /**
     * @var AmazonCaptureStatus
     */
    private $status;

    /**
     * @var string|null
     */
    private $transactionId;

    /**
     * AmazonCaptureDetails constructor.
     *
     * @param AmazonCaptureStatusFactory $amazonCaptureStatusFactory
     * @param array                      $details
     */
    public function __construct(AmazonCaptureStatusFactory $amazonCaptureStatusFactory, array $details)
    {
        $status       = $details['CaptureStatus'];
        $this->status = $amazonCaptureStatusFactory->create([
            'state'      => $status['State'],
            'reasonCode' => (isset($status['ReasonCode']) ? $status['ReasonCode'] : null)
        ]);

        if (isset($details['AmazonCaptureId'])) {
            $this->transactionId = $details['AmazonCaptureId'];
        }
    }

    /**
     * Get status
     *
     * @return AmazonCaptureStatus
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Get transaction id
     *
     * @return string|null
     */
    public function getTransactionId()
    {
        return $this->transactionId;
    }
}
