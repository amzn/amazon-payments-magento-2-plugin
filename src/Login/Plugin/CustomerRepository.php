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

use Amazon\Login\Api\Data\CustomerLinkInterfaceFactory;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerExtensionFactory;
use Magento\Customer\Api\Data\CustomerInterface;

class CustomerRepository
{
    /**
     * @var CustomerExtensionFactory
     */
    protected $customerExtensionFactory;

    /**
     * @var CustomerLinkInterfaceFactory
     */
    protected $customerLinkFactory;

    /**
     * CustomerRepository constructor.
     *
     * @param CustomerExtensionFactory     $customerExtensionFactory
     * @param CustomerLinkInterfaceFactory $customerLinkFactory
     */
    public function __construct(
        CustomerExtensionFactory $customerExtensionFactory,
        CustomerLinkInterfaceFactory $customerLinkFactory
    ) {
        $this->customerExtensionFactory = $customerExtensionFactory;
        $this->customerLinkFactory      = $customerLinkFactory;
    }

    /**
     * Add amazon id extension attribute to customer
     *
     * @param CustomerRepositoryInterface $customerRepository
     * @param CustomerInterface           $customer
     *
     * @return CustomerInterface
     */
    public function afterGetById(CustomerRepositoryInterface $customerRepository, CustomerInterface $customer)
    {
        $this->setAmazonIdExtensionAttribute($customer);

        return $customer;
    }

    /**
     * Add amazon id extension attribute to customer
     *
     * @param CustomerRepositoryInterface $customerRepository
     * @param CustomerInterface           $customer
     *
     * @return CustomerInterface
     */
    public function afterGet(CustomerRepositoryInterface $customerRepository, CustomerInterface $customer)
    {
        $this->setAmazonIdExtensionAttribute($customer);

        return $customer;
    }

    protected function setAmazonIdExtensionAttribute(CustomerInterface $customer)
    {
        $customerExtension = ($customer->getExtensionAttributes()) ?: $this->customerExtensionFactory->create();

        $amazonCustomer = $this->customerLinkFactory->create();
        $amazonCustomer->load($customer->getId(), 'customer_id');

        if ($amazonCustomer->getId()) {
            $customerExtension->setAmazonId($amazonCustomer->getAmazonId());
        }

        $customer->setExtensionAttributes($customerExtension);
    }
}
