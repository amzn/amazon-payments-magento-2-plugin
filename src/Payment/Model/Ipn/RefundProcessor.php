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
namespace Amazon\Payment\Model\Ipn;

use Amazon\Payment\Api\Data\PendingRefundInterface;
use Amazon\Payment\Api\Ipn\ProcessorInterface;
use Amazon\Payment\Domain\Details\AmazonRefundDetailsFactory;
use Amazon\Payment\Model\QueuedRefundUpdater;
use Amazon\Payment\Model\ResourceModel\PendingRefund\CollectionFactory;
use Magento\Store\Model\StoreManagerInterface;

/**
 * @deprecated As of February 2021, this Legacy Amazon Pay plugin has been
 * deprecated, in favor of a newer Amazon Pay version available through GitHub
 * and Magento Marketplace. Please download the new plugin for automatic
 * updates and to continue providing your customers with a seamless checkout
 * experience. Please see https://pay.amazon.com/help/E32AAQBC2FY42HS for details
 * and installation instructions.
 */
class RefundProcessor implements ProcessorInterface
{
    /**
     * @var AmazonRefundDetailsFactory
     */
    private $amazonRefundDetailsFactory;

    /**
     * @var QueuedRefundUpdater
     */
    private $queuedRefundUpdater;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        AmazonRefundDetailsFactory $amazonRefundDetailsFactory,
        QueuedRefundUpdater $queuedRefundUpdater,
        CollectionFactory $collectionFactory,
        StoreManagerInterface $storeManager
    ) {
        $this->amazonRefundDetailsFactory = $amazonRefundDetailsFactory;
        $this->queuedRefundUpdater        = $queuedRefundUpdater;
        $this->collectionFactory          = $collectionFactory;
        $this->storeManager               = $storeManager;
    }

    /**
     * {@inheritDoc}
     */
    public function supports(array $ipnData)
    {
        return (isset($ipnData['NotificationType']) && 'PaymentRefund' === $ipnData['NotificationType']);
    }

    /**
     * {@inheritDoc}
     */
    public function process(array $ipnData)
    {
        $details = $this->amazonRefundDetailsFactory->create([
            'details' => $ipnData['RefundDetails']
        ]);

        $collection = $this->collectionFactory
            ->create()
            ->addFieldToFilter(PendingRefundInterface::REFUND_ID, ['eq' => $details->getRefundId()])
            ->setPageSize(1)
            ->setCurPage(1);

        $collection->getSelect()
            ->join(['so' => $collection->getTable('sales_order')], 'main_table.order_id = so.entity_id', [])
            ->where('so.store_id = ?', $this->storeManager->getStore()->getId());

        if (count($items = $collection->getItems())) {
            $pendingRefund = current($items);
            $this->queuedRefundUpdater->setThrowExceptions(true);
            $this->queuedRefundUpdater->checkAndUpdateRefund($pendingRefund->getId(), $details);
        }
    }
}
