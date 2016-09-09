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
namespace Amazon\Payment\Api\PaymentManagement;

use Amazon\Payment\Domain\Details\AmazonCaptureDetails;

interface CaptureInterface
{
    /**
     * @param boolean $throwExceptions
     *
     * @return $this
     */
    public function setThrowExceptions($throwExceptions);

    /**
     * Update capture
     *
     * @param integer                   $pendingCaptureId
     * @param AmazonCaptureDetails|null $captureDetails
     *
     * @return void
     */
    public function updateCapture($pendingCaptureId, AmazonCaptureDetails $captureDetails = null);
}
