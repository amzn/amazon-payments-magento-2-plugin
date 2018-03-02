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
namespace Context\Data;

use Behat\Behat\Context\SnippetAcceptingContext;
use Fixtures\AdminNotification as AdminNotificationFixture;
use Fixtures\CreditMemo as CreditMemoFixture;
use Fixtures\Customer as CustomerFixture;
use Fixtures\Invoice as InvoiceFixture;
use Fixtures\Order as OrderFixture;
use Fixtures\Transaction as TransactionFixture;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order\Payment\Transaction;
use PHPUnit_Framework_Assert;

class OrderContext implements SnippetAcceptingContext
{
    /**
     * @var CustomerFixture
     */
    private $customerFixture;

    /**
     * @var OrderFixture
     */
    private $orderFixture;

    /**
     * @var TransactionFixture
     */
    private $transactionFixture;

    /**
     * @var invoiceFixture
     */
    private $invoiceFixture;

    /**
     * @var CreditMemoFixture
     */
    private $creditMemoFixture;

    /**
     * @var AdminNotificationFixture
     */
    private $adminNotificationFixture;

    public function __construct()
    {
        $this->customerFixture          = new CustomerFixture;
        $this->orderFixture             = new OrderFixture;
        $this->transactionFixture       = new TransactionFixture;
        $this->invoiceFixture           = new InvoiceFixture;
        $this->creditMemoFixture        = new CreditMemoFixture;
        $this->adminNotificationFixture = new AdminNotificationFixture;
    }

    /**
     * @Given :email should not have placed an order
     */
    public function shouldNotHavePlacedAnOrder($email)
    {
        $customer = $this->customerFixture->get($email);
        $orders   = $this->orderFixture->getForCustomer($customer);

        $orderCount = count($orders->getItems());

        PHPUnit_Framework_Assert::assertSame(0, $orderCount);
    }

    /**
     * @Then :email should have placed an order
     */
    public function shouldHavePlacedAnOrder($email)
    {
        $customer = $this->customerFixture->get($email);
        $orders   = $this->orderFixture->getForCustomer($customer);

        $orderCount = count($orders->getItems());

        PHPUnit_Framework_Assert::assertSame(1, $orderCount);
    }

    /**
     * @Then there should be an open authorization for the last order for :email
     */
    public function thereShouldBeAnOpenAuthorizationForTheLastOrderFor($email)
    {
        $transaction = $this->transactionFixture->getLastTransactionForLastOrder($email);

        PHPUnit_Framework_Assert::assertSame(Transaction::TYPE_AUTH, $transaction->getTxnType());
        PHPUnit_Framework_Assert::assertSame('0', $transaction->getIsClosed());
    }

    /**
     * @Given there should be a closed authorization for the last order for :email
     */
    public function thereShouldBeAClosedAuthorizationForTheLastOrderFor($email)
    {
        $lastOrder = $this->orderFixture->getLastOrderForCustomer($email);
        $paymentId = $lastOrder->getPayment()->getId();
        $orderId   = $lastOrder->getId();

        $transaction = $this->transactionFixture->getByTransactionType(Transaction::TYPE_AUTH, $paymentId, $orderId);
        PHPUnit_Framework_Assert::assertSame('1', $transaction->getIsClosed());
    }

    /**
     * @Then there should be a closed capture for the last order for :email
     */
    public function thereShouldBeAClosedCaptureForTheLastOrderFor($email)
    {
        $transaction = $this->transactionFixture->getLastTransactionForLastOrder($email);

        PHPUnit_Framework_Assert::assertSame(Transaction::TYPE_CAPTURE, $transaction->getTxnType());
        PHPUnit_Framework_Assert::assertSame('1', $transaction->getIsClosed());
    }

    /**
     * @Then there should be a paid invoice for the last order for :email
     */
    public function thereShouldBeAPaidInvoiceForTheLastOrderFor($email)
    {
        $transaction = $this->transactionFixture->getLastTransactionForLastOrder($email);
        $invoice     = $this->invoiceFixture->getByTransactionId($transaction->getTxnId());

        PHPUnit_Framework_Assert::assertSame((string)Invoice::STATE_PAID, $invoice->getState());
    }

    /**
     * @Then there should be a credit memo for the value of the last invoice for :email
     */
    public function thereShouldBeACreditMemoForTheValueOfTheLastInvoiceFor($email)
    {
        $lastOrder      = $this->orderFixture->getLastOrderForCustomer($email);
        $lastInvoice    = $this->invoiceFixture->getLastForOrder($lastOrder->getId());
        $lastCreditMemo = $this->creditMemoFixture->getLastForOrder($lastOrder->getId());

        PHPUnit_Framework_Assert::assertSame($lastInvoice->getBaseGrandTotal(), $lastCreditMemo->getBaseGrandTotal());
    }

    /**
     * @Then the last order for :email should be in payment review
     */
    public function theLastOrderForShouldBeInPaymentReview($email)
    {
        $lastOrder = $this->orderFixture->getLastOrderForCustomer($email);

        PHPUnit_Framework_Assert::assertSame(Order::STATE_PAYMENT_REVIEW, $lastOrder->getState());
    }

    /**
     * @Then the last order for :email should have the processing state
     */
    public function theLastOrderForShouldHaveTheProcessingState($email)
    {
        $lastOrder = $this->orderFixture->getLastOrderForCustomer($email);

        PHPUnit_Framework_Assert::assertSame(Order::STATE_PROCESSING, $lastOrder->getState());
    }

    /**
     * @Then the last invoice for :email should be pending
     */
    public function theLastInvoiceForShouldBePending($email)
    {
        $transaction = $this->transactionFixture->getLastTransactionForLastOrder($email);
        $invoice     = $this->invoiceFixture->getByTransactionId($transaction->getTxnId());

        PHPUnit_Framework_Assert::assertSame((string)Invoice::STATE_OPEN, $invoice->getState());
    }

    /**
     * @Then the last capture transaction for :email should be open
     */
    public function theLastCaptureTransactionForShouldBeOpen($email)
    {
        $transaction = $this->transactionFixture->getLastTransactionForLastOrder($email);

        PHPUnit_Framework_Assert::assertSame(Transaction::TYPE_CAPTURE, $transaction->getTxnType());
        PHPUnit_Framework_Assert::assertSame('0', $transaction->getIsClosed());
    }

    /**
     * @Then the last invoice for :email should be cancelled
     */
    public function theLastInvoiceForShouldBeCancelled($email)
    {
        $transaction = $this->transactionFixture->getLastTransactionForLastOrder($email);
        $invoice     = $this->invoiceFixture->getByTransactionId($transaction->getTxnId());

        PHPUnit_Framework_Assert::assertSame((string)Invoice::STATE_CANCELED, $invoice->getState());
    }

    /**
     * @Then the last order for :email should be on hold
     */
    public function theLastOrderForShouldBeOnHold($email)
    {
        $lastOrder = $this->orderFixture->getLastOrderForCustomer($email);

        PHPUnit_Framework_Assert::assertSame(Order::STATE_HOLDED, $lastOrder->getState());
    }

    /**
     * @Then there should be an admin notification that the last refund for :email  was declined
     */
    public function thereShouldBeAnAdminNotificationThatTheLastRefundForWasDeclined($email)
    {
        $order       = $this->orderFixture->getLastOrderForCustomer($email);
        $transaction = $this->transactionFixture->getLastTransactionForLastOrder($email);

        $notification = $this->adminNotificationFixture->getLatestNotification();

        PHPUnit_Framework_Assert::assertSame(
            'Refund ID '
            . $transaction->getTxnId()
            . ' for Order ID '
            . $order->getId()
            . ' has been declined by Amazon Pay.',
            $notification->getDescription()
        );
    }
}