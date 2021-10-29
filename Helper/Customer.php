<?php

/**
 * Copyright 2020 Amazon.com, Inc. or its affiliates. All Rights Reserved.
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

namespace Amazon\Pay\Helper;

use Amazon\Pay\Api\Data\AmazonCustomerInterface;
use Zend_Validate;

class Customer
{
    /**
     * @var Adapter\AmazonPayAdapter
     */
    private $amazonAdapter;

    /**
     * @var AmazonCustomerFactory
     */
    private $amazonCustomerFactory;

    /**
     * @var CustomerLinkManagementInterface
     */
    private $customerLinkManagement;

    /**
     * @var AmazonConfig
     */
    private $amazonConfig;

    /**
     * @param Adapter\AmazonPayAdapter $amazonAdapter
     * @param Domain\AmazonCustomerFactory $amazonCustomerFactory
     * @param CustomerLinkManagementInterface $customerLinkManagement
     * @param AmazonConfig $amazonConfig
     */
    public function __construct(
        \Amazon\Pay\Model\Adapter\AmazonPayAdapter $amazonAdapter,
        \Amazon\Pay\Domain\AmazonCustomerFactory $amazonCustomerFactory,
        \Amazon\Pay\Api\CustomerLinkManagementInterface $customerLinkManagement,
        \Amazon\Pay\Model\AmazonConfig $amazonConfig
    ) {
        $this->amazonAdapter = $amazonAdapter;
        $this->amazonCustomerFactory = $amazonCustomerFactory;
        $this->customerLinkManagement = $customerLinkManagement;
        $this->amazonConfig = $amazonConfig;
    }

    public function getAmazonCustomer($buyerInfo)
    {
        if (is_array($buyerInfo) && array_key_exists('buyerId', $buyerInfo) && !empty($buyerInfo['buyerId'])) {
            $data = [
                'id'      => $buyerInfo['buyerId'],
                'email'   => $buyerInfo['email'],
                'name'    => $buyerInfo['name'],
                'country' => $this->amazonConfig->getRegion(),
            ];
            $amazonCustomer = $this->amazonCustomerFactory->create($data);

            return $amazonCustomer;

        }
        return false;
    }

    public function createCustomer(AmazonCustomerInterface $amazonCustomer)
    {
        if (! Zend_Validate::is($amazonCustomer->getEmail(), 'EmailAddress')) {
            throw new ValidatorException(__('the email address for your Amazon account is invalid'));
        }

        $customerData = $this->customerLinkManagement->create($amazonCustomer);
        $this->updateCustomerLink($customerData->getId(), $amazonCustomer->getId());

        return $customerData;
    }

    public function updateCustomerLink($customerDataId, $amazonCustomerId)
    {
        return $this->customerLinkManagement->updateLink($customerDataId, $amazonCustomerId);
    }
}
