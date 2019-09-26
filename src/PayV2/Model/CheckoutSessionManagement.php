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

namespace Amazon\PayV2\Model;

class CheckoutSessionManagement implements \Amazon\PayV2\Api\CheckoutSessionManagementInterface
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var AmazonConfig
     */
    private $amazonConfig;

    /**
     * @var Adapter\AmazonPayV2Adapter
     */
    private $amazonAdapter;

    /**
     * CheckoutSessionManagement constructor.
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param AmazonConfig $amazonConfig
     * @param Adapter\AmazonPayV2Adapter $amazonAdapter
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Amazon\PayV2\Model\AmazonConfig $amazonConfig,
        \Amazon\PayV2\Model\Adapter\AmazonPayV2Adapter $amazonAdapter
    ) {
        $this->storeManager = $storeManager;
        $this->amazonConfig = $amazonConfig;
        $this->amazonAdapter = $amazonAdapter;
    }

    /**
     * {@inheritdoc}
     */
    public function createCheckoutSession()
    {
        if (!$this->amazonConfig->isEnabled()) {
            return false;
        }
        $response = $this->amazonAdapter->createCheckoutSession($this->storeManager->getStore()->getId());
        return $response;
    }

    /**
     * {@inheritdoc}
     */
    public function completeCheckout($amazonCheckoutSessionId)
    {
        if (!$this->amazonConfig->isEnabled()) {
            return false;
        }
        $response = $this->amazonAdapter->getCheckoutSession(
            $this->storeManager->getStore()->getId(),
            $amazonCheckoutSessionId
        );
        return $response;
    }

    /**
     * Update Checkout Session to set payment info and transaction metadata
     *
     * @see PaymentInformationManagement plugins
     *
     * @param $quote
     * @param $amazonCheckoutSessionId
     */
    public function updateCheckoutSession($quote, $amazonCheckoutSessionId)
    {
        $response = $this->amazonAdapter->updateCheckoutSession($quote, $amazonCheckoutSessionId);

        // Return final redirect URL to process payment on Amazon before redirecting to Magento success page
        if (!empty($response['webCheckoutDetails']['amazonPayRedirectUrl'])) {
            return $response['webCheckoutDetails']['amazonPayRedirectUrl'];
        }
        return false;
    }
}
