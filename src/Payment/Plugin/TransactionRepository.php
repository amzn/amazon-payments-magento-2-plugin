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
namespace Amazon\Payment\Plugin;

use Closure;
use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Api\SortOrder;
use Magento\Sales\Api\TransactionRepositoryInterface;
use Magento\Sales\Model\ResourceModel\Order\Payment\Transaction\Collection;

class TransactionRepository
{
    /**
     * Fix core bug where sort order is not applied
     *
     * @param TransactionRepositoryInterface $transactionRepository
     * @param Closure                        $proceed
     * @param SearchCriteria                 $searchCriteria
     *
     * @return Collection
     */
    public function aroundGetList(
        TransactionRepositoryInterface $transactionRepository,
        Closure $proceed,
        SearchCriteria $searchCriteria
    ) {
        $collection = $proceed($searchCriteria);

        if ($collection instanceof Collection) {
            $sortOrders = $searchCriteria->getSortOrders();
            if ($sortOrders) {
                foreach ($sortOrders as $sortOrder) {
                    $collection->addOrder(
                        $sortOrder->getField(),
                        ($sortOrder->getDirection() == SortOrder::SORT_ASC) ? 'ASC' : 'DESC'
                    );
                }
            }
        }

        return $collection;
    }
}
