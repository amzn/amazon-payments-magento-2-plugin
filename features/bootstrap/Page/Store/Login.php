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
namespace Page\Store;

use Page\AmazonLoginTrait;
use Page\PageTrait;
use SensioLabs\Behat\PageObjectExtension\PageObject\Page;

class Login extends Page
{
    use PageTrait, AmazonLoginTrait;

    private $path = '/customer/account/login/';

    private $elements
        = [
            'login'             => ['css' => '#send2'],
            'open-amazon-login' => ['css' => '#OffAmazonPaymentsWidgets0'],
            'amazon-login'      => ['css' => 'button']
        ];

    public function loginCustomer($email, $password)
    {
        $this->fillField('email', $email);
        $this->fillField('pass', $password);
        $this->getElement('login')->click();
    }
}