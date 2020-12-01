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

/**
 * @deprecated As of February 2021, this Legacy Amazon Pay plugin has been
 * deprecated, in favor of a newer Amazon Pay version available through GitHub
 * and Magento Marketplace. Please download the new plugin for automatic
 * updates and to continue providing your customers with a seamless checkout
 * experience. Please see https://pay.amazon.com/help/E32AAQBC2FY42HS for details
 * and installation instructions.
 */
class AmazonAddressDecoratorDe implements AmazonAddressInterface
{
    /**
     * @var AmazonAddressInterface
     */
    private $amazonAddress;

    /**
     * @param AmazonAddressInterface $amazonAddress
     */
    public function __construct(AmazonAddressInterface $amazonAddress)
    {
        $this->amazonAddress = $amazonAddress;
    }

    /**
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
     * {@inheritdoc}
     */
    public function getFirstName()
    {
        return $this->amazonAddress->getFirstName();
    }

    /**
     * {@inheritdoc}
     */
    public function getLastName()
    {
        return $this->amazonAddress->getLastName();
    }

    /**
     * {@inheritdoc}
     */
    public function getCity()
    {
        return $this->amazonAddress->getCity();
    }

    /**
     * {@inheritdoc}
     */
    public function getState()
    {
        return $this->amazonAddress->getState();
    }

    /**
     * {@inheritdoc}
     */
    public function getPostCode()
    {
        return $this->amazonAddress->getPostCode();
    }

    /**
     * {@inheritdoc}
     */
    public function getCountryCode()
    {
        return $this->amazonAddress->getCountryCode();
    }

    /**
     * {@inheritdoc}
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
     *
     * @return null|string
     */
    public function getLine($lineNumber)
    {
        return $this->amazonAddress->getLine($lineNumber);
    }

    /**
     * {@inheritdoc}
     */
    public function shiftLines($times)
    {
        return $this->amazonAddress->shiftLines($times);
    }

    /**
     * {@inheritdoc}
     */
    public function setCompany($company)
    {
        return $this->amazonAddress->setCompany($company);
    }
}
