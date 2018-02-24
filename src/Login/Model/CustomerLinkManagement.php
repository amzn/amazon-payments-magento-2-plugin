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

use Amazon\Login\Model\CustomerLinkRepositryFactory;
use Amazon\Login\Api\CustomerLinkRepositoryInterface;
use Amazon\Login\Api\Data\CustomerLinkInterfaceFactory;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Customer\Api\Data\CustomerExtensionFactory;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\Math\Random;

class CustomerLinkManagement implements \Amazon\Login\Api\CustomerLinkManagementInterface
{
    /**
     * @var CustomerLinkRepositoryInterface
     */
    protected $customerLinkRepository;

    /**
     * @var CustomerExtensionFactory
     */
    protected $customerExtensionFactory;

    /**
     * @var CustomerLinkFactory
     */
    protected $customerLinkFactory;

    /**
     * @var CustomerInterface
     */
    protected $customerInterface;

    /**
     * @var Session
     */
    protected $customerSession;

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
    private $random;

    /**
     * @param CustomerLinkRepositoryInterface $customerLinkRepository
     * @param CustomerExtensionFactory $customerExtensionFactory
     * @param CustomerLinkFactory $customerLinkFactory
     * @param CustomerInterface $customerInterface
     * @param Session $customerSession
     * @param CustomerInterfaceFactory $customerDataFactory
     * @param AccountManagementInterface $accountManagement
     * @param Random $random
     */
    public function __construct(
        CustomerLinkRepositoryInterface $customerLinkRepository,
        CustomerExtensionFactory $customerExtensionFactory,
        CustomerLinkFactory $customerLinkFactory,
        CustomerInterface $customerInterface,
        Session $customerSession,
        CustomerInterfaceFactory $customerDataFactory,
        AccountManagementInterface $accountManagement,
        Random $random
    ) {
        $this->customerLinkRepository   = $customerLinkRepository;
        $this->customerExtensionFactory = $customerExtensionFactory;
        $this->customerLinkFactory = $customerLinkFactory;
        $this->customerInterface   = $customerInterface;
        $this->customerSession     = $customerSession;
        $this->customerDataFactory = $customerDataFactory;
        $this->accountManagement   = $accountManagement;
        $this->random              = $random;
    }

    /**
     * {@inheritdoc}
     */
    public function getByCustomerId($customerId)
    {
        return $this->customerLinkFactory->create()->load($customerId, 'customer_id');
    }

    /**
     * {@inheritdoc}
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

    /**
     * {@inheritdoc}
     */
    public function setAmazonIdExtensionAttribute(CustomerInterface $customer)
    {
        $isSession = $this->customerSession->getId() == $customer->getId();
        $amazonId  = $isSession ? $this->customerSession->getAmazonId() : null;

        $customerExtension = ($customer->getExtensionAttributes()) ?: $this->customerExtensionFactory->create();

        if (null === $amazonId) {
            $amazonCustomer = $this->getByCustomerId($customer->getId());

            if ($amazonCustomer->getId()) {
                $amazonId = $amazonCustomer->getAmazonId();
            }

            if ($isSession) {
                $this->customerSession->setAmazonId($amazonId ? $amazonId : false);
            }
        }

        if ($amazonId) {
            $customerExtension->setAmazonId($amazonId);
        }

        $customer->setExtensionAttributes($customerExtension);
    }
}
