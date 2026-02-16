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
     * CustomerLinkManagement constructor
     *
     * @param CustomerLinkRepositoryInterface $customerLinkRepository
     * @param CustomerLinkFactory             $customerLinkFactory
     * @param CustomerInterface               $customerInterface
     * @param CustomerInterfaceFactory        $customerDataFactory
     * @param AccountManagementInterface      $accountManagement
     */
    public function __construct(
        CustomerLinkRepositoryInterface $customerLinkRepository,
        CustomerLinkFactory $customerLinkFactory,
        CustomerInterface $customerInterface,
        CustomerInterfaceFactory $customerDataFactory,
        AccountManagementInterface $accountManagement,
    ) {
        $this->customerLinkRepository   = $customerLinkRepository;
        $this->customerLinkFactory = $customerLinkFactory;
        $this->customerInterface   = $customerInterface;
        $this->customerDataFactory = $customerDataFactory;
        $this->accountManagement   = $accountManagement;
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
        $password = $this->generatePassword();

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

    /**
     * Generate a password that satisfies Magento's 4 character-class strength check:
     * lower, upper, digits, special.
     *
     * @param int $length
     * @return string
     */
    private function generatePassword($length = 64)
    {
        $lower   = 'abcdefghijklmnopqrstuvwxyz';
        $upper   = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $digits  = '0123456789';
        // Common specials; avoid whitespace and quotes to reduce edge-case issues
        $special = '!@#$%^&*()-_=+[]{};:,.?~';

        // Ensure at least one from each class
        $chars = [
            $this->randChar($lower),
            $this->randChar($upper),
            $this->randChar($digits),
            $this->randChar($special),
        ];

        $all = $lower . $upper . $digits . $special;

        // Fill the rest
        for ($i = count($chars); $i < $length; $i++) {
            $chars[] = $this->randChar($all);
        }

        // Shuffle to avoid predictable positions
        shuffle($chars);

        return implode('', $chars);
    }

    /**
     * @param string $pool
     * @return string
     */
    private function randChar($pool)
    {
        $max = strlen($pool) - 1;
        return $pool[random_int(0, $max)];
    }
}
