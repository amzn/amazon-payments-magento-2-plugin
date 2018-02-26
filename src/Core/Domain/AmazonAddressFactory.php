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
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\StoreManagerInterface;

class AmazonAddressFactory
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager = null;

    /**
     * @var array
     */
    private $perCountryAddressHandlers;

    /**
     * @var AmazonAddress
     */
    private $amazonAddress;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param AmazonAddressInterface $amazonAddress
     * @param array $perCountryAddressHandlers Per-country custom handlers of incoming address data.
     *                                         The key as an "ISO 3166-1 alpha-2" country code and
     *                                         the value as an FQCN of a child of AmazonAddress.
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        AmazonAddressInterface $amazonAddress,
        array $perCountryAddressHandlers = []
    ) {
        $this->objectManager = $objectManager;
        $this->amazonAddress = $amazonAddress;
        $this->perCountryAddressHandlers = $perCountryAddressHandlers;
    }

    /**
     * @param array $data
     * @return AmazonAddress
     */
    public function create(array $data = [])
    {
        $instanceClassName = AmazonAddressBuilder::class;
        $countryCode = strtoupper($data['address']['CountryCode']);

        $this->perCountryAddressHandlers = array_change_key_case($this->perCountryAddressHandlers, CASE_UPPER);

        if (!empty($this->perCountryAddressHandlers[$countryCode])) {
            $instanceClassName = (string) $this->perCountryAddressHandlers[$countryCode];
        }

        $amazonAddressBuilder = $this->objectManager->create($instanceClassName);

        $addressData = $this->getAddressData($data['address']);

        return $amazonAddressBuilder
            ->setData($addressData)
            ->setAddress($data['address'], $addressData['lines'])
            ->build($this->amazonAddress);
    }

    /**
     * Convert Amazon address array into data array
     *
     * @param array $address
     * @return array
     */
    private function getAddressData($address)
    {
        $data = [];
        $data['name']        = $address['Name'];
        $data['city']        = $address['City'];
        $data['postCode']    = $address['PostalCode'];
        $data['countryCode'] = $address['CountryCode'];

        if (isset($address['Phone'])) {
            $data['telephone'] = $address['Phone'];
        }

        if (isset($address['StateOrRegion'])) {
            $data['state'] = $address['StateOrRegion'];
        }

        $data['lines'] = [];
        for ($i = 1; $i <= 3; $i++) {
            $key = 'AddressLine' . $i;

            if (isset($address[$key])) {
                if (empty($address[$key])) {
                    $data['lines'][$i] = '';
                } else {
                    $data['lines'][$i] = $address[$key];
                }
            }
        }

        return $data;
    }
}
