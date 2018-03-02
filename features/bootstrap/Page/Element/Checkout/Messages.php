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
namespace Page\Element\Checkout;

use PHPUnit_Framework_Assert;
use SensioLabs\Behat\PageObjectExtension\PageObject\Element;

class Messages extends Element
{
    private $selector = '.messages';

    public function hasHardDeclineError()
    {
        try {
            $element    = $this->find('css', '.message-error div');
            $constraint = PHPUnit_Framework_Assert::stringContains(
                'Unfortunately it is not possible to Pay with Amazon for this order. Please choose another payment method.',
                false
            );

            if ( ! $element) {
                return false;
            }

            PHPUnit_Framework_Assert::assertThat($element->getText(), $constraint);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function hasSoftDeclineError()
    {
        try {
            $element    = $this->find('css', '.message-error div');
            $constraint = PHPUnit_Framework_Assert::stringContains(
                'There has been a problem with the selected payment method on your Amazon account. Please choose another one.',
                false
            );

            if ( ! $element) {
                return false;
            }

            PHPUnit_Framework_Assert::assertThat($element->getText(), $constraint);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}