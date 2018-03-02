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
use Page\Store\Checkout;
use PHPUnit_Framework_Assert;

class AmazonContext implements SnippetAcceptingContext
{
    /**
     * @var Checkout
     */
    private $checkoutPage;

    /**
     * @var AmazonOrderFixture
     */
    private $amazonOrderFixture;

    public function __construct(Checkout $checkoutPage)
    {
        $this->checkoutPage       = $checkoutPage;
        $this->amazonOrderFixture = new AmazonOrderFixture;
    }

    /**
     * @Then my amazon order should be cancelled
     */
    public function myAmazonOrderShouldBeCancelled()
    {
        $orderRef   = $this->checkoutPage->getAmazonOrderRef();
        $orderState = $this->amazonOrderFixture->getState($orderRef);

        PHPUnit_Framework_Assert::assertSame('Canceled', $orderState);
    }
}