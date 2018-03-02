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
use Fixtures\AmazonOrder as AmazonOrderFixture;
use Fixtures\Customer as CustomerFixture;
use Fixtures\Order as OrderFixture;
use Fixtures\Transaction as TransactionFixture;
use PHPUnit_Framework_Assert;

class AmazonContext implements SnippetAcceptingContext
{
    /**
     * @var AmazonOrderFixture
     */
    private $amazonOrderFixture;

    /**
     * @var OrderFixture
     */
    private $orderFixture;

    /**
     * @var CustomerFixture
     */
    private $customerFixture;

    /**
     * @var TransactionFixture
     */
    private $transactionFixture;

    public function __construct()
    {
        $this->customerFixture    = new CustomerFixture;
        $this->orderFixture       = new OrderFixture;
        $this->amazonOrderFixture = new AmazonOrderFixture;
        $this->transactionFixture = new TransactionFixture;
    }

    /**
     * @Then amazon should have an open authorization for the last order for :email
     */
    public function amazonShouldHaveAnOpenAuthorizationForTheLastOrderFor($email)
    {
        $authorizationId    = $this->getLastTransactionIdForLastOrder($email);
        $authorizationState = $this->amazonOrderFixture->getAuthrorizationState($authorizationId);

        PHPUnit_Framework_Assert::assertSame('Open', $authorizationState);
    }

    /**
     * @Then amazon should have a complete capture for the last order for :email
     */
    public function amazonShouldHaveACompleteCaptureForTheLastOrderFor($email)
    {
        $captureId    = $this->getLastTransactionIdForLastOrder($email);
        $captureState = $this->amazonOrderFixture->getCaptureState($captureId);

        PHPUnit_Framework_Assert::assertSame('Completed', $captureState);
    }

    /**
     * @Then amazon should have a refund for the last invoice for :email
     */
    public function amazonShouldHaveARefundForheLastInvoiceFor($email)
    {
        $transaction = $this->transactionFixture->getLastTransactionForLastOrder($email);
        $refundId    = $transaction->getTxnId();
        $refundState = $this->amazonOrderFixture->getRefundState($refundId);

        PHPUnit_Framework_Assert::assertSame('Pending', $refundState);
    }

    protected function getLastTransactionIdForLastOrder($email)
    {
        $customer = $this->customerFixture->get($email);
        $orders   = $this->orderFixture->getForCustomer($customer);

        $lastOrder = current($orders->getItems());

        if ( ! $lastOrder) {
            throw new \Exception('Last order not found for ' . $email);
        }

        return $lastOrder->getPayment()->getLastTransId();
    }
}