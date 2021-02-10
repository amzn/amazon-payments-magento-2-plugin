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
namespace Amazon\Pay\Model\Customer;

use Amazon\Pay\Api\Data\AmazonCustomerInterface;
use Amazon\Pay\Model\Customer\MatcherInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class EmailMatcher implements MatcherInterface
{
    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * EmailMatcher constructor.
     *
     * @param CustomerRepositoryInterface $customerRepository
     */
    public function __construct(
        CustomerRepositoryInterface $customerRepository
    ) {
        $this->customerRepository = $customerRepository;
    }

    /**
     * {@inheritDoc}
     */
    public function match(AmazonCustomerInterface $amazonCustomer)
    {
        try {
            $customerData = $this->customerRepository->get($amazonCustomer->getEmail());

            if ($customerData->getId()) {
                return $customerData;
            }
        } catch (NoSuchEntityException $exception) {
            return null;
        }

        return null;
    }
}
