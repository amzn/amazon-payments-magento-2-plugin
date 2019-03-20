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
namespace Amazon\Login\Plugin;

use Amazon\Core\Model\AmazonConfig;
use Amazon\Login\Api\CustomerManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;

class CustomerRepository
{
    /**
     * @var CustomerManagementInterface
     */
    private $customerManagement;

    /**
     * @var AmazonConfig
     */
    private $amazonConfig;

    /**
     * CustomerRepository constructor.
     *
     * @param CustomerManagementInterface  $customerManagement
     * @param AmazonConfig $amazonConfig
     */
    public function __construct(
        CustomerManagementInterface $customerManagement,
        AmazonConfig $amazonConfig
    ) {
        $this->customerManagement       = $customerManagement;
        $this->amazonConfig             = $amazonConfig;
    }

    /**
     * Add amazon id extension attribute to customer
     *
     * @param CustomerRepositoryInterface $customerRepository
     * @param CustomerInterface           $customer
     *
     * @return CustomerInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetById(CustomerRepositoryInterface $customerRepository, CustomerInterface $customer)
    {
        if ($this->amazonConfig->isEnabled()) {
            $this->customerManagement->setAmazonIdExtensionAttribute($customer);
        }

        return $customer;
    }

    /**
     * Add amazon id extension attribute to customer
     *
     * @param CustomerRepositoryInterface $customerRepository
     * @param CustomerInterface           $customer
     *
     * @return CustomerInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGet(CustomerRepositoryInterface $customerRepository, CustomerInterface $customer)
    {
        if ($this->amazonConfig->isEnabled()) {
            $this->customerManagement->setAmazonIdExtensionAttribute($customer);
        }

        return $customer;
    }
}
