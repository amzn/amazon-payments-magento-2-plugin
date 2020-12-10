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
namespace Amazon\Login\Model\Customer;

use Amazon\Core\Api\Data\AmazonCustomerInterface;
use Amazon\Login\Model\Customer\MatcherInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;

/**
 * @deprecated As of February 2021, this Legacy Amazon Pay plugin has been
 * deprecated, in favor of a newer Amazon Pay version available through GitHub
 * and Magento Marketplace. Please download the new plugin for automatic
 * updates and to continue providing your customers with a seamless checkout
 * experience. Please see https://pay.amazon.com/help/E32AAQBC2FY42HS for details
 * and installation instructions.
 */
class IdMatcher implements MatcherInterface
{
    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * IdMatcher constructor.
     *
     * @param CustomerRepositoryInterface $customerRepository
     * @param SearchCriteriaBuilder       $searchCriteriaBuilder
     */
    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->customerRepository    = $customerRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * {@inheritDoc}
     */
    public function match(AmazonCustomerInterface $amazonCustomer)
    {
        $this->searchCriteriaBuilder->addFilter(
            'amazon_id',
            $amazonCustomer->getId()
        );

        $searchCriteria = $this->searchCriteriaBuilder
            ->setPageSize(1)
            ->setCurrentPage(1)
            ->create();

        $customerList = $this->customerRepository->getList($searchCriteria);

        if (count($items = $customerList->getItems())) {
            return current($items);
        }

        return null;
    }
}
