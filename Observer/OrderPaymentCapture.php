<?php
/**
 * Copyright Amazon.com, Inc. or its affiliates. All Rights Reserved.
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
namespace Amazon\Pay\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class OrderPaymentCapture implements ObserverInterface
{
    /**
     * Ensure payment amount is expressed in display currency instead of base currency
     *
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $payment = $observer->getPayment();
        $invoice = $observer->getInvoice();

        $payment->setAmazonDisplayInvoiceAmount($invoice->getGrandTotal());
    }
}
