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
namespace Amazon\Pay\Model;

use Amazon\Pay\Api\Data\AmazonCustomerInterface;
use Amazon\Pay\Model\CustomerLinkRepositryFactory;
use Amazon\Pay\Api\CustomerLinkRepositoryInterface;
use Amazon\Pay\Api\Data\CustomerLinkInterfaceFactory;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\Math\Random;

class CustomerLinkManagement implements \Amazon\Pay\Api\CustomerLinkManagementInterface
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
     * CustomerLinkManagement constructor
     *
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
     * @inheritdoc
     */
    public function getByCustomerId($customerId)
    {
        return $this->customerLinkRepository->get($customerId);
    }

    /**
     * @inheritdoc
     */
    public function create(AmazonCustomerInterface $amazonCustomer)
    {
        $customerData = $this->customerDataFactory->create();
        $sanitizedNames = $this->getSanitizedNameData($amazonCustomer);

        $customerData->setFirstname($sanitizedNames['first_name']);
        $customerData->setLastname($sanitizedNames['last_name']);
        $customerData->setEmail($amazonCustomer->getEmail());
        $password = $this->random->getRandomString(64);

        $customer = $this->accountManagement->createAccount($customerData, $password);

        return $customer;
    }

    /**
     * @inheritdoc
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

    /**
     * Remove special characters allowed in Amazon account names, but not Magento account names
     *
     * @param AmazonCustomerInterface $customer
     * @return array
     */
    private function getSanitizedNameData($customer)
    {
        $pattern = '/([^\p{L}\p{M}\,\-\_\.\'\s\d]){1,255}+/u';

        return [
            'first_name' => trim(preg_replace($pattern, '', htmlspecialchars_decode($customer->getFirstname()))),
            'last_name'  => trim(preg_replace($pattern, '', htmlspecialchars_decode($customer->getLastname())))
        ];
    }
}
