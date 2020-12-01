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
use Amazon\Payment\Api\Data\PendingCaptureInterface;
use Amazon\Payment\Model\PaymentManagement\Capture;
use Amazon\Payment\Model\ResourceModel\PendingCapture\CollectionFactory;
use Magento\Framework\Data\Collection;
use Magento\Sales\Api\TransactionRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\FilterGroup;
use Psr\Log\LoggerInterface;

/**
 * @deprecated As of February 2021, this Legacy Amazon Pay plugin has been
 * deprecated, in favor of a newer Amazon Pay version available through GitHub
 * and Magento Marketplace. Please download the new plugin for automatic
 * updates and to continue providing your customers with a seamless checkout
 * experience. Please see https://pay.amazon.com/help/E32AAQBC2FY42HS for details
 * and installation instructions.
 */
class GetAmazonCaptureUpdates
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
     * @var Capture
     */
    private $capture;

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
     * GetAmazonCaptureUpdates constructor.
     * @param CollectionFactory $collectionFactory
     * @param Capture $capture
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
        Capture $capture,
        Data $coreHelper,
        TransactionRepositoryInterface $transactionRepository,
        SearchCriteriaBuilder $searchBuilder,
        FilterBuilder $filterBuilder,
        FilterGroup $filterGroup,
        LoggerInterface $logger,
        $limit = 100
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->capture           = $capture;
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
     * Since we might not get order or payment ID during gateway transaction, we make sure items in the
     * amazon_pending_capture table have these IDs if they are not set by matching them to a transaction that
     * has matching transaction or parent transaction IDs.
     */
    private function updateIds()
    {
        // only get items that have no order ID set since we don't want to have to keep repeating this
        $collection = $this->collectionFactory
            ->create()
            ->addFieldToFilter('order_id', ['eq' => 0])
            ->addOrder(PendingCaptureInterface::CREATED_AT, Collection::SORT_ORDER_ASC)
            ->setPageSize($this->limit)
            ->setCurPage(1);

        foreach ($collection->getItems() as $item) {
            if ($item) {
                $hasTransaction = false;

                $parent = $this->filterBuilder
                    ->setField('parent_txn_id')
                    ->setValue($item->getCaptureId())
                    ->setConditionType('eq')
                    ->create();
                $child = $this->filterBuilder
                    ->setField('txn_id')
                    ->setValue($item->getCaptureId())
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
            ->addOrder(PendingCaptureInterface::CREATED_AT, Collection::SORT_ORDER_ASC)
            ->setPageSize($this->limit)
            ->setCurPage(1);

        $pendingCaptureIds = $collection->getIdGenerator();
        foreach ($pendingCaptureIds as $pendingCaptureId) {
            try {
                $this->capture->updateCapture($pendingCaptureId);
            } catch (\Exception $e) {
                $this->logger->error($e);
            }
        }
    }
}
