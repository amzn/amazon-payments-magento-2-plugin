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
use PHPUnit_Framework_Assert;
use SensioLabs\Behat\PageObjectExtension\PageObject\Page;

class Invoice extends Page
{
    use PageTrait;

    private $elements
        = [
            'credit-memo' => ['css' => 'button.credit-memo'],
            'error'       => ['css' => '.message-error']
        ];

    private $path = '/admin/sales/order_invoice/view/invoice_id/{id}';

    public function openWithInvoiceId($invoiceId)
    {
        $this->open(['id' => (int)$invoiceId]);
    }

    public function openCreateCreditMemo()
    {
        $this->clickElement('credit-memo');
    }

    public function hasPendingError()
    {
        try {
            $this->waitForPageLoad();
            $element    = $this->getElementWithWait('error');
            $constraint = PHPUnit_Framework_Assert::stringContains(
                'Capture pending approval from the payment gateway',
                false
            );

            PHPUnit_Framework_Assert::assertThat($element->getText(), $constraint);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}