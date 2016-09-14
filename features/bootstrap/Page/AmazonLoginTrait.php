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
namespace Page;

use Behat\Mink\Driver\DriverInterface;
use SensioLabs\Behat\PageObjectExtension\PageObject\Element;

trait AmazonLoginTrait
{
    /**
     * Returns element's driver.
     *
     * @return DriverInterface
     */
    abstract protected function getDriver();

    /**
     * @param string $name
     *
     * @return Element
     */
    abstract public function clickElement($elementName);
    
    abstract public function waitForPageLoad($maxWait = 60000);

    abstract public function fillField($locator, $value);

    public function loginAmazonCustomer($email, $password)
    {
        $this->waitForPageLoad();
        $currentWindow = $this->getDriver()->getWindowName();
        
        $this->clickElement('open-amazon-login');
        
        $this->getDriver()->switchToWindow('amazonloginpopup');

        $this->fillField('ap_email', $email);
        $this->fillField('ap_password', $password);
        $this->clickElement('amazon-login');

        $this->getDriver()->switchToWindow($currentWindow);

        $this->waitForPageLoad();
    }

    public function hasLoginWithAmazonButton()
    {
        try {
            $element = $this->getElementWithWait('open-amazon-login', 5000);
            return $element->isVisible();
        } catch (\Exception $e) {
            return false;
        }
    }
}