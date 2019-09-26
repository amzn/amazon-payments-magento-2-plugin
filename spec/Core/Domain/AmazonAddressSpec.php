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

use Amazon\Core\Domain\AmazonName;
use Amazon\Core\Domain\AmazonNameFactory;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class AmazonAddressSpec extends ObjectBehavior
{
    function let(AmazonNameFactory $nameFactory)
    {
        $fullName = 'Firstname Lastname';
        $nameFactory->create(Argument::type('array'))
                    ->willReturn(new AmazonName($fullName));

        $addressData = [
            'Name' => $fullName,
            'City' => 'city',
            'PostalCode' => 'PO4 CODE',
            'CountryCode' => 'GB',
            'AddressLine1' => 'address 1',
            'AddressLine2' => 'address 2',
            'AddressLine3' => 'address 3',
            'Phone' => '123456',
            'StateOrRegion' => 'Caledonia',
        ];
        $this->beConstructedWith($addressData, $nameFactory);
    }

    function it_maps_input_correctly()
    {
        $this->getTelephone()->shouldReturn('123456');
        $this->getCountryCode()->shouldReturn('GB');
        $this->getPostCode()->shouldReturn('PO4 CODE');
        $this->getState()->shouldReturn('Caledonia');
    }

    function it_returns_address_lines()
    {
        $this->getLine(1)->shouldReturn('address 1');
        $this->getLine(2)->shouldReturn('address 2');
        $this->getLine(3)->shouldReturn('address 3');
        $this->getLine(4)->shouldReturn(null);
        $this->getLine(40)->shouldReturn(null);

        $this->getLines()->shouldReturn([1 => 'address 1', 2 => 'address 2', 3 => 'address 3']);
    }

    function it_returns_names()
    {
        $this->getFirstName()->shouldReturn('Firstname');
        $this->getLastName()->shouldReturn('Lastname');
    }

    function it_correctly_parses_empty_arrays_as_address_lines(AmazonNameFactory $nameFactory)
    {
        $fullName = 'Firstname Lastname';
        $addressData = [
            'Name' => $fullName,
            'City' => 'city',
            'PostalCode' => 'PO4 CODE',
            'CountryCode' => 'GB',
            'AddressLine1' => [],
            'AddressLine2' => '',
        ];
        $this->beConstructedWith($addressData, $nameFactory);

        $this->getLine(1)->shouldBe('');
        $this->getLine(2)->shouldBe('');
        $this->getLine(3)->shouldBe(null);

        $this->getLines()->shouldBe([1 => '', 2 => '']);
    }
}
