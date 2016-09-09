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

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\StoreManagerInterface;

class AmazonAddressFactory
{
    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager = null;

    /**
     * @var array
     */
    protected $perCountryAddressHandlers;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param array $perCountryAddressHandlers Per-country custom handlers of incoming address data.
     *                                         The key as an "ISO 3166-1 alpha-2" country code and
     *                                         the value as an FQCN of a child of AmazonAddress.
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        array $perCountryAddressHandlers = []
    ) {
        $this->objectManager = $objectManager;
        $this->perCountryAddressHandlers = array_change_key_case($perCountryAddressHandlers, CASE_UPPER);
    }

    /**
     * @param array $data
     * @return AmazonAddress
     * @throws LocalizedException
     */
    public function create(array $data = [])
    {
        $instanceClassName = AmazonAddress::class;
        $countryCode = strtoupper($data['address']['CountryCode']);

        if (!empty($this->perCountryAddressHandlers[$countryCode])) {
            $instanceClassName = (string) $this->perCountryAddressHandlers[$countryCode];
        }

        $instance = $this->objectManager->create($instanceClassName, $data);

        if (!$instance instanceof AmazonAddress) {
            throw new LocalizedException(
                __('Address country handler %1 must be of type %2', [$instanceClassName, AmazonAddress::class])
            );
        }

        return $instance;
    }
}
