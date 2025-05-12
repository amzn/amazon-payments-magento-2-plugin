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

namespace Amazon\Pay\Helper;

use Amazon\Pay\Domain\AmazonAddressInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\AddressInterfaceFactory;
use Magento\Customer\Api\Data\RegionInterfaceFactory;
use Magento\Directory\Model\RegionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Eav\Model\Config as EavConfig;

class Address
{
    /**
     * @var AddressInterfaceFactory
     */
    private $addressFactory;

    /**
     * @var RegionFactory
     */
    private $regionFactory;

    /**
     * @var RegionInterfaceFactory
     */
    private $regionDataFactory;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var eavConfig
     */
    private $eavConfig;

    /**
     * @var productMetadata
     */
    private $productMetadata;

    /**
     * Address constructor
     *
     * @param AddressInterfaceFactory $addressFactory
     * @param RegionFactory $regionFactory
     * @param RegionInterfaceFactory $regionDataFactory
     * @param ScopeConfigInterface $config
     * @param EavConfig $eavConfig
     * @param ProductMetadataInterface $productMetadata
     */
    public function __construct(
        AddressInterfaceFactory $addressFactory,
        RegionFactory $regionFactory,
        RegionInterfaceFactory $regionDataFactory,
        ScopeConfigInterface $config,
        EavConfig $eavConfig,
        ProductMetadataInterface $productMetadata
    ) {
        $this->addressFactory    = $addressFactory;
        $this->regionFactory     = $regionFactory;
        $this->regionDataFactory = $regionDataFactory;
        $this->scopeConfig = $config;
        $this->productMetadata = $productMetadata;
        $this->eavConfig = $eavConfig;
    }

    /**
     * Convert Amazon Address to Magento Address
     *
     * @param AmazonAddressInterface $amazonAddress
     * @return AddressInterface
     */
    public function convertToMagentoEntity(AmazonAddressInterface $amazonAddress)
    {
        if ($this->productMetadata->getEdition() == 'Enterprise') {
            $addressLinesAllowed = (int) $this->eavConfig->getAttribute(
                'customer_address',
                'street'
            )->getMultilineCount();
        } else {
            $addressLinesAllowed = (int) $this->scopeConfig->getValue(
                'customer/address/street_lines',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
        }
 
        $address = $this->addressFactory->create();
        $address->setFirstname($amazonAddress->getFirstName());
        $address->setLastname($amazonAddress->getLastName());
        $address->setCity($amazonAddress->getCity());
        $address->setPostcode($amazonAddress->getPostCode());
        $address->setTelephone($amazonAddress->getTelephone());
        $address->setCountryId($this->getCountryId($amazonAddress));

        $company = !empty($amazonAddress->getCompany()) ? $amazonAddress->getCompany() : '';
        $address->setCompany($company);

        /*
         * The number of lines in a street address is configurable via 'customer/address/street_lines'.
         * To avoid discarding information, we'll concatenate additional lines so that they fit within the configured
         *  address length.
         */
        $lines = [];
        for ($i = 1; $i <= 4; $i++) {
            $line = (string) $amazonAddress->getLine($i);
            if ($i <= $addressLinesAllowed) {
                $lines[] = $line;
            } else {
                $lines[count($lines) - 1] = trim($lines[count($lines) - 1] . ' ' . $line);
            }
        }
        $address->setStreet(array_values($lines));

        if ($amazonAddress->getState()) {
            $address->setRegion($this->getRegionData($amazonAddress, $address->getCountryId()));
        }

        return $address;
    }

    /**
     * Get country code from Amazon address
     *
     * @param AmazonAddressInterface $amazonAddress
     * @return string
     */
    protected function getCountryId(AmazonAddressInterface $amazonAddress)
    {
        return strtoupper($amazonAddress->getCountryCode());
    }

    /**
     * Get region information from Amazon address
     *
     * @param AmazonAddressInterface $amazonAddress
     * @param string $countryId
     * @return \Magento\Customer\Api\Data\RegionInterface $regionData
     */
    protected function getRegionData(AmazonAddressInterface $amazonAddress, $countryId)
    {
        $region     = $this->regionFactory->create();
        $regionData = $this->regionDataFactory->create();

        $region->loadByCode($amazonAddress->getState(), $countryId);

        if (!$region->getId()) {
            $region->loadByName($amazonAddress->getState(), $countryId);
        }

        if ($region->getId()) {
            $regionData
                ->setRegionId($region->getId())
                ->setRegionCode($region->getCode())
                ->setRegion($region->getDefaultName());
        } else {
            $regionData->setRegion($amazonAddress->getState());
        }

        return $regionData;
    }

    /**
     * Convert Magento address to array for json encode
     *
     * @param AddressInterface $address
     * @return array
     */
    public function convertToArray(AddressInterface $address)
    {
        $data = [
            AddressInterface::CITY       => $address->getCity(),
            AddressInterface::FIRSTNAME  => $address->getFirstname(),
            AddressInterface::LASTNAME   => $address->getLastname(),
            AddressInterface::COUNTRY_ID => $address->getCountryId(),
            AddressInterface::STREET     => $address->getStreet(),
            AddressInterface::POSTCODE   => $address->getPostcode(),
            AddressInterface::COMPANY    => $address->getCompany(),
            AddressInterface::TELEPHONE  => null,
            AddressInterface::REGION     => null,
            AddressInterface::REGION_ID  => null,
            'region_code'                => null
        ];

        if ($address->getTelephone()) {
            $data[AddressInterface::TELEPHONE] = $address->getTelephone();
        }

        if ($address->getRegion()) {
            $data[AddressInterface::REGION] = $address->getRegion()->getRegion();

            if ($address->getRegion()->getRegionId()) {
                $data[AddressInterface::REGION_ID] = $address->getRegion()->getRegionId();
                $data['region_code']               = $address->getRegion()->getRegionCode();
            }
        }

        return $data;
    }

    /**
     * Ensure shipping address associated with checkout session is one coming from Amazon
     *
     * @param mixed $amazonAddress
     * @param \Magento\Quote\Model\Quote\Address $magentoAddress
     */
    public function validateShippingIsSame($amazonAddress, $magentoAddress)
    {
        return $this->fuzzyCompare($amazonAddress['firstname'], $magentoAddress->getFirstname()) &&
            $this->fuzzyCompare($amazonAddress['lastname'], $magentoAddress->getLastname()) &&
            $this->fuzzyCompare($amazonAddress['city'], $magentoAddress->getCity()) &&
            $this->fuzzyCompare($amazonAddress['postcode'], $magentoAddress->getPostcode()) &&
            $this->fuzzyCompare($amazonAddress['street'][0], $magentoAddress->getStreet()[0]);
    }

    /**
     * Compare two strings after stripping punctuation and lowercasing
     *
     * @param string $a
     * @param string $b
     * @return bool
     */
    protected function fuzzyCompare($a, $b)
    {
        return strtolower(preg_replace("#[[:punct:]]#", "", $a)) == strtolower(preg_replace("#[[:punct:]]#", "", $b));
    }
}
