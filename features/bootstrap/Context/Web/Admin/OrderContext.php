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
namespace Context\Web\Admin;

use Behat\Behat\Context\SnippetAcceptingContext;
use Fixtures\Customer as CustomerFixture;
use Fixtures\Invoice as InvoiceFixture;
use Fixtures\Order as OrderFixture;
use Page\Admin\CreditMemo;
use Page\Admin\Invoice;
use Page\Admin\Order;
use PHPUnit_Framework_Assert;

class OrderContext implements SnippetAcceptingContext
{
    /**
     * @var Order
     */
    private $orderPage;

    /**
     * @var Invoice
     */
    private $invoicePage;

    /**
     * @var CreditMemo
     */
    private $creditMemoPage;

    public function __construct(Order $orderPage, Invoice $invoicePage, CreditMemo $creditMemoPage)
    {
        $this->orderPage       = $orderPage;
        $this->invoicePage     = $invoicePage;
        $this->creditMemoPage  = $creditMemoPage;
        $this->customerFixture = new CustomerFixture;
        $this->orderFixture    = new OrderFixture;
        $this->invoiceFixture  = new InvoiceFixture;
    }

    /**
     * @Given I go to invoice the last order for :email
     */
    public function iGoToInvoiceTheLastOrderFor($email)
    {
        $lastOrder = $this->orderFixture->getLastOrderForCustomer($email);

        if ( ! $lastOrder) {
            throw new \Exception('Last order not found for ' . $email);
        }

        $orderId = $lastOrder->getId();

        $this->orderPage->openWithOrderId($orderId);
        $this->orderPage->openCreateInvoice();
    }

    /**
     * @Given I submit my invoice
     */
    public function iSubmitMyInvoice()
    {
        $this->orderPage->submitInvoice();
    }

    /**
     * @Given I go to refund the last invoice for :email
     */
    public function iGoToRefundTheLastInvoiceFor($email)
    {
        $lastOrder   = $this->orderFixture->getLastOrderForCustomer($email);
        $lastInvoice = $this->invoiceFixture->getLastForOrder($lastOrder->getId());

        $this->invoicePage->openWithInvoiceId($lastInvoice->getId());
        $this->invoicePage->openCreateCreditMemo();
    }

    /**
     * @Given I submit my refund
     */
    public function iSubmitMyRefund()
    {
        $this->creditMemoPage->submitCreditMemo();
    }

    /**
     * @Then I should be notified that my capture is pending
     */
    public function iShouldBeNotifiedThatMyCaptureIsPending()
    {
        $hasPendingError = $this->invoicePage->hasPendingError();
        PHPUnit_Framework_Assert::assertTrue($hasPendingError);
    }
}