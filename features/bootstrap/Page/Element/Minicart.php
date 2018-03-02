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

use PHPUnit_Framework_Assert;
use SensioLabs\Behat\PageObjectExtension\PageObject\Element;

class Minicart extends Element
{
    private $selector = 'div[data-block="minicart"]';

    public function collapseMinicartContent()
    {
        $this->click();
    }

    /**
     * @return bool
     */
    public function pwaButtonIsNotVisible()
    {
        PHPUnit_Framework_Assert::assertNull($this->find('css', '#OffAmazonPaymentsWidgets0'));
    }
}
