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
namespace Amazon\Core\Domain;

class AmazonAddress
{
    /**
     * @var AmazonName
     */
    protected $name;

    /**
     * @var array
     */
    protected $lines;

    /**
     * @var string
     */
    protected $city;

    /**
     * @var string|null
     */
    protected $state;

    /**
     * @var string
     */
    protected $postCode;

    /**
     * @var string
     */
    protected $countryCode;

    /**
     * @var string
     */
    protected $telephone;

    /**
     * @var string
     */
    protected $company = '';

    /**
     * @param array $address
     * @param AmazonNameFactory $addressNameFactory
     */
    public function __construct(array $address, AmazonNameFactory $addressNameFactory)
    {
        $this->name = $addressNameFactory->create(['name' => $address['Name']]);

        $this->lines = [];

        for ($i = 1; $i <= 3; $i++) {
            $key = 'AddressLine' . $i;

            if (isset($address[$key])) {
                if (empty($address[$key])) {
                    $this->lines[$i] = '';
                } else {
                    $this->lines[$i] = $address[$key];
                }
            }
        }

        $this->city        = $address['City'];
        $this->postCode    = $address['PostalCode'];
        $this->countryCode = $address['CountryCode'];

        if (isset($address['Phone'])) {
            $this->telephone = $address['Phone'];
        }

        if (isset($address['StateOrRegion'])) {
            $this->state = $address['StateOrRegion'];
        }
    }

    /**
     * Get first name
     *
     * @return string
     */
    public function getFirstName()
    {
        return $this->name->getFirstName();
    }

    /**
     * Get last name
     *
     * @return string
     */
    public function getLastName()
    {
        return $this->name->getLastName();
    }

    /**
     * @return array
     */
    public function getLines()
    {
        return $this->lines;
    }

    /**
     * @param int $lineNumber
     * @return null|string
     */
    public function getLine($lineNumber)
    {
        if (isset($this->lines[$lineNumber])) {
            return $this->lines[$lineNumber];
        }
        return null;
    }

    /**
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @return string
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @return string
     */
    public function getPostCode()
    {
        return $this->postCode;
    }

    /**
     * @return string
     */
    public function getCountryCode()
    {
        return $this->countryCode;
    }

    /**
     * @return string
     */
    public function getTelephone()
    {
        return $this->telephone;
    }

    /**
     * @return string
     */
    public function getCompany()
    {
        return $this->company;
    }
}
