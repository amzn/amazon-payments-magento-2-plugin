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

class AmazonAddressDecoratorDe implements AmazonAddressInterface
{
    /**
     * @var AmazonAddressInterface
     */
    private $amazonAddress;

    /**
     * AmazonAddressDecoratorDe constructor
     *
     * @param AmazonAddressInterface $amazonAddress
     */
    public function __construct(AmazonAddressInterface $amazonAddress)
    {
        $this->amazonAddress = $amazonAddress;
    }

    /**
     * Get array of address lines from Amazon address
     *
     * @return array
     */
    public function getLines()
    {
        $line1 = (string) $this->amazonAddress->getLine(1);
        $line2 = (string) $this->amazonAddress->getLine(2);
        $line3 = (string) $this->amazonAddress->getLine(3);

        $lines = [];
        switch (true) {
            case !empty($line3):
                $lines = [$line3];
                $firstTwoLines = $line1 . ' ' . $line2;
                if ($this->isPOBox($line1, $firstTwoLines)) {
                    $lines[] = $firstTwoLines;
                }
                break;
            case !empty($line2):
                $lines = [$line2];
                if ($this->isPOBox($line1, $line1)) {
                    $lines[] = $line1;
                }
                break;
            case !empty($line1):
                $lines = [$line1];
                break;
        }

        return $lines;
    }

    /**
     * Get company from Amazon address
     *
     * @return string
     */
    public function getCompany()
    {
        $line1 = (string) $this->amazonAddress->getLine(1);
        $line2 = (string) $this->amazonAddress->getLine(2);
        $line3 = (string) $this->amazonAddress->getLine(3);

        $company = $this->amazonAddress->getCompany();
        switch (true) {
            case !empty($line3):
                $firstTwoLines = $line1 . ' ' . $line2;
                if (!$this->isPOBox($line1, $firstTwoLines)) {
                    $company = $firstTwoLines;
                    $this->amazonAddress->setCompany($company);
                    $this->amazonAddress->shiftLines(2);
                }
                break;
            case !empty($line2):
                if (!$this->isPOBox($line1, $line1)) {
                    $company = $line1;
                    $this->amazonAddress->setCompany($company);
                    $this->amazonAddress->shiftLines();
                }
                break;
        }

        return $company;
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
        return $this->amazonAddress->getCity();
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
     * Returns true if strings contain address.
     *
     * @param string $line1
     * @param string $line2
     * @return bool
     */
    private function isPOBox($line1, $line2)
    {
        return is_numeric($line1) || $this->isPackstationAddress($line2);
    }

    /**
     * Return true if address information indicates it is a packstation
     *
     * @link https://en.wikipedia.org/wiki/Packstation
     * @param string $address
     * @return bool
     */
    private function isPackstationAddress($address)
    {
        return stripos($address, 'packstation') !== false;
    }

    /**
     * Get an address line
     *
     * @param int $lineNumber
     * @return null|string
     */
    public function getLine($lineNumber)
    {
        return $this->amazonAddress->getLine($lineNumber);
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
