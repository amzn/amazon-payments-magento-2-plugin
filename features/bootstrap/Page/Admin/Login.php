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
namespace Page\Admin;

use Page\PageTrait;
use SensioLabs\Behat\PageObjectExtension\PageObject\Page;

class Login extends Page
{
    use PageTrait;

    private $path = '/admin/admin/';

    private $elements
        = [
            'login' => ['css' => '.action-login'],
        ];

    public function loginAdmin($email, $password)
    {
        $this->fillField('login[username]', $email);
        $this->fillField('login[password]', $password);
        $this->clickElement('login');
    }
}