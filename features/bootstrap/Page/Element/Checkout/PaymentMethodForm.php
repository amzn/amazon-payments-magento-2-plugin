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

use Behat\Mink\Element\NodeElement;
use Page\Element\ElementHelper;
use SensioLabs\Behat\PageObjectExtension\PageObject\Element;

class PaymentMethodForm extends Element
{
    use ElementHelper;

    private $selector = 'form#co-payment-form';

    /**
     * @param string $paymentMethodCode e.g. "checkmo"
     * @param bool $strict
     * @throws \Exception
     */
    public function selectPaymentMethodByCode($paymentMethodCode, $strict = true)
    {
        /** @var NodeElement[] $paymentMethodRadios */
        $paymentMethodRadios = $this->findAll('css', 'input[name="payment[method]"]');

        foreach ($paymentMethodRadios as $paymentMethodRadio) {
            if ($paymentMethodRadio->getAttribute('value') === $paymentMethodCode) {
                $paymentMethodRadio->click();
                return;
            }
        }

        if ($strict) {
            throw new \Exception("Payment method with code $paymentMethodCode was not found");
        }
    }
}
