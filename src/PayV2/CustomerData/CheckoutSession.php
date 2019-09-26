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

use Magento\Customer\CustomerData\SectionSourceInterface;

/**
 * Amazon Checkout Session section
 */
class CheckoutSession implements SectionSourceInterface
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
     * @var \Amazon\PayV2\Model\AmazonConfig
     */
    private $amazonConfig;

    /**
     * CheckoutSession constructor.
     * @param \Magento\Checkout\Model\Session $session
     * @param \Amazon\PayV2\Model\CheckoutSessionManagement $checkoutSessionManagement
     * @param \Amazon\PayV2\Model\AmazonConfig $amazonConfig
     */
    public function __construct(
        \Magento\Checkout\Model\Session $session,
        \Amazon\PayV2\Model\CheckoutSessionManagement $checkoutSessionManagement,
        \Amazon\PayV2\Model\AmazonConfig $amazonConfig
    ) {
        $this->session = $session;
        $this->checkoutSessionManagement = $checkoutSessionManagement;
        $this->amazonConfig = $amazonConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function getSectionData()
    {
        $data = [];
        if ($this->amazonConfig->isEnabled()) {
            $data = ['checkoutSessionId' => $this->getCheckoutSessionId()];
        }
        return $data;
    }

    /**
     * Clear Amazon Checkout Session Id
     */
    public function clearCheckoutSessionId()
    {
        $this->session->unsAmazonCheckoutSessionId();
    }

    /**
     * Get Amazon Checkout Session Id
     */
    public function getCheckoutSessionId($reset = false)
    {
        if (!$this->amazonConfig->isEnabled()) {
            return false;
        }

        $sessionId = $this->session->getAmazonCheckoutSessionId();
        if (!$sessionId || $reset) {
            $sessionId = $this->createCheckoutSessionId();
        }
        return $sessionId;
    }

    /**
     * Create and save Amazon Checkout Session Id
     */
    protected function createCheckoutSessionId()
    {
        $response = $this->checkoutSessionManagement->createCheckoutSession();
        if ($response) {
            $sessionId = $response['checkoutSessionId'];
            $this->session->setAmazonCheckoutSessionId($sessionId);
            return $sessionId;
        }
    }
}
