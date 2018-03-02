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

use Page\Element\Checkout\PaymentMethodForm;
use Page\Element\Checkout\ShippingAddressForm;
use Page\PageTrait;
use SensioLabs\Behat\PageObjectExtension\PageObject\Page;

class Checkout extends Page
{
    use PageTrait;

    private $elements
        = [
            'shipping-widget'            => ['css' => '#addressBookWidgetDiv iframe'],
            'payment-widget'             => ['css' => '#walletWidgetDiv iframe'],
            'first-amazon-address'       => ['css' => '.address-list li:nth-of-type(1) a'],
            'first-amazon-payment'       => ['css' => '.payment-list li:nth-of-type(1) a'],
            'second-amazon-payment'      => ['css' => '.payment-list li:nth-of-type(2) a'],
            'go-to-billing'              => ['css' => 'button.continue.primary'],
            'first-shipping-method'      => ['css' => 'input[name="shipping_method"]:nth-of-type(1)'],
            'billing-address'            => ['css' => '.amazon-billing-address'],
            'full-screen-loader'         => ['css' => '.loading-mask'],
            'shipping-loader'            => ['css' => '.checkout-shipping-method._block-content-loading'],
            'revert-checkout'            => ['css' => '.revert-checkout'],
            'shipping-form'              => ['css' => '#co-shipping-form'],
            'pay-with-amazon'            => ['css' => '#OffAmazonPaymentsWidgets0'],
            'submit-order'               => ['css' => '._active button.checkout.primary'],
            'customer-email-input'       => ['css' => 'input#customer-email'],
            'error-messages-container'   => ['css' => 'div#checkout > div[data-role=checkout-messages]'],
        ];

    private $path = '/checkout/';

    public function provideShippingAddress()
    {
        $this->getShippingForm()
            ->withFirstName('John')
            ->withLastName('Doe')
            ->withAddressLines(['419 Kings Row', 'Spruce Tree Cottage'])
            ->withCity('Manchester')
            ->withCountry('GB')
            ->withPostCode('M13 9PL')
            ->withPhoneNumber('+447774443333');

        $this->waitUntilElementDisappear('shipping-loader');
    }

    public function selectFirstAmazonShippingAddress()
    {
        $iframe = $this->getElementWithWait('shipping-widget');
        $this->getDriver()->switchToIFrame($iframe->getAttribute('name'));
        $this->clickElement('first-amazon-address');
        $this->getDriver()->switchToIFrame(null);
    }

    public function selectFirstAmazonPaymentMethod()
    {
        $iframe = $this->getElementWithWait('payment-widget');
        $this->getDriver()->switchToIFrame($iframe->getAttribute('name'));
        $this->clickElement('first-amazon-payment');
        $this->getDriver()->switchToIFrame(null);
        $this->waitForAjaxRequestsToComplete();
        $this->waitUntilElementDisappear('full-screen-loader');
    }

    public function selectAlternativeAmazonPaymentMethod()
    {
        $iframe = $this->getElementWithWait('payment-widget');
        $this->getDriver()->switchToIFrame($iframe->getAttribute('name'));
        $this->clickElement('second-amazon-payment');
        $this->getDriver()->switchToIFrame(null);
        $this->waitForCondition('true === false', 1000);
        $this->waitForAjaxRequestsToComplete();
        $this->waitUntilElementDisappear('full-screen-loader');
    }

    public function selectDefaultShippingMethod()
    {
        $this->waitForCondition('true === false', 1000);
        $this->waitForAjaxRequestsToComplete();
        $this->waitUntilElementDisappear('shipping-loader');

        $defaultShippingMethod = $this->getElementWithWait('first-shipping-method');
        if ( ! $defaultShippingMethod->isChecked()) {
            $defaultShippingMethod->click();
        }
    }

    public function goToBilling()
    {
        $this->waitForCondition('true === false', 1000);
        $this->waitForAjaxRequestsToComplete();
        $this->waitUntilElementDisappear('full-screen-loader');
        $this->clickElement('go-to-billing');
        $this->waitUntilElementDisappear('full-screen-loader');

    }

    public function submitOrder()
    {
        $this->waitForCondition('true === false', 1000);
        $this->waitForAjaxRequestsToComplete();
        $this->waitUntilElementDisappear('full-screen-loader');
        $this->clickElement('submit-order');
        $this->waitUntilElementDisappear('full-screen-loader');
        $this->waitForPageLoad();
    }

    public function getBillingAddress()
    {
        $this->waitUntilElementDisappear('full-screen-loader');
        return $this->getElementText('billing-address');
    }

    public function revertToStandardCheckout()
    {
        $this->clickElement('revert-checkout');
    }

    public function hasStandardShippingForm()
    {
        try {
            $element = $this->getElementWithWait('shipping-form');
            return $element->isVisible();
        } catch (\Exception $e) {
            return false;
        }
    }

    public function hasPayWithAmazonButton()
    {
        try {
            $element = $this->getElementWithWait('pay-with-amazon', 30000);
            return $element->isVisible();
        } catch (\Exception $e) {
            return false;
        }
    }

    public function hasShippingWidget()
    {
        try {
            $element = $this->getElementWithWait('shipping-widget');
            return $element->isVisible();
        } catch (\Exception $e) {
            return false;
        }
    }

    public function hasPaymentWidget()
    {
        try {
            $element = $this->getElement('payment-widget');
            return $element->isVisible();
        } catch (\Exception $e) {
            return false;
        }
    }

    public function isLoggedIn()
    {
        try {
            return $this->getDriver()->evaluateScript(
                'require(\'uiRegistry\').get(\'checkout.steps.shipping-step.shippingAddress.before-form.amazon-widget-address\').isAmazonAccountLoggedIn();'
            );
        } catch (\Exception $e) {
            return false;
        }
    }

    public function getAmazonOrderRef()
    {
        $orderRef = $this->getDriver()->evaluateScript(
            'require(\'uiRegistry\').get(\'checkout.steps.shipping-step.shippingAddress.before-form.amazon-widget-address\').getAmazonOrderReference();'
        );

        if ( ! strlen($orderRef)) {
            throw new \Exception('Could not locate amazon order reference');
        }

        return $orderRef;
    }

    public function getAddressConsentToken()
    {
        $addressConsentToken = $this->getDriver()->evaluateScript(
            'require(\'uiRegistry\').get(\'checkout.steps.shipping-step.shippingAddress.before-form.amazon-widget-address\').getAddressConsentToken();'
        );

        if ( ! strlen($addressConsentToken)) {
            throw new \Exception('Could not locate address consent token');
        }

        return $addressConsentToken;
    }

    public function selectSimulation($simulation)
    {
        $this->waitUntilElementDisappear('full-screen-loader');
        $this->getElement('Checkout\SandboxSimulation')->selectSimulation($simulation);
    }

    /**
     * @return ShippingAddressForm
     */
    public function getShippingForm()
    {
        return $this->getElement('Checkout\ShippingAddressForm');
    }

    /**
     * @return PaymentMethodForm
     */
    public function getPaymentMethodForm()
    {
        return $this->getElement('Checkout\PaymentMethodForm');
    }

    /**
     * @param string $email
     *
     * @throws \Exception
     */
    public function setCustomerEmail($email)
    {
        $input = $this->getElementWithWait('customer-email-input');

        if ( ! $input) {
            throw new \Exception('No customer email input was found.');
        }

        $input->setValue((string)$email);
    }

    /**
     * @return bool
     */
    public function isErrorMessageContainerVisible()
    {
        $errorContainer = $this->getElement('error-messages-container');
        return $errorContainer->isVisible();
    }
}
