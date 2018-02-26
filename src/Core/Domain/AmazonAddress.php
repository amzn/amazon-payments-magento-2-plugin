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

use Amazon\Core\Api\Data\AmazonAddressInterface;

class AmazonAddress implements AmazonAddressInterface
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var array
     */
    private $lines = [];

    /**
     * @var string
     */
    private $city;

    /**
     * @var string|null
     */
    private $state;

    /**
     * @var string
     */
    private $postCode;

    /**
     * @var string
     */
    private $countryCode;

    /**
     * @var string
     */
    private $telephone;

    /**
     * @var string
     */
    private $company = '';

    /**
     * @var AmazonName
     */
    private $amazonName;

    /**
     * @var AmazonNameFactory
     */
    private $amazonNameFactory;

    /**
     * @param AmazonNameFactory $addressNameFactory
     */
    public function __construct(AmazonNameFactory $amazonNameFactory)
    {
        $this->amazonNameFactory = $amazonNameFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getFirstName()
    {
        return $this->getAmazonName()->getFirstName();
    }

    /**
     * {@inheritdoc}
     */
    public function getLastName()
    {
        return $this->getAmazonName()->getLastName();
    }

    /**
     * {@inheritdoc}
     */
    public function getLines()
    {
        return $this->lines;
    }

    /**
     * {@inheritdoc}
     */
    public function getLine($lineNumber)
    {
        if (isset($this->lines[$lineNumber])) {
            return $this->lines[$lineNumber];
        }
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * {@inheritdoc}
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * {@inheritdoc}
     */
    public function getPostCode()
    {
        return $this->postCode;
    }

    /**
     * {@inheritdoc}
     */
    public function getCountryCode()
    {
        return $this->countryCode;
    }

    /**
     * {@inheritdoc}
     */
    public function getTelephone()
    {
        return $this->telephone;
    }

    /**
     * {@inheritdoc}
     */
    public function getCompany()
    {
        return $this->company;
    }

    /**
     * {@inheritdoc}
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setLines($lines)
    {
        $this->lines = $lines;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setCity($city)
    {
        $this->city = $city;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setState($state)
    {
        $this->state = $state;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setPostCode($postCode)
    {
        $this->postCode = $postCode;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setCountryCode($countryCode)
    {
        $this->countryCode = $countryCode;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setTelephone($telephone)
    {
        $this->telephone = $telephone;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setCompany($company)
    {
        $this->company = $company;
        return $this;
    }

    /**
     * Get AmazonName
     *
     * @return AmazonName
     */
    private function getAmazonName()
    {
        if (null === $this->amazonName) {
            $this->amazonName = $this->amazonNameFactory
                ->create(['name' => $this->name, 'country' => $this->countryCode]);
        }
        return $this->amazonName;
    }
}
