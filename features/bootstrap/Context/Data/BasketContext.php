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
use Fixtures\Basket as BasketFixture;
use Fixtures\Customer as CustomerFixture;
use PHPUnit_Framework_Assert;

class BasketContext implements SnippetAcceptingContext
{
    /**
     * @var CustomerFixture
     */
    private $customerFixture;

    /**
     * @var BasketFixture
     */
    private $basketFixture;

    public function __construct()
    {
        $this->customerFixture = new CustomerFixture;
        $this->basketFixture   = new BasketFixture;
    }

    /**
     * @Then the basket for :email should not be linked to an amazon order
     */
    public function theBasketForShouldNotBeLinkedToAnAmazonOrder($email)
    {
        $customer = $this->customerFixture->get($email);
        $basket   = $this->basketFixture->getActiveForCustomer($customer->getId());

        PHPUnit_Framework_Assert::assertNull($basket->getExtensionAttributes()->getAmazonOrderReferenceId());
    }
}