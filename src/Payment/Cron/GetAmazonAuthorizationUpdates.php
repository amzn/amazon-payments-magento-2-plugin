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
namespace Amazon\Payment\Cron;

use Amazon\Core\Helper\Data;
use Amazon\Core\Model\Config\Source\UpdateMechanism;
use Amazon\Payment\Api\Data\PendingAuthorizationInterface;
use Amazon\Payment\Model\PaymentManagement\Authorization;
use Amazon\Payment\Model\ResourceModel\PendingAuthorization\CollectionFactory;
use Magento\Framework\Data\Collection;
use Magento\Sales\Api\TransactionRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\FilterGroup;
use Psr\Log\LoggerInterface;

class GetAmazonAuthorizationUpdates
{
    /**
     * @var int
     */
    private $limit;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var Authorization
     */
    private $authorization;

    /**
     * @var Data
     */
    private $coreHelper;

    /**
     * @var TransactionRepositoryInterface
     */
    private $transactionRepository;

    /**
     * @var FilterBuilder
     */
    private $filterBuilder;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchBuilder;

    /**
     * @var FilterGroup
     */
    private $filterGroup;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * GetAmazonAuthorizationUpdates constructor.
     * @param CollectionFactory $collectionFactory
     * @param Authorization $authorization
     * @param Data $coreHelper
     * @param TransactionRepositoryInterface $transactionRepository
     * @param SearchCriteriaBuilder $searchBuilder
     * @param FilterBuilder $filterBuilder
     * @param FilterGroup $filterGroup
     * @param LoggerInterface $logger
     * @param int $limit
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        Authorization $authorization,
        Data $coreHelper,
        TransactionRepositoryInterface $transactionRepository,
        SearchCriteriaBuilder $searchBuilder,
        FilterBuilder $filterBuilder,
        FilterGroup $filterGroup,
        LoggerInterface $logger,
        $limit = 100
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->authorization     = $authorization;
        $this->coreHelper        = $coreHelper;
        $this->transactionRepository = $transactionRepository;
        $this->searchBuilder = $searchBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->filterGroup = $filterGroup;
        $this->logger = $logger;

        $limit = (int)$limit;

        if ($limit < 1) {
            throw new \InvalidArgumentException('Limit must be greater than 1.');
        }

        $this->limit = $limit;
    }

    /**
     * Since we can't get order or payment ID during gateway transaction, we make sure items in the
     * amazon_pending_authorization table have these IDs if they are not set by matching them to a transaction that
     * has matching transaction or parent transaction IDs.
     */
    private function updateIds()
    {
        // only get items that have no order ID set since we don't want to have to keep repeating this
        $collection = $this->collectionFactory
            ->create()
            ->addFieldToFilter('order_id', ['eq' => 0])
            ->addOrder(PendingAuthorizationInterface::UPDATED_AT, Collection::SORT_ORDER_ASC)
            ->setPageSize($this->limit)
            ->setCurPage(1);

        foreach ($collection->getItems() as $item) {
            if ($item) {
                $hasTransaction = false;

                $parent = $this->filterBuilder
                    ->setField('parent_txn_id')
                    ->setValue($item->getAuthorizationId())
                    ->setConditionType('eq')
                    ->create();
                $child = $this->filterBuilder
                    ->setField('txn_id')
                    ->setValue($item->getAuthorizationId())
                    ->setConditionType('eq')
                    ->create();

                $filterOr = $this->filterGroup->setFilters([$parent, $child]);

                $searchCriteria = $this->searchBuilder->setFilterGroups([$filterOr])->create();

                $transactionList = $this->transactionRepository->getList($searchCriteria);

                foreach ($transactionList->getItems() as $transaction) {
                    if ($transaction) {
                        $item->setPaymentId($transaction->getPaymentId());
                        $item->setOrderId($transaction->getOrderId());
                        $item->save();
                        $hasTransaction = true;
                    }
                }

                // If there's no match, get rid of this item in the table so the cron job will not error out.
                if (!$hasTransaction) {
                    $item->delete();
                }
            }
        }
    }

    /**
     * During cron, process payments if possible.
     */
    public function execute()
    {
        if (UpdateMechanism::IPN === $this->coreHelper->getUpdateMechanism()) {
            return;
        }

        $this->updateIds();

        $collection = $this->collectionFactory
            ->create()
            ->addOrder(PendingAuthorizationInterface::UPDATED_AT, Collection::SORT_ORDER_ASC)
            ->setPageSize($this->limit)
            ->setCurPage(1);

        $pendingAuthorizationIds = $collection->getIdGenerator();
        foreach ($pendingAuthorizationIds as $pendingAuthorizationId) {
            try {
                $this->authorization->updateAuthorization($pendingAuthorizationId);
            } catch (\Exception $e) {
                $this->logger->error($e);
            }
        }
    }
}
