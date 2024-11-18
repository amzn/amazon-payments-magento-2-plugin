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
     * Retrieve config values for button rendering
     *
     * @param string|null $cartId
     * @return mixed
     */
    public function getConfig($cartId = null);

    /**
     * Get shipping address associated with Amazon checkout session
     *
     * @param mixed $amazonSessionId
     * @return mixed
     */
    public function getShippingAddress($amazonSessionId);

    /**
     * Get billing address associated with Amazon checkout session
     *
     * @param mixed $amazonSessionId
     * @return mixed
     */
    public function getBillingAddress($amazonSessionId);

    /**
     * Get short description of selected payment method for Amazon checkout session
     *
     * @param mixed $amazonSessionId
     * @return string
     */
    public function getPaymentDescriptor($amazonSessionId);

    /**
     * Set payment/charge information on Amazon checkout session so it can be completed.
     *
     * On success, returns an Amazon-hosted URL to redirect the buyer to for payment processing.
     *
     * @param mixed $amazonSessionId
     * @param mixed|null $cartId
     * @return string
     */
    public function updateCheckoutSession($amazonSessionId, $cartId = null);

    /**
     * Complete the Amazon checkout session and place the order in Magento
     *
     * @param mixed $amazonSessionId
     * @param mixed|null $cartId
     * @return mixed
     */
    public function completeCheckoutSession($amazonSessionId, $cartId = null);

    /**
     * Login to the Magento store using Amazon account information
     *
     * Creates a Magento store account if one does not exist.
     *
     * @param mixed $buyerToken
     * @return mixed
     */
    public function signIn($buyerToken);

    /**
     * Links entries in the amazon_customer table to Magento customer records
     *
     * @param mixed $buyerToken
     * @param string $password
     * @return mixed
     */
    public function setCustomerLink($buyerToken, $password);
}
