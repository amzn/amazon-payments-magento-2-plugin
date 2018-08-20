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
namespace Amazon\Login\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Checkout\Model\Session as CheckoutSession;

class CheckoutConfigProvider implements ConfigProviderInterface
{
    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * CheckoutConfigProvider constructor.
     * @param CustomerSession $customerSession
     * @param CheckoutSession $checkoutSession
     */
    public function __construct(
        CustomerSession $customerSession,
        CheckoutSession $checkoutSession
    ) {
        $this->customerSession = $customerSession;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        $config = [];

        /** @var \Amazon\Core\Api\Data\AmazonCustomerInterface $amazonCustomer */
        if ($amazonCustomer = $this->customerSession->getAmazonCustomer()) {
            $config['amazon_customer_email'] = $amazonCustomer->getEmail();
        }

        if (!isset($config['amazon_customer_email'])) {
            $quote = $this->checkoutSession->getQuote();
            $config['amazon_customer_email'] = $quote->getCustomerEmail();
        }

        // return a stdClass so that the resulting JSON is an empty object, not an empty array
        return ['amazonLogin' => empty($config) ? new \stdClass : $config];
    }
}
