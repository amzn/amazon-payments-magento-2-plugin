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
namespace Amazon\PayV2\CustomerData;

/**
 * Amazon Checkout Session section
 */
class CheckoutSession
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $session;

    /**
     * @var \Amazon\PayV2\Model\CheckoutSessionManagement
     */
    private $checkoutSessionManagement;

    /**
     * CheckoutSession constructor.
     * @param \Magento\Checkout\Model\Session $session
     * @param \Amazon\PayV2\Model\CheckoutSessionManagement $checkoutSessionManagement
     */
    public function __construct(
        \Magento\Checkout\Model\Session $session,
        \Amazon\PayV2\Model\CheckoutSessionManagement $checkoutSessionManagement
    ) {
        $this->session = $session;
        $this->checkoutSessionManagement = $checkoutSessionManagement;
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        return $this->checkoutSessionManagement->getConfig($this->session->getQuote());
    }

    /**
     * Clear Amazon Checkout Session Id
     */
    public function clearCheckoutSessionId()
    {
        $this->checkoutSessionManagement->cancelCheckoutSession($this->session->getQuote());
    }

    /**
     * Get Amazon Checkout Session Id
     */
    public function getCheckoutSessionId()
    {
        return $this->checkoutSessionManagement->getCheckoutSession($this->session->getQuote());
    }

    /**
     * Complete Amazon Checkout Session
     */
    public function completeCheckoutSession()
    {
        return $this->checkoutSessionManagement->completeCheckoutSession($this->session->getQuote());
    }
}
