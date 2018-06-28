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
namespace Fixtures;

use Bex\Behat\Magento2InitExtension\Fixtures\BaseFixture;
use Fixtures\Customer as CustomerFixture;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrder;
use Magento\Sales\Api\OrderRepositoryInterface;

class Order extends BaseFixture
{
    /**
     * @var CustomerFixture
     */
    private $customerFixture;
    
    public function __construct()
    {
        parent::__construct();
        $this->customerFixture = new CustomerFixture;
    }

    public function getForCustomer(CustomerInterface $customer)
    {
        $searchCriteriaBuilder = $this->createMagentoObject(SearchCriteriaBuilder::class);
        $searchCriteriaBuilder->addFilter(
            'customer_id', $customer->getId()
        );

        $sortOrder = $this->createMagentoObject(SortOrder::class, [
            'data' => [
                SortOrder::FIELD     => 'created_at',
                SortOrder::DIRECTION => SortOrder::SORT_DESC
            ]
        ]);

        $searchCriteriaBuilder->addSortOrder($sortOrder);

        $searchCriteria = $searchCriteriaBuilder
            ->create();

        $repository = $this->createMagentoObject(OrderRepositoryInterface::class);
        return $repository->getList($searchCriteria);
    }

    /**
     * @param $email
     * @return \Magento\Sales\Api\Data\OrderInterface
     * @throws \Exception
     */
    public function getLastOrderForCustomer($email)
    {
        $customer = $this->customerFixture->get($email, true);
        $orders   = $this->getForCustomer($customer);

        $order = current($orders->getItems());

        if ( ! $order) {
            throw new \Exception('Last order not found for ' . $email);
        }

        $order->load($order->getId());

        return $order;
    }
}
