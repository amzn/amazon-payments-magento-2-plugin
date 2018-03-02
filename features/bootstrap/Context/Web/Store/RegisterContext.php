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
use Page\Store\Register;
use PHPUnit_Framework_Assert;

class RegisterContext implements SnippetAcceptingContext
{
    /**
     * @var Register
     */
    private $registerPage;

    public function __construct(Register $registerPage)
    {
        $this->registerPage = $registerPage;
    }

    /**
     * @Given I go to register
     */
    public function iGoToRegister()
    {
        $this->registerPage->open();
    }

    /**
     * @Then I see a login with amazon button on the registration page
     */
    public function iSeeALoginWithAmazonButtonOnTheRegistrationPage()
    {
        $hasLwa = $this->registerPage->hasLoginWithAmazonButton();
        PHPUnit_Framework_Assert::assertTrue($hasLwa);
    }
}