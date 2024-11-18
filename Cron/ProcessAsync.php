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
namespace Amazon\Pay\Cron;

use Amazon\Pay\Api\Data\AsyncInterface;
use Magento\Framework\Data\Collection;

class ProcessAsync
{
    /**
     * @var \Amazon\Pay\Model\ResourceModel\Async\CollectionFactory
     */
    private $asyncCollectionFactory;

    /**
     * @var \Amazon\Pay\Model\AsyncUpdaterFactory
     */
    private $asyncUpdater;

    /**
     * @var int
     */
    private $limit;

    /**
     * ProcessAsync constructor.
     *
     * @param \Amazon\Pay\Model\ResourceModel\Async\CollectionFactory $asyncCollectionFactory
     * @param \Amazon\Pay\Model\AsyncUpdater $asyncUpdater
     * @param int $limit
     */
    public function __construct(
        \Amazon\Pay\Model\ResourceModel\Async\CollectionFactory $asyncCollectionFactory,
        \Amazon\Pay\Model\AsyncUpdater $asyncUpdater,
        $limit = 100
    ) {
        $limit = (int)$limit;

        if ($limit < 1) {
            throw new \InvalidArgumentException('Limit must be greater than 0.');
        }

        $this->asyncCollectionFactory = $asyncCollectionFactory;
        $this->asyncUpdater = $asyncUpdater;
        $this->limit = $limit;
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        $collection = $this->asyncCollectionFactory
            ->create()
            ->addFilter(AsyncInterface::IS_PENDING, true)
            ->addOrder(AsyncInterface::ID, Collection::SORT_ORDER_ASC)
            ->setPageSize($this->limit)
            ->setCurPage(1);

        /** @var \Amazon\Pay\Model\Async $async */
        foreach ($collection as $async) {
            $this->asyncUpdater->processPending($async);
        }
    }
}
