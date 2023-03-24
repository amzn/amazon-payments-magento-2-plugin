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

use Amazon\Pay\Api\CustomerLinkManagementInterface;
use Amazon\Pay\Api\Data\AmazonCustomerInterface;
use Amazon\Pay\Domain\AmazonCustomerFactory;
use Amazon\Pay\Model\Adapter\AmazonPayAdapter;
use Amazon\Pay\Model\AmazonConfig;
use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\Validator\ValidatorChain;

class Customer
{
    /**
     * @var AmazonPayAdapter
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
     * Customer constructor
     *
     * @param AmazonPayAdapter $amazonAdapter
     * @param AmazonCustomerFactory $amazonCustomerFactory
     * @param CustomerLinkManagementInterface $customerLinkManagement
     * @param AmazonConfig $amazonConfig
     */
    public function __construct(
        AmazonPayAdapter $amazonAdapter,
        AmazonCustomerFactory $amazonCustomerFactory,
        CustomerLinkManagementInterface $customerLinkManagement,
        AmazonConfig $amazonConfig
    ) {
        $this->amazonAdapter = $amazonAdapter;
        $this->amazonCustomerFactory = $amazonCustomerFactory;
        $this->customerLinkManagement = $customerLinkManagement;
        $this->amazonConfig = $amazonConfig;
    }

    /**
     * Get or create Amazon customer from buyerInfo returned by Amazon
     *
     * @param array $buyerInfo
     * @return \Amazon\Pay\Domain\AmazonCustomer|bool
     */
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

    /**
     * Create magento customer using amazon customer details
     *
     * @param AmazonCustomerInterface $amazonCustomer
     * @return \Magento\Customer\Api\Data\CustomerInterface|null
     * @throws ValidatorException
     */
    public function createCustomer(AmazonCustomerInterface $amazonCustomer)
    {
        if (! ValidatorChain::is($amazonCustomer->getEmail(), \Magento\Framework\Validator\EmailAddress::class)) {
            throw new ValidatorException(__('the email address for your Amazon account is invalid'));
        }

        $customerData = $this->customerLinkManagement->create($amazonCustomer);
        $this->updateCustomerLink($customerData->getId(), $amazonCustomer->getId());

        return $customerData;
    }

    /**
     * Create or update magento/amazon customer link entity
     *
     * @param int $customerDataId
     * @param string $amazonCustomerId
     * @return void
     */
    public function updateCustomerLink($customerDataId, $amazonCustomerId)
    {
        return $this->customerLinkManagement->updateLink($customerDataId, $amazonCustomerId);
    }
}
