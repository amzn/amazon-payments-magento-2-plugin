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
use Page\Store\Basket;
use Page\Store\Product;
use PHPUnit_Framework_Assert;

class ProductContext implements SnippetAcceptingContext
{
    /**
     * @var Product
     */
    private $productPage;

    /**
     * @var Basket
     */
    private $basketPage;

    /**
     * @param Product $productPage
     * @param Basket $basketPage
     */
    public function __construct(Product $productPage, Basket $basketPage)
    {
        $this->productPage = $productPage;
        $this->basketPage = $basketPage;
    }

    /**
     * @Given I go to the Product Page of product SKU :productSku
     */
    public function iGoToTheProductPageOfProductSku($productSku)
    {
        $this->productPage->openWithProductSku($productSku);
    }

    /**
     * @Then the PWA button should not be visible
     */
    public function thePWAButtonShouldNotBeVisible()
    {
        PHPUnit_Framework_Assert::assertFalse($this->productPage->pwaButtonIsVisibleNoWait());
    }

    /**
     * @Then the PWA button should be visible
     */
    public function thePWAButtonShouldBeVisible()
    {
        PHPUnit_Framework_Assert::assertTrue($this->productPage->pwaButtonIsVisible());
    }
}
