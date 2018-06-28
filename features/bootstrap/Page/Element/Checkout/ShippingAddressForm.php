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

use Page\Element\ElementHelper;
use SensioLabs\Behat\PageObjectExtension\PageObject\Element;

class ShippingAddressForm extends Element
{
    use ElementHelper;

    private $selector = 'form#co-shipping-form';

    /**
     * @param string $firstName
     * @return $this
     */
    public function withFirstName($firstName)
    {
        $this->findElement('input[name="firstname"]')->setValue((string) $firstName);
        return $this;
    }

    /**
     * @param string $lastName
     * @return $this
     */
    public function withLastName($lastName)
    {
        $this->findElement('input[name="lastname"]')->setValue((string) $lastName);
        return $this;
    }

    /**
     * @param string $company
     * @return $this
     */
    public function withCompany($company)
    {
        $this->findElement('input[name="company"]')->setValue((string) $company);
        return $this;
    }

    /**
     * @param array $addressLines
     * @param bool $strict
     * @return $this
     */
    public function withAddressLines(array $addressLines, $strict = false)
    {
        // reset to numeric keys
        $addressLines = array_values($addressLines);

        foreach ($addressLines as $lineNumber => $addressLine) {
            $addressLineElement = $this->findElement(sprintf('input[name="street[%d]"]', $lineNumber), $strict);

            if ($addressLineElement === null) {
                continue;
            }

            $addressLineElement->setValue((string) $addressLine);
        }

        return $this;
    }

    /**
     * @param string $city
     * @return $this
     */
    public function withCity($city)
    {
        $this->findElement('input[name="city"]')->setValue((string) $city);
        return $this;
    }

    /**
     * @param string $state e.g. "Washington", "Texas"
     * @return $this
     * @throws \Exception
     */
    public function withState($state)
    {
        if ($regionTextElement = $this->findElement('input[name="region_id"]', false)) {
            $regionTextElement->setValue((string) $state);
        } elseif ($regionSelectElement = $this->findElement('select[name="region_id"]', false)) {
            $regionSelectElement->selectOption((string) $state);
        } else {
            throw new \Exception("Could not find State element.");
        }

        return $this;
    }

    /**
     * @param string $postCode
     * @return $this
     */
    public function withPostCode($postCode)
    {
        $this->findElement('input[name="postcode"]')->setValue((string) $postCode);
        return $this;
    }

    /**
     * @param string $country e.g. GB, US, DE
     * @return $this
     */
    public function withCountry($country)
    {
        $country = strtoupper($country);
        $this->findElement('select[name="country_id"]')->selectOption($country);
        return $this;
    }

    /**
     * @param string $phoneNumber
     * @return $this
     */
    public function withPhoneNumber($phoneNumber)
    {
        $this->findElement('input[name="telephone"]')->setValue((string) $phoneNumber);
        return $this;
    }
}
