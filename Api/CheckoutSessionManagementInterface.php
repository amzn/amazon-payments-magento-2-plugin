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
namespace Amazon\Pay\Api;

/**
 * @api
 */
interface CheckoutSessionManagementInterface
{
    /**
     * @param string|null $cartId
     * @return mixed
     */
    public function getConfig($cartId = null);

    /**
     * @param mixed $amazonSessionId
     * @return mixed
     */
    public function getShippingAddress($amazonSessionId);

    /**
     * @param mixed $amazonSessionId
     * @return mixed
     */
    public function getBillingAddress($amazonSessionId);

    /**
     * @param mixed $amazonSessionId
     * @return string
     */
    public function getPaymentDescriptor($amazonSessionId);

    /**
     * @param mixed $amazonSessionId
     * @param mixed|null $cartId
     * @return string
     */
    public function updateCheckoutSession($amazonSessionId, $cartId = null);

    /**
     * @param mixed $amazonSessionId
     * @param mixed|null $cartId
     * @return int
     */
    public function completeCheckoutSession($amazonSessionId, $cartId = null);
}
