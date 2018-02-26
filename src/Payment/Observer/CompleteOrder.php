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

use Amazon\Payment\Model\Method\Amazon;
use Amazon\Payment\Model\OrderInformationManagement;
use Exception;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order;

class CompleteOrder implements ObserverInterface
{
    /**
     * @var OrderInformationManagement
     */
    private $orderInformationManagement;

    /**
     * CompleteOrder constructor.
     * @param OrderInformationManagement $orderInformationManagement
     */
    public function __construct(
        OrderInformationManagement $orderInformationManagement
    ) {
        $this->orderInformationManagement = $orderInformationManagement;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        /**
         * @var OrderInterface $order
         */
        $order    = $observer->getOrder();

        if ($order->getPayment() && $order->getPayment()->getMethod() != Amazon::PAYMENT_METHOD_CODE) {
            return;
        }

        if ($order->getState() == Order::STATE_COMPLETE &&
            ($order->isObjectNew() || (
                array_key_exists(OrderInterface::STATE, $order->getStoredData()) &&
                $order->getStoredData()[OrderInterface::STATE] != Order::STATE_COMPLETE
                )
            )
        ) {
            $this->closeOrderReference($order);
        }
    }

    /**
     * @param OrderInterface $order
     */
    protected function closeOrderReference(OrderInterface $order)
    {
        try {
            $amazonOrderReferenceId = $order->getExtensionAttributes()->getAmazonOrderReferenceId();
            if ($amazonOrderReferenceId) {
                $this->orderInformationManagement->closeOrderReference($amazonOrderReferenceId, $order->getStoreId());
            }
        } catch (Exception $e) {
            //ignored as either it's in a closed state already or it will be auto closed by amazon
            return;
        }
    }
}
