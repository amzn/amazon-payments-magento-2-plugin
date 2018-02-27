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
namespace Context\Web\Store;

use Behat\Behat\Context\SnippetAcceptingContext;
use Fixtures\AmazonOrder as AmazonOrderFixture;
use Fixtures\Customer as CustomerFixture;
use Fixtures\QuoteLink as QuoteLinkFixture;
use Page\Store\Checkout;
use Page\Store\Success;
use Fixtures\Order as OrderFixture;
use PHPUnit_Framework_Assert;

class CustomerContext implements SnippetAcceptingContext
{
    /**
     * @var Checkout
     */
    private $checkoutPage;

    /**
     * @var Success
     */
    private $successPage;

    /**
     * @var CustomerFixture
     */
    private $customerFixture;

    /**
     * @var OrderFixture
     */
    private $orderFixture;

    /**
     * @var AmazonOrderFixture
     */
    private $amazonOrderFixture;

    /**
     * @var QuoteLinkFixture
     */
    private $quoteLinkFixture;

    /**
     * CustomerContext constructor.
     *
     * @param Checkout $checkoutPage
     * @param Success  $successPage
     */
    public function __construct(Checkout $checkoutPage, Success $successPage)
    {
        $this->checkoutPage = $checkoutPage;
        $this->successPage  = $successPage;
        $this->customerFixture = new CustomerFixture;
        $this->orderFixture = new OrderFixture;
        $this->amazonOrderFixture = new AmazonOrderFixture;
        $this->quoteLinkFixture = new QuoteLinkFixture;

    }

    /**
     * @Then I can create a new Amazon account on the success page with email :email
     */
    public function iCanCreateANewAmazonAccountOnTheSuccessPageWithEmail($email)
    {
        $this->successPage->clickCreateAccount();
        $this->customerFixture->track($email);
    }

    /**
     * @Given the order for :email should be confirmed
     */
    public function theOrderForShouldBeConfirmed($email)
    {
        $order = $this->orderFixture->getLastOrderForCustomer($email);

        $orderRef = $order->getExtensionAttributes()->getAmazonOrderReferenceId();

        PHPUnit_Framework_Assert::assertNotEmpty($orderRef, 'Empty Amazon Order reference');
        $quoteLink = $this->quoteLinkFixture->getByColumnValue('amazon_order_reference_id', $orderRef);

        PHPUnit_Framework_Assert::assertNotEmpty(
            $quoteLink->getId(),
            "Quote Link with Amazon order reference $orderRef was not found"
        );

        PHPUnit_Framework_Assert::assertTrue($quoteLink->isConfirmed());

        $orderState = $this->amazonOrderFixture->getState($orderRef);

        PHPUnit_Framework_Assert::assertSame($orderState, 'Open');
    }
}
