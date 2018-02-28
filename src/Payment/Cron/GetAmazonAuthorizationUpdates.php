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

    public function __construct(
        CollectionFactory $collectionFactory,
        Authorization $authorization,
        Data $coreHelper,
        $limit = 100
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->authorization     = $authorization;
        $this->coreHelper        = $coreHelper;

        $limit = (int)$limit;

        if ($limit < 1) {
            throw new \InvalidArgumentException('Limit must be greater than 1.');
        }

        $this->limit = $limit;
    }

    public function execute()
    {
        if (UpdateMechanism::IPN === $this->coreHelper->getUpdateMechanism()) {
            return;
        }

        $collection = $this->collectionFactory
            ->create()
            ->addOrder(PendingAuthorizationInterface::UPDATED_AT, Collection::SORT_ORDER_ASC)
            ->setPageSize($this->limit)
            ->setCurPage(1);

        $pendingAuthorizationIds = $collection->getIdGenerator();
        foreach ($pendingAuthorizationIds as $pendingAuthorizationId) {
            $this->authorization->updateAuthorization($pendingAuthorizationId);
        }
    }
}
