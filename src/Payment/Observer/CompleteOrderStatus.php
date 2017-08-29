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
use Amazon\Payment\Model\Method\Amazon;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order;

class CompleteOrderStatus implements ObserverInterface
{
    /**
     * @var Data
     */
    protected $coreHelper;

    public function __construct(
        Data $coreHelper
    ) {
        $this->coreHelper              = $coreHelper;
    }

    public function execute(Observer $observer)
    {
        /**
         * @var OrderInterface $order
         */
        $order          = $observer->getOrder();
        $payment        = $order->getPayment();
        $newOrderStatus = $this->coreHelper->getNewOrderStatus();

        if ($newOrderStatus
            && $order->isObjectNew()
            && Amazon::PAYMENT_METHOD_CODE == $payment->getMethod()
            && $order->getState() == Order::STATE_PROCESSING
        ) {
            $order->setStatus($newOrderStatus);
        }
    }
}
