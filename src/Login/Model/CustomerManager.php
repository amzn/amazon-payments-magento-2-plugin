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

use Amazon\Core\Domain\AmazonCustomer;
use Amazon\Login\Api\CustomerManagerInterface;
use Amazon\Login\Api\Data\CustomerLinkInterfaceFactory;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Framework\Math\Random;

/**
 * @api
 */
class CustomerManager implements CustomerManagerInterface
{
    /**
     * @var CustomerInterfaceFactory
     */
    protected $customerDataFactory;

    /**
     * @var AccountManagementInterface
     */
    protected $accountManagement;

    /**
     * @var Random
     */
    protected $random;

    /**
     * @var CustomerLinkInterfaceFactory
     */
    protected $customerLinkFactory;

    /**
     * CustomerManager constructor.
     *
     * @param CustomerInterfaceFactory     $customerDataFactory
     * @param AccountManagementInterface   $accountManagement
     * @param Random                       $random
     * @param CustomerLinkInterfaceFactory $customerLinkFactory
     */
    public function __construct(
        CustomerInterfaceFactory $customerDataFactory,
        AccountManagementInterface $accountManagement,
        Random $random,
        CustomerLinkInterfaceFactory $customerLinkFactory
    ) {
        $this->customerDataFactory = $customerDataFactory;
        $this->accountManagement   = $accountManagement;
        $this->random              = $random;
        $this->customerLinkFactory = $customerLinkFactory;
    }

    /**
     * Create magento customer using amazon customer details
     *
     * @param AmazonCustomer $amazonCustomer
     *
     * @return CustomerInterface
     */
    public function create(AmazonCustomer $amazonCustomer)
    {
        $customerData = $this->customerDataFactory->create();

        $customerData->setFirstname($amazonCustomer->getFirstName());
        $customerData->setLastname($amazonCustomer->getLastName());
        $customerData->setEmail($amazonCustomer->getEmail());
        $password = $this->random->getRandomString(64);

        $customer = $this->accountManagement->createAccount($customerData, $password);

        return $customer;
    }

    /**
     * Create or update magento/amazon customer link entity
     *
     * @param integer $customerId
     * @param string  $amazonId
     */
    public function updateLink($customerId, $amazonId)
    {
        $customerLink = $this->customerLinkFactory
            ->create();

        $customerLink
            ->load($customerId, 'customer_id')
            ->setAmazonId($amazonId)
            ->setCustomerId($customerId)
            ->save();
    }
}
