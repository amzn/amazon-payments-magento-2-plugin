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
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Api\Data\OrderExtensionFactory;
use Magento\Sales\Api\Data\OrderInterface;
use Amazon\Payment\Api\Data\QuoteLinkInterfaceFactory;
use Amazon\Payment\Model\Adapter\AmazonPaymentAdapter;

class LoadOrder implements ObserverInterface
{
    /**
     * @var OrderExtensionFactory
     */
    private $orderExtensionFactory;

    /**
     * @var OrderLinkInterfaceFactory
     */
    private $orderLinkFactory;

    /**
     * @var Data
     */
    private $coreHelper;

    private $quoteLinkFactory;

    private $adapter;

    public function __construct(
        OrderExtensionFactory $orderExtensionFactory,
        OrderLinkInterfaceFactory $orderLinkFactory,
        Data $coreHelper,
        QuoteLinkInterfaceFactory $quoteLinkFactory,
        AmazonPaymentAdapter $adapter
    ) {
        $this->orderExtensionFactory = $orderExtensionFactory;
        $this->orderLinkFactory      = $orderLinkFactory;
        $this->coreHelper            = $coreHelper;
        $this->quoteLinkFactory = $quoteLinkFactory;
        $this->adapter = $adapter;
    }

    public function execute(Observer $observer)
    {
        if ($this->coreHelper->isPwaEnabled()) {
            $order = $observer->getOrder();
            $this->setAmazonOrderReferenceIdExtensionAttribute($order);
        }
    }

    protected function setAmazonOrderReferenceIdExtensionAttribute(OrderInterface $order)
    {
        $orderExtension = ($order->getExtensionAttributes()) ?: $this->orderExtensionFactory->create();

        if ($order->getId()) {
            $amazonOrder = $this->orderLinkFactory->create();
            $amazonOrder->load($order->getId(), 'order_id');

            if ($amazonOrder->getId()) {
                $orderExtension->setAmazonOrderReferenceId($amazonOrder);
            } else {
                if ($order->getQuoteId()) {
                    $quoteLink = $this->quoteLinkFactory->create();
                    $quoteLink->load($order->getQuoteId(), 'quote_id');

                    if ($quoteLink->getAmazonOrderReferenceId()) {
                        $amazonOrder->setAmazonOrderReferenceId($quoteLink->getAmazonOrderReferenceId())
                            ->setOrderId($order->getId())
                            ->save();

                        $this->adapter->setOrderAttributes($order->getStoreId(), $quoteLink->getAmazonOrderReferenceId(), $order->getIncrementId());
                    }
                }
            }
        }

        $order->setExtensionAttributes($orderExtension);
    }
}
