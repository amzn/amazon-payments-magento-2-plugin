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

class AmazonAddressJp extends AmazonAddress
{
    /**
     * @param array $address
     * @param AmazonNameFactory $addressNameFactory
     */
    public function __construct(array $address, AmazonNameFactory $addressNameFactory)
    {
        $this->name = $addressNameFactory->create(['name' => $address['Name'],
            'country' => $address['CountryCode']
        ]);

        $this->lines = [];
        $lines = [];

        for ($i = 1; $i <= 3; $i++) {
            $key = 'AddressLine' . $i;

            if (isset($address[$key])) {
                if (empty($address[$key])) {
                    $lines[$i] = '';
                } else {
                    $lines[$i] = $address[$key];
                }
            }
        }

        if(!array_key_exists('City', $address)) {
            $this->city = $lines[1];
            $this->lines[] = $lines[2];

            if (count($lines) == 3) {
                $this->lines[] = $lines[3];
            }
        } else {
            $this->city        = $address['City'];
            $this->lines[] = $lines[1] . ' ' . $lines[2];
            $this->lines[] = $lines[3];
        }

        $this->postCode    = $address['PostalCode'];
        $this->countryCode = $address['CountryCode'];

        if (isset($address['Phone'])) {
            $this->telephone = $address['Phone'];
        }

        if (isset($address['StateOrRegion'])) {
            $this->state = $address['StateOrRegion'];
        }
    }
}
