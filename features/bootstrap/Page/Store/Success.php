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

use Page\PageTrait;
use SensioLabs\Behat\PageObjectExtension\PageObject\Page;

class Success extends Page
{
    use PageTrait;

    private $path = '/checkout/onepage/success';

    private $elements = [
        'create-account-btn' => ['css' => 'input[type="submit"]'],
    ];

    /**
     * @return bool
     */
    public function createAccountButtonIsVisible()
    {
        $createAccount = $this->getElementWithWait('create-account-btn', 30000);
        return ($createAccount !== null) && $createAccount->isVisible();
    }

    /**
     * @throws \Exception
     */
    public function clickCreateAccount()
    {
        if (!$this->createAccountButtonIsVisible()) {
            throw new \Exception('Create account button is not existent or visible');
        }

        $this->getElement('create-account-btn')->click();
        $this->waitForAjaxRequestsToComplete();
    }
}
