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
use Fixtures\Basket as BasketFixture;
use Fixtures\Customer as CustomerFixture;
use Page\Store\Checkout;
use PHPUnit_Framework_Assert;

class ShippingContext implements SnippetAcceptingContext
{
    /**
     * @var Checkout
     */
    private $checkoutPage;

    /**
     * @var CustomerFixture
     */
    private $customerFixture;

    /**
     * @var BasketFixture
     */
    private $basketFixture;

    /**
     * @var AmazonOrderFixture
     */
    private $amazonOrderFixture;

    public function __construct(Checkout $checkoutPage)
    {
        $this->checkoutPage       = $checkoutPage;
        $this->customerFixture    = new CustomerFixture;
        $this->basketFixture      = new BasketFixture;
        $this->amazonOrderFixture = new AmazonOrderFixture;
    }

    /**
     * @Given I select a shipping address from my amazon account
     */
    public function iSelectAShippingAddressFromMyAmazonAccount()
    {
        $this->checkoutPage->selectFirstAmazonShippingAddress();
    }

    /**
     * @Given I select a valid shipping method
     */
    public function iSelectAValidShippingMethod()
    {
        $this->checkoutPage->selectDefaultShippingMethod();
    }

    /**
     * @Then the amazon shipping widget should be displayed
     */
    public function theAmazonShippingWidgetShouldBeDisplayed()
    {
        $hasShippingWidget = $this->checkoutPage->hasShippingWidget();
        PHPUnit_Framework_Assert::assertTrue($hasShippingWidget);
    }

    /**
     * @Then the amazon shipping widget should not be displayed
     */
    public function theAmazonShippingWidgetShouldNotBeDisplayed()
    {
        $hasShippingWidget = $this->checkoutPage->hasShippingWidget();
        PHPUnit_Framework_Assert::assertFalse($hasShippingWidget);
    }

    /**
     * @Then the standard shipping form should be displayed
     */
    public function theStandardShippingFormShouldBeDisplayed()
    {
        $hasShippingForm = $this->checkoutPage->hasStandardShippingForm();
        PHPUnit_Framework_Assert::assertTrue($hasShippingForm);
    }

    /**
     * @Then the standard shipping form should not be displayed
     */
    public function theStandardShippingFormShouldNotBeDisplayed()
    {
        $hasShippingForm = $this->checkoutPage->hasStandardShippingForm();
        PHPUnit_Framework_Assert::assertFalse($hasShippingForm);
    }


    /**
     * @Given I provide the :email email in the shipping form
     */
    public function iProvideTheEmailInTheShippingForm($email)
    {
        $this->checkoutPage->setCustomerEmail($email);
    }

    /**
     * @Then the current basket for :email should have my amazon shipping address
     */
    public function theCurrentBasketForShouldHaveMyAmazonShippingAddress($email)
    {
        $customer = $this->customerFixture->get($email);
        $basket   = $this->basketFixture->getActiveForCustomer($customer->getId());

        $orderRef            = $this->checkoutPage->getAmazonOrderRef();
        $addressConsentToken = $this->checkoutPage->getAddressConsentToken();

        $amazonShippingAddress = $this->amazonOrderFixture->getShippingAddress($orderRef, $addressConsentToken);
        $shippingAddress       = $basket->getShippingAddress()->exportCustomerAddress();

        $amazonShippingAddressData = $amazonShippingAddress->__toArray();
        $shippingAddressData       = array_intersect_key($shippingAddress->__toArray(), $amazonShippingAddressData);

        unset($shippingAddressData['company']);
        unset($amazonShippingAddressData['company']);

        asort($amazonShippingAddressData);
        asort($shippingAddressData);

        PHPUnit_Framework_Assert::assertSame($amazonShippingAddressData, $shippingAddressData);
    }

    /**
     * @Given I should see an error about the invalid address having a black listed term
     */
    public function iShouldSeeAnErrorAboutTheInvalidAddressHavingABlackListedTerm()
    {
        PHPUnit_Framework_Assert::assertTrue($this->checkoutPage->isErrorMessageContainerVisible());
    }
}
