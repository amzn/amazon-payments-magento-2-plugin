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
namespace Page\Admin;

use Page\PageTrait;
use SensioLabs\Behat\PageObjectExtension\PageObject\Page;

class Order extends Page
{
    use PageTrait;

    private $elements
        = [
            'invoice' => ['css' => '#order_invoice'],
            'submit-invoice' => ['css' => '#invoice_totals .submit-button']
        ];

    private $path = '/admin/sales/order/view/order_id/{id}';

    public function openWithOrderId($orderId)
    {
        $this->open(['id' => (int)$orderId]);
    }

    public function openCreateInvoice()
    {
        $this->clickElement('invoice');
        $this->waitForPageLoad();
    }

    public function submitInvoice()
    {
        $this->clickElement('submit-invoice');
    }
}