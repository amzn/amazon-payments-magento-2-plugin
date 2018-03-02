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

use SensioLabs\Behat\PageObjectExtension\PageObject\Element;

class PaymentMethods extends Element
{
    private $selector = '.payment-methods';
    
    public function hasMethods()
    {
        try {
            return (null !== $this->find('css', 'input[name="payment[method]"]'));
        } catch (\Exception $e) {
            return false;
        }
    }
}