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
use Fixtures\Currency as CurrencyFixture;
use Fixtures\Product as ProductFixture;
use Magento\Catalog\Api\Data\ProductInterface;
use Page\Element\CurrencySwitcher;
use Page\Store\Basket;
use Page\Store\Home;
use Page\Store\Product;
use PHPUnit_Framework_Assert;

class BasketContext implements SnippetAcceptingContext
{
    /**
     * @var Product
     */
    private $productPage;

    /**
     * @var CurrencySwitcher
     */
    private $currencySwitcherElement;

    /**
     * @var Home
     */
    private $homePage;

    /**
     * @var CurrencyFixture
     */
    private $currencyFixture;

    /**
     * @var Basket
     */
    private $basketPage;

    /**
     * @var ProductFixture
     */
    private $productFixture;

    /**
     * @param Product          $productPage
     * @param CurrencySwitcher $currencySwitcherElement
     * @param Home             $homePage
     * @param Basket           $basketPage
     */
    public function __construct(
        Product $productPage,
        CurrencySwitcher $currencySwitcherElement,
        Home $homePage,
        Basket $basketPage
    ) {
        $this->productPage             = $productPage;
        $this->currencySwitcherElement = $currencySwitcherElement;
        $this->homePage                = $homePage;
        $this->currencyFixture         = new CurrencyFixture;
        $this->basketPage              = $basketPage;
        $this->productFixture          = new ProductFixture;
    }

    /**
     * @Given there is a valid product in my basket
     */
    public function thereIsAValidProductInMyBasket()
    {
        $product = $this->productFixture->create([
            ProductInterface::SKU  => 'test-product-' . time(),
            ProductInterface::NAME => 'Test Product ' . time()
        ]);
        $this->productPage->openWithProductId($product->getId());
        $this->productPage->addToBasket();
    }

    /**
     * @Given Product SKU :productSku is added to the basket
     */
    public function productSKUIsAddedToTheBasket($productSku)
    {
        $this->productPage->openWithProductSku($productSku);
        $this->productPage->addToBasket();
    }

    /**
     * @Given I go to my basket
     */
    public function iGoToMyBasket()
    {
        $this->basketPage->open();
    }

    /**
     * @Then I see a login with amazon button on the basket page
     */
    public function iSeeALoginWithAmazonButtonOnTheBasketPage()
    {
        $hasLwa = $this->basketPage->hasLoginWithAmazonButton();
        PHPUnit_Framework_Assert::assertTrue($hasLwa);
    }

    /**
     * @Given I want to pay using an unsupported currency
     */
    public function iWantToPayUsingAnUnsupportedCurrency()
    {
        $rates = [
            'GBP' => [
                'CHF' => '1.41',
                'GBP' => '1.00'
            ]
        ];

        $this->currencyFixture->saveRates($rates);

        $this->homePage->open();
        $this->currencySwitcherElement->selectCurrency('CHF');
    }

    /**
     * @Then I should be redirected to the Basket
     */
    public function iShouldBeRedirectedToTheBasket()
    {
        $this->basketPage->isOpen();
    }

    /**
     * @Then the minicart should not display the PWA button
     */
    public function theMinicartShouldNotDisplayThePWAButton()
    {
        /** @var \Page\Element\Minicart $minicart */
        $minicart = $this->homePage->getElement('Minicart');

        $minicart->collapseMinicartContent();
        $minicart->pwaButtonIsNotVisible();
    }

    /**
     * @Then the Basket page should not display the PWA button
     */
    public function theBasketPageShouldNotDisplayThePWAButton()
    {
        PHPUnit_Framework_Assert::assertFalse($this->basketPage->pwaButtonIsVisibleNoWait());
    }
}
