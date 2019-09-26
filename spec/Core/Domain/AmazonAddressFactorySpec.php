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
namespace spec\Amazon\Core\Domain;

use Amazon\Core\Domain\AmazonAddress;
use Amazon\Core\Domain\AmazonAddressDe;
use Magento\Framework\App\ObjectManager;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class AmazonAddressFactorySpec extends ObjectBehavior
{
    function it_returns_a_valid_de_instance()
    {
        $addressData = [
            'Name' => 'Firstname Lastname',
            'City' => 'city',
            'PostalCode' => 'PO4 CODE',
            'CountryCode' => 'DE',
            'Phone' => '123456',
            'StateOrRegion' => 'Caledonia',
        ];
        $this->beConstructedWith(ObjectManager::getInstance(), ['de' => AmazonAddressDe::class]);

        $this->create(['address' => $addressData])->shouldReturnAnInstanceOf(AmazonAddressDe::class);
    }

    function it_returns_a_default_instance_when_no_country_handlers_are_found()
    {
        $addressData = [
            'Name' => 'Firstname Lastname',
            'City' => 'city',
            'PostalCode' => 'PO4 CODE',
            'CountryCode' => 'FR',
            'Phone' => '123456',
            'StateOrRegion' => 'Caledonia',
        ];
        $this->beConstructedWith(ObjectManager::getInstance(), ['de' => AmazonAddressDe::class]);

        $this->create(['address' => $addressData])->shouldReturnAnInstanceOf(AmazonAddress::class);
    }

    function it_throws_if_the_handler_is_not_a_valid_type()
    {
        $addressData = [
            'Name' => 'Firstname Lastname',
            'City' => 'city',
            'PostalCode' => 'PO4 CODE',
            'CountryCode' => 'DE',
            'Phone' => '123456',
            'StateOrRegion' => 'Caledonia',
        ];
        $this->beConstructedWith(ObjectManager::getInstance(), ['de' => \stdClass::class]);

        $this->shouldThrow(\Magento\Framework\Exception\LocalizedException::class)
             ->during('create', [['address' => $addressData]]);
    }
}
