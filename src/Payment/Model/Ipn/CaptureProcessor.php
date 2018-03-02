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

use Amazon\Payment\Api\Data\PendingCaptureInterface;
use Amazon\Payment\Api\Ipn\ProcessorInterface;
use Amazon\Payment\Model\PaymentManagement\Capture;
use Amazon\Payment\Domain\Details\AmazonCaptureDetailsFactory;
use Amazon\Payment\Model\ResourceModel\PendingCapture\CollectionFactory;
use Magento\Store\Model\StoreManagerInterface;

class CaptureProcessor implements ProcessorInterface
{
    /**
     * @var AmazonCaptureDetailsFactory
     */
    private $amazonCaptureDetailsFactory;

    /**
     * @var Capture
     */
    private $capture;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        AmazonCaptureDetailsFactory $amazonCaptureDetailsFactory,
        Capture $capture,
        CollectionFactory $collectionFactory,
        StoreManagerInterface $storeManager
    ) {
        $this->amazonCaptureDetailsFactory = $amazonCaptureDetailsFactory;
        $this->capture                     = $capture;
        $this->collectionFactory           = $collectionFactory;
        $this->storeManager                = $storeManager;
    }

    /**
     * {@inheritDoc}
     */
    public function supports(array $ipnData)
    {
        return (isset($ipnData['NotificationType']) && 'PaymentCapture' === $ipnData['NotificationType']);
    }

    /**
     * {@inheritDoc}
     */
    public function process(array $ipnData)
    {
        $details = $this->amazonCaptureDetailsFactory->create([
            'details' => $ipnData['CaptureDetails']
        ]);

        $collection = $this->collectionFactory
            ->create()
            ->addFieldToFilter(PendingCaptureInterface::CAPTURE_ID, ['eq' => $details->getTransactionId()])
            ->setPageSize(1)
            ->setCurPage(1);

        $collection->getSelect()
            ->join(['so' => $collection->getTable('sales_order')], 'main_table.order_id = so.entity_id', [])
            ->where('so.store_id = ?', $this->storeManager->getStore()->getId());

        if (count($items = $collection->getItems())) {
            $pendingCapture = current($items);
            $this->capture->setThrowExceptions(true);
            $this->capture->updateCapture($pendingCapture->getId(), $details);
        }
    }
}
