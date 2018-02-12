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

use Amazon\Payment\Domain\Details\AmazonAuthorizationDetails;
use Amazon\Payment\Domain\Details\AmazonOrderDetails;

/**
 * @api
 */
interface AuthorizationInterface
{
    /**
     * @param boolean $throwExceptions
     *
     * @return $this
     */
    public function setThrowExceptions($throwExceptions);

    /**
     * Update authorization
     *
     * @param integer                         $pendingAuthorizationId
     * @param AmazonAuthorizationDetails|null $authorizationDetails
     * @param AmazonOrderDetails|null         $orderDetails
     *
     * @return void
     */
    public function updateAuthorization(
        $pendingAuthorizationId,
        AmazonAuthorizationDetails $authorizationDetails = null,
        AmazonOrderDetails $orderDetails = null
    );
}
