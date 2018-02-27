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
use Fixtures\Customer as CustomerFixture;
use Page\Store\Basket;
use Page\Store\CustomerSection;
use Page\Store\Login;
use Page\Store\Logout;
use Page\Store\Product;
use Page\Store\ValidatePassword;
use PHPUnit_Framework_Assert;

class LoginContext implements SnippetAcceptingContext
{
    /**
     * @var Login
     */
    private $loginPage;

    /**
     * @var Basket
     */
    private $basketPage;

    private $amazonPassword = 'eZhV5fyirWImL7OzIJ9t';

    /**
     * @var CustomerFixture
     */
    private $customerFixture;

    /**
     * @var Product
     */
    private $productPage;

    /**
     * @var CustomerSection
     */
    private $customerSectionPage;

    /**
     * @var ValidatePassword
     */
    private $validatePasswordPage;

    /**
     * @var Logout
     */
    private $logoutPage;

    /**
     * @param Login   $loginPage
     * @param Basket  $basketPage
     * @param Product $productPage
     */
    public function __construct(
        Login $loginPage,
        Logout $logoutPage,
        Basket $basketPage,
        Product $productPage,
        CustomerSection $customerSectionPage,
        ValidatePassword $validatePasswordPage
    ) {
        $this->customerFixture      = new CustomerFixture;
        $this->loginPage            = $loginPage;
        $this->logoutPage           = $logoutPage;
        $this->basketPage           = $basketPage;
        $this->productPage          = $productPage;
        $this->customerSectionPage  = $customerSectionPage;
        $this->validatePasswordPage = $validatePasswordPage;
    }

    /**
     * @Given I go to login
     */
    public function iGoToLogin()
    {
        $this->loginPage->open();
    }

    /**
     * @Then I see a login with amazon button on the login page
     */
    public function iSeeALoginWithAmazonButtonOnTheLoginPage()
    {
        $hasLwa = $this->loginPage->hasLoginWithAmazonButton();
        PHPUnit_Framework_Assert::assertTrue($hasLwa);
    }

    /**
     * @Given :email is logged in
     */
    public function isLoggedIn($email)
    {
        $this->loginPage->open();
        $this->loginPage->loginCustomer($email, $this->customerFixture->getDefaultPassword());
    }

    /**
     * @Given I login with amazon as :email
     */
    public function iLoginWithAmazonAs($email)
    {
        $this->loginPage->open();
        $this->loginPage->loginAmazonCustomer($email, $this->getAmazonPassword());
        $this->customerFixture->track($email);
    }

    /**
     * @Given I login with Amazon as :email on product page
     */
    public function iLoginWithAmazonAsOnProductPage($email)
    {
        $this->productPage->openWithProductId(1);
        $this->productPage->loginAmazonCustomer($email, $this->getAmazonPassword());
        $this->customerFixture->track($email);
    }

    /**
     * @Given I login with amazon on the basket page as :email
     */
    public function iLoginWithAmazonOnTheBasketPageAs($email)
    {
        $this->basketPage->open();
        $this->basketPage->loginAmazonCustomer($email, $this->getAmazonPassword());
        $this->customerFixture->track($email);
    }

    /**
     * @Then I should be asked to confirm my password
     */
    public function iShouldBeAskedToConfirmMyPassword()
    {
        $this->validatePasswordPage->isOpen();
    }

    /**
     * @When I confirm my password
     */
    public function iConfirmMyPassword()
    {
        $this->validatePasswordPage->submitWithPassword($this->customerFixture->getDefaultPassword());
    }

    /**
     * @Then I should be logged in as a customer
     */
    public function iShouldBeLoggedInAsACustomer()
    {
        $loggedIn = $this->customerSectionPage->isLoggedIn();
        PHPUnit_Framework_Assert::assertTrue($loggedIn);
    }

    /**
     * @Then I should not be logged in as a customer
     */
    public function iShouldNotBeLoggedInAsACustomer()
    {
        $loggedIn = $this->customerSectionPage->isLoggedIn();
        PHPUnit_Framework_Assert::assertFalse($loggedIn);
    }

    /**
     * @Given there is a customer :email which is linked to amazon
     */
    public function thereIsACustomerWhichIsLinkedToAmazon($email)
    {
        $this->loginPage->open();
        $this->loginPage->loginAmazonCustomer($email, $this->getAmazonPassword());
        $this->customerFixture->track($email);
        $this->logoutPage->logout();

        $loggedIn = $this->customerSectionPage->isLoggedIn();
        PHPUnit_Framework_Assert::assertFalse($loggedIn);
    }

    /**
     * @AfterScenario
     */
    public function logoutUser()
    {
        $this->logoutPage->open();
    }

    protected function getAmazonPassword()
    {
        return $this->amazonPassword;
    }
}
