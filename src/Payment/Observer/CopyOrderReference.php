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
namespace Amazon\Payment\Observer;

use Amazon\Core\Helper\Data;
use Amazon\Payment\Api\Data\OrderLinkInterfaceFactory;
use Amazon\Payment\Api\Data\QuoteLinkInterfaceFactory;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order;

class CopyOrderReference implements ObserverInterface
{
    /**
     * @var QuoteLinkInterfaceFactory
     */
    private $quoteLinkFactory;

    /**
     * @var OrderLinkInterfaceFactory
     */
    private $orderLinkFactory;

    /**
     * @var Data
     */
    private $coreHelper;

    public function __construct(
        QuoteLinkInterfaceFactory $quoteLinkFactory,
        OrderLinkInterfaceFactory $orderLinkFactory,
        Data $coreHelper
    ) {
        $this->quoteLinkFactory = $quoteLinkFactory;
        $this->orderLinkFactory = $orderLinkFactory;
        $this->coreHelper       = $coreHelper;
    }

    public function execute(Observer $observer)
    {
        if ($this->coreHelper->isPwaEnabled()) {
            $order = $observer->getOrder();

            if ($order instanceof Order) {
                $orderId = $order->getId();
                $quoteId = $order->getQuoteId();

                $quoteLink = $this->quoteLinkFactory->create();
                $quoteLink->load($quoteId, 'quote_id');

                $amazonOrderReferenceId = $quoteLink->getAmazonOrderReferenceId();
                if ($amazonOrderReferenceId !== null) {
                    $orderLink = $this->orderLinkFactory->create();
                    $orderLink
                        ->load($orderId, 'order_id')
                        ->setAmazonOrderReferenceId($amazonOrderReferenceId)
                        ->setOrderId($orderId)
                        ->save();
                }
            }
        }
    }
}
