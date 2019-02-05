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
use Magento\Framework\App\Config\ScopeConfigInterface;

class AmazonAddressDecoratorJp implements AmazonAddressInterface
{
    /**
     * @var AmazonAddressInterface
     */
    private $amazonAddress;
    private $_scopeConfig;

    /**
     * @param AmazonAddressInterface $amazonAddress
     * @param ScopeConfigInterface $config
     */
    public function __construct(
        AmazonAddressInterface $amazonAddress,
        ScopeConfigInterface $config
    ) {
        $this->amazonAddress = $amazonAddress;
        $this->_scopeConfig = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function getLines()
    {
        $addressLinesAllowed = (int)$this->_scopeConfig->getValue('customer/address/street_lines', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $city = $this->amazonAddress->getCity();


        /*
         * AmazonAddressDecoratorJp->getCity() returns address line 1 when city is empty.
         * Omit line 1 from the street address in this case.
         */
        $offset = empty($city) ? 1 : 0;

        /*
         * The number of lines in a street address is configurable via 'customer/address/street_lines'.
         * To avoid discarding information, we'll concatenate additional lines so that they fit within the configured
         *  address length.
         */
        $lines = [];
        for($i = 1; $i <= 4; $i++) {
            $line = (string) $this->amazonAddress->getLine($i+$offset);
            if($i <= $addressLinesAllowed) {
                $lines[] = $line;
            } else {
                $lines[count($lines)-1] = trim($lines[count($lines)-1] . ' ' . $line);
            }
        }

        return $lines;
    }

    /**
     * {@inheritdoc}
     */
    public function getCompany()
    {
        return $this->amazonAddress->getCompany();
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
        return $this->amazonAddress->getCity() ?? $this->amazonAddress->getLine(1);
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
     * {@inheritdoc}
     */
    public function getLine($lineNumber)
    {
        $this->amazonAddress->getLine($lineNumber);
    }
}
