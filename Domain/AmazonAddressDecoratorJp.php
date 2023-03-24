<?php

/**
 * Copyright 2020 Amazon.com, Inc. or its affiliates. All Rights Reserved.
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

namespace Amazon\Pay\Domain;

class AmazonAddressDecoratorJp implements AmazonAddressInterface
{
    /**
     * @var AmazonAddressInterface
     */
    private $amazonAddress;

    /**
     * AmazonAddressDecoratorJp constructor
     *
     * @param AmazonAddressInterface $amazonAddress
     */
    public function __construct(
        AmazonAddressInterface $amazonAddress
    ) {
        $this->amazonAddress = $amazonAddress;
    }

    /**
     * @inheritDoc
     */
    public function getLines()
    {
        return $this->amazonAddress->getLines();
    }

    /**
     * @inheritDoc
     */
    public function getCompany()
    {
        return $this->amazonAddress->getCompany();
    }

    /**
     * @inheritDoc
     */
    public function getFirstName()
    {
        return $this->amazonAddress->getFirstName();
    }

    /**
     * @inheritDoc
     */
    public function getLastName()
    {
        return $this->amazonAddress->getLastName();
    }

    /**
     * @inheritDoc
     */
    public function getCity()
    {
        return $this->amazonAddress->getCity() ?? '-';
    }

    /**
     * @inheritDoc
     */
    public function getState()
    {
        return $this->amazonAddress->getState();
    }

    /**
     * @inheritDoc
     */
    public function getPostCode()
    {
        return $this->amazonAddress->getPostCode();
    }

    /**
     * @inheritDoc
     */
    public function getCountryCode()
    {
        return $this->amazonAddress->getCountryCode();
    }

    /**
     * @inheritDoc
     */
    public function getTelephone()
    {
        return $this->amazonAddress->getTelephone();
    }

    /**
     * @inheritDoc
     */
    public function getLine($lineNumber)
    {
        $lines = $this->getLines();
        if (isset($lines[$lineNumber - 1])) {
            return $lines[$lineNumber - 1];
        }
        return null;
    }

    /**
     * @inheritDoc
     */
    public function shiftLines($times)
    {
        return $this->amazonAddress->shiftLines($times);
    }

    /**
     * @inheritDoc
     */
    public function setCompany($company)
    {
        return $this->amazonAddress->setCompany($company);
    }
}
