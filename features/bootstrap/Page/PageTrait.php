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
use SensioLabs\Behat\PageObjectExtension\PageObject\Exception\ElementNotFoundException;

trait PageTrait
{
    /**
     * @return DriverInterface
     */
    abstract protected function getDriver();

    /**
     * @param string $name
     *
     * @return Element
     */
    abstract public function getElement($name);

    public function waitForCondition($condition, $maxWait = 90000)
    {
        $this->getDriver()->wait($maxWait, $condition);
    }

    public function waitForPageLoad($maxWait = 90000)
    {
        $this->waitForCondition('(document.readyState == "complete") && (typeof window.jQuery == "function") && (jQuery.active == 0)', $maxWait);
    }

    public function waitForElement($elementName, $maxWait = 90000)
    {
        $visibilityCheck = $this->getElementVisibilityCheck($elementName);
        $this->waitForCondition("(typeof window.jQuery == 'function') && $visibilityCheck", $maxWait);
    }

    public function waitUntilElementDisappear($elementName, $maxWait = 180000)
    {
        $visibilityCheck = $this->getElementVisibilityCheck($elementName);
        $this->waitForCondition("(typeof window.jQuery == 'function') && !$visibilityCheck", $maxWait);
    }

    public function clickElement($elementName)
    {
        $element = $this->getElementWithWait($elementName);

        if ( ! $element) {
            throw new ElementNotFoundException;
        }

        $element->click();
    }

    public function getElementValue($elementName)
    {
        return $this->getElementWithWait($elementName)->getValue();
    }

    public function setElementValue($elementName, $value)
    {
        $this->getElementWithWait($elementName)->setValue($value);
    }

    public function getElementText($elementName)
    {
        return $this->getElementWithWait($elementName)->getText();
    }

    public function getElementWithWait($elementName, $waitTime = 120000)
    {
        $this->waitForElement($elementName, $waitTime);
        return $this->getElement($elementName);
    }

    public function getElementVisibilityCheck($elementName)
    {
        $visibilityCheck = 'true';

        if (isset($this->elements[$elementName]['css'])) {
            $elementFinder = $this->elements[$elementName]['css'];
            $visibilityCheck = "jQuery('$elementFinder').is(':visible')";
        }

        if (isset($this->elements[$elementName]['xpath'])) {
            $elementFinder = $this->elements[$elementName]['xpath'];
            $visibilityCheck = "jQuery(document.evaluate('$elementFinder', document, null, XPathResult.ANY_TYPE, null).iterateNext()).is(':visible')";
        }

        return $visibilityCheck;
    }

    public function isElementVisible($elementName)
    {
        $xpath = $this->getElement($elementName)->getXpath();
        return $this->getDriver()->isVisible($xpath);
    }

    public function waitForAjaxRequestsToComplete($maxWait = 90000)
    {
        $this->getDriver()->wait($maxWait, 'jQuery.active == 0');
    }
}