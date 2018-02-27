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
namespace Page\Element;

use SensioLabs\Behat\PageObjectExtension\PageObject\Element;
use Behat\Mink\Exception\ElementNotFoundException;

class CurrencySwitcher extends Element
{
    private $selector = '#switcher-currency';

    public function selectCurrency($code)
    {
        $switcher = $this->find('css', '#switcher-currency-trigger');

        if ($switcher) {
            $switcher->click();
        } else {
            throw new ElementNotFoundException($this->getSession());
        }

        $currency = $this->find('css', 'li.currency-' . $code . ' a');

        if ($currency) {
            $currency->click();
        } else {
            throw new ElementNotFoundException($this->getSession());
        }
    }
}