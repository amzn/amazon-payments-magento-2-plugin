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

use Amazon\Payment\Api\Data\PendingAuthorizationInterface;
use Amazon\Payment\Api\Ipn\ProcessorInterface;
use Amazon\Payment\Model\PaymentManagement\Authorization;
use Amazon\Payment\Domain\Details\AmazonOrderDetailsFactory;
use Amazon\Payment\Model\ResourceModel\OrderLink;
use Amazon\Payment\Model\ResourceModel\PendingAuthorization\CollectionFactory;
use Magento\Store\Model\StoreManagerInterface;

class OrderProcessor implements ProcessorInterface
{
    /**
     * @var AmazonOrderDetailsFactory
     */
    private $amazonOrderDetailsFactory;

    /**
     * @var AuthorizationInterface
     */
    private $authorization;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        AmazonOrderDetailsFactory $amazonOrderDetailsFactory,
        Authorization $authorization,
        CollectionFactory $collectionFactory,
        StoreManagerInterface $storeManager
    ) {

        $this->amazonOrderDetailsFactory = $amazonOrderDetailsFactory;
        $this->collectionFactory         = $collectionFactory;
        $this->authorization             = $authorization;
        $this->storeManager              = $storeManager;
    }

    /**
     * {@inheritDoc}
     */
    public function supports(array $ipnData)
    {
        return (isset($ipnData['NotificationType']) && 'OrderReferenceNotification' === $ipnData['NotificationType']);
    }

    /**
     * {@inheritDoc}
     */
    public function process(array $ipnData)
    {
        $details = $this->amazonOrderDetailsFactory->create([
            'details' => $ipnData['OrderReference']
        ]);

        $collection = $this->collectionFactory
            ->create()
            ->addFieldToFilter(PendingAuthorizationInterface::PROCESSED, ['eq' => 1])
            ->setPageSize(1)
            ->setCurPage(1);

        $collection->getSelect()
            ->join(['so' => $collection->getTable('sales_order')], 'main_table.order_id = so.entity_id', [])
            ->where('so.store_id = ?', $this->storeManager->getStore()->getId())
            ->join(['ao' => $collection->getTable(OrderLink::TABLE_NAME)], 'main_table.order_id = ao.order_id', [])
            ->where('ao.amazon_order_reference_id = ?', $details->getOrderReferenceId());

        if (count($items = $collection->getItems())) {
            $pendingAuthorization = current($items);
            $this->authorization->setThrowExceptions(true);
            $this->authorization->updateAuthorization($pendingAuthorization->getId(), null, $details);
        }
    }
}
