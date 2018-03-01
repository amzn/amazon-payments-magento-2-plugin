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

class CheckoutConfigProvider implements ConfigProviderInterface
{
    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * @param CustomerSession $customerSession
     */
    public function __construct(CustomerSession $customerSession)
    {
        $this->customerSession = $customerSession;
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

        // return a stdClass so that the resulting JSON is an empty object, not an empty array
        return ['amazonLogin' => empty($config) ? new \stdClass : $config];
    }
}
