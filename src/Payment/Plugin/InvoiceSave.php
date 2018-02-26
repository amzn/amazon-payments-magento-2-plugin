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
namespace Amazon\Payment\Plugin;

use Amazon\Payment\Model\Method\Amazon;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Sales\Api\Data\OrderInterfaceFactory;
use Magento\Sales\Controller\Adminhtml\Order\Invoice\Save;
use Magento\Sales\Model\Order\Invoice;

class InvoiceSave
{
    /**
     * @var OrderInterfaceFactory
     */
    private $orderFactory;

    /**
     * @var Context
     */
    private $context;

    public function __construct(OrderInterfaceFactory $orderFactory, Context $context)
    {
        $this->orderFactory = $orderFactory;
        $this->context      = $context;
    }

    public function afterExecute(Save $save, Redirect $redirect)
    {
        $orderId = $save->getRequest()->getParam('order_id');
        $order   = $this->orderFactory->create();
        $order->load($orderId);

        if ($order->getPayment() && Amazon::PAYMENT_METHOD_CODE == $order->getPayment()->getMethod()) {
            $lastInvoice = $order->getInvoiceCollection()->getLastItem();

            if ($lastInvoice && Invoice::STATE_OPEN == $lastInvoice->getState()) {
                $this->context->getMessageManager()->addErrorMessage(__('Capture pending approval from ' .
                    'the payment gateway'));
            }
        }

        return $redirect;
    }
}
