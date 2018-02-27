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

use Amazon\Login\Api\CustomerLinkManagementInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\Data\CustomerExtensionFactory;

class CustomerManagement implements \Amazon\Login\Api\CustomerManagementInterface
{
    /**
     * @var CustomerLinkManagement
     */
    private $customerLinkManagement;

    /**
     * @var CustomerExtensionFactory
     */
    private $customerExtensionFactory;

    /**
     * @var string
     */
    private $amazonId;

    /**
     * @param CustomerLinkRepositoryInterface $customerLinkRepository
     * @param CustomerExtensionFactory $customerExtensionFactory
     */
    public function __construct(
        CustomerLinkManagementInterface $customerLinkManagement,
        CustomerExtensionFactory $customerExtensionFactory
    ) {
        $this->customerLinkManagement   = $customerLinkManagement;
        $this->customerExtensionFactory = $customerExtensionFactory;
    }
    /**
     * {@inheritdoc}
     */
    public function setAmazonIdExtensionAttribute(CustomerInterface $customer)
    {
        $customerExtension = ($customer->getExtensionAttributes()) ?: $this->customerExtensionFactory->create();

        if (null === $this->amazonId) {
            $amazonCustomer = $this->customerLinkManagement->getByCustomerId($customer->getId());

            if ($amazonCustomer->getId()) {
                $this->amazonId = $amazonCustomer->getAmazonId();
            }
        }

        if ($this->amazonId) {
            $customerExtension->setAmazonId($this->amazonId);
        }

        $customer->setExtensionAttributes($customerExtension);
    }
}
