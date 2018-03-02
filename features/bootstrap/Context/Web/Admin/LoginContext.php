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
namespace Context\Web\Admin;

use Behat\Behat\Context\SnippetAcceptingContext;
use Fixtures\AdminUser as AdminUserFixture;
use Page\Admin\Login;
use PHPUnit_Framework_Assert;

class LoginContext implements SnippetAcceptingContext
{
    /**
     * @var AdminUserFixture
     */
    private $adminUserFixture;

    /**
     * @var Login
     */
    private $loginPage;

    public function __construct(Login $loginPage)
    {
        $this->loginPage        = $loginPage;
        $this->adminUserFixture = new AdminUserFixture;
    }

    /**
     * @Given I am logged into admin
     */
    public function iAmLoggedIntoAdmin()
    {
        $this->adminUserFixture->generate();

        $this->loginPage->open();

        $this->loginPage->loginAdmin(
            $this->adminUserFixture->getDefaultUsername(),
            $this->adminUserFixture->getDefaultPassword()
        );
    }
}