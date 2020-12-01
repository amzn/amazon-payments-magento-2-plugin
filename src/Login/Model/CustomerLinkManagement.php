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

use Amazon\Core\Api\Data\AmazonCustomerInterface;
use Amazon\Login\Model\CustomerLinkRepositryFactory;
use Amazon\Login\Api\CustomerLinkRepositoryInterface;
use Amazon\Login\Api\Data\CustomerLinkInterfaceFactory;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\Math\Random;

/**
 * @deprecated As of February 2021, this Legacy Amazon Pay plugin has been
 * deprecated, in favor of a newer Amazon Pay version available through GitHub
 * and Magento Marketplace. Please download the new plugin for automatic
 * updates and to continue providing your customers with a seamless checkout
 * experience. Please see https://pay.amazon.com/help/E32AAQBC2FY42HS for details
 * and installation instructions.
 */
class CustomerLinkManagement implements \Amazon\Login\Api\CustomerLinkManagementInterface
{
    /**
     * @var CustomerLinkRepositoryInterface
     */
    private $customerLinkRepository;

    /**
     * @var CustomerLinkFactory
     */
    private $customerLinkFactory;

    /**
     * @var CustomerInterface
     */
    private $customerInterface;

    /**
     * @var CustomerInterfaceFactory
     */
    private $customerDataFactory;

    /**
     * @var AccountManagementInterface
     */
    private $accountManagement;

    /**
     * @var Random
     */
    private $random;

    /**
     * @param CustomerLinkRepositoryInterface $customerLinkRepository
     * @param CustomerLinkFactory             $customerLinkFactory
     * @param CustomerInterface               $customerInterface
     * @param CustomerInterfaceFactory        $customerDataFactory
     * @param AccountManagementInterface      $accountManagement
     * @param Random                          $random
     */
    public function __construct(
        CustomerLinkRepositoryInterface $customerLinkRepository,
        CustomerLinkFactory $customerLinkFactory,
        CustomerInterface $customerInterface,
        CustomerInterfaceFactory $customerDataFactory,
        AccountManagementInterface $accountManagement,
        Random $random
    ) {
        $this->customerLinkRepository   = $customerLinkRepository;
        $this->customerLinkFactory = $customerLinkFactory;
        $this->customerInterface   = $customerInterface;
        $this->customerDataFactory = $customerDataFactory;
        $this->accountManagement   = $accountManagement;
        $this->random              = $random;
    }

    /**
     * {@inheritdoc}
     */
    public function getByCustomerId($customerId)
    {
        return $this->customerLinkRepository->get($customerId);
    }

    /**
     * {@inheritdoc}
     */
    public function create(AmazonCustomerInterface $amazonCustomer)
    {
        $customerData = $this->customerDataFactory->create();

        $customerData->setFirstname($amazonCustomer->getFirstName());
        $customerData->setLastname($amazonCustomer->getLastName());
        $customerData->setEmail($amazonCustomer->getEmail());
        $password = "4mZ!" . $this->random->getRandomString(60);

        $customer = $this->accountManagement->createAccount($customerData, $password);

        return $customer;
    }

    /**
     * {@inheritdoc}
     */
    public function updateLink($customerId, $amazonId)
    {
        $customerLink = $this->customerLinkFactory->create();

        $customerLink
            ->load($customerId, 'customer_id')
            ->setAmazonId($amazonId)
            ->setCustomerId($customerId);

        $this->customerLinkRepository->save($customerLink);
    }
}
