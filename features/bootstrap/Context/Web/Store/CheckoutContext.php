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
use Page\Store\Checkout;
use PHPUnit_Framework_Assert;

class CheckoutContext implements SnippetAcceptingContext
{
    /**
     * @var Checkout
     */
    private $checkoutPage;

    public function __construct(Checkout $checkoutPage)
    {
        $this->checkoutPage = $checkoutPage;
    }
    
    /**
     * @Given I go to the checkout
     */
    public function iGoToTheCheckout()
    {
        $this->checkoutPage->open();
    }

    /**
     * @Given I go to billing
     */
    public function iGoToBilling()
    {
        $this->checkoutPage->goToBilling();
    }
    
    /**
     * @When I revert to standard checkout
     */
    public function iRevertToStandardCheckout()
    {
        $this->checkoutPage->revertToStandardCheckout();
    }

    /**
     * @Then I do not see a pay with amazon button
     */
    public function iDoNotSeeAPayWithAmazonButton()
    {
        $hasPwa = $this->checkoutPage->hasPayWithAmazonButton();
        PHPUnit_Framework_Assert::assertFalse($hasPwa);
    }

    /**
     * @When I place my order
     */
    public function iPlaceMyOrder()
    {
        $this->checkoutPage->submitOrder();
    }

    /**
     * @Then I should be logged out of amazon
     */
    public function iShouldBeLoggedOutOfAmazon()
    {
        $loggedIn = $this->checkoutPage->isLoggedIn();
        PHPUnit_Framework_Assert::assertFalse($loggedIn);
    }
}