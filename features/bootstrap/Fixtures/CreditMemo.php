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
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrder;
use Magento\Sales\Api\CreditmemoRepositoryInterface;

class CreditMemo extends BaseFixture
{
    /**
     * @var CreditmemoRepositoryInterface
     */
    private $repository;

    public function __construct()
    {
        parent::__construct();
        $this->repository = $this->getMagentoObject(CreditmemoRepositoryInterface::class);
    }

    public function getLastForOrder($orderid)
    {
        $searchCriteriaBuilder = $this->createMagentoObject(SearchCriteriaBuilder::class);
        $searchCriteriaBuilder->addFilter(
            'order_id', $orderid
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

        $creditMemos = $this->repository->getList($searchCriteria);

        $creditMemo = current($creditMemos->getItems());

        if ( ! $creditMemo) {
            throw new \Exception('Credit memo not found for order id ' . $orderid);
        }

        return $creditMemo;
    }
}