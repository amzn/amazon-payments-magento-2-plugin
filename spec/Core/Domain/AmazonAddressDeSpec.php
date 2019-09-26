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

class AmazonAddressDeSpec extends ObjectBehavior
{
    private $commonAddressData = [
        'Name' => 'Firstname Lastname',
        'City' => 'city',
        'PostalCode' => 'PO4 CODE',
        'CountryCode' => 'DE',
        'Phone' => '123456',
        'StateOrRegion' => 'Caledonia',
    ];

    function let(AmazonNameFactory $nameFactory)
    {
        $nameFactory->create(Argument::type('array'))
                    ->willReturn(new AmazonName('Firstname Lastname'));

        $this->beConstructedWith($this->commonAddressData, $nameFactory);
    }

    function it_sets_as_company_first_2_address_lines_and_line_3_as_only_address_if_line3_is_set(AmazonNameFactory $nameFactory)
    {
        $addressData = [
            'AddressLine1' => 'address 1',
            'AddressLine2' => 'address 2',
            'AddressLine3' => 'address 3',
        ];
        $this->beConstructedWith(array_merge($this->commonAddressData, $addressData), $nameFactory);

        $this->getCompany()->shouldReturn('address 1 address 2');
        $this->getLines()->shouldReturn(['address 3']);
        $this->getPostCode()->shouldReturn('PO4 CODE');
    }

    function it_sets_as_po_box_first_2_lines_if_they_contain_packstation_and_line3_is_set(AmazonNameFactory $nameFactory)
    {
        $addressData = [
            'AddressLine1' => 'address 1 Packstation',
            'AddressLine2' => 'address 2',
            'AddressLine3' => 'address 3',
        ];
        $this->beConstructedWith(array_merge($this->commonAddressData, $addressData), $nameFactory);

        $this->getCompany()->shouldReturn('');
        $this->getLines()->shouldReturn(['address 3', 'address 1 Packstation address 2']);
    }

    function it_sets_as_po_box_first_2_lines_if_line1_is_numeric_and_line3_is_set(AmazonNameFactory $nameFactory)
    {
        $addressData = [
            'AddressLine1' => '11',
            'AddressLine2' => 'address 2',
            'AddressLine3' => 'address 3',
        ];
        $this->beConstructedWith(array_merge($this->commonAddressData, $addressData), $nameFactory);

        $this->getCompany()->shouldReturn('');
        $this->getLines()->shouldReturn(['address 3', '11 address 2']);
    }

    function it_sets_as_company_line1_and_address_as_line2_if_line2_is_set(AmazonNameFactory $nameFactory)
    {
        $addressData = [
            'AddressLine1' => 'address 1',
            'AddressLine2' => 'address 2',
        ];
        $this->beConstructedWith(array_merge($this->commonAddressData, $addressData), $nameFactory);

        $this->getCompany()->shouldReturn('address 1');
        $this->getLines()->shouldReturn(['address 2']);
        $this->getPostCode()->shouldReturn('PO4 CODE');
    }

    function it_sets_line2_as_address_and_line1_as_po_box_if_line1_contains_packstation_and_line2_is_set(AmazonNameFactory $nameFactory)
    {
        $addressData = [
            'AddressLine1' => 'Packstation',
            'AddressLine2' => 'address 2',
        ];
        $this->beConstructedWith(array_merge($this->commonAddressData, $addressData), $nameFactory);

        $this->getCompany()->shouldReturn('');
        $this->getLines()->shouldReturn(['address 2', 'Packstation']);
    }

    function it_sets_line2_as_address_and_line1_as_po_box_if_line1_is_numeric_and_line2_is_set(AmazonNameFactory $nameFactory)
    {
        $addressData = [
            'AddressLine1' => '11',
            'AddressLine2' => 'address 2',
        ];
        $this->beConstructedWith(array_merge($this->commonAddressData, $addressData), $nameFactory);

        $this->getCompany()->shouldReturn('');
        $this->getLines()->shouldReturn(['address 2', '11']);
    }

    function it_returns_line1_if_only_address(AmazonNameFactory $nameFactory)
    {
        $addressData = [
            'AddressLine1' => 'address 1',
        ];
        $this->beConstructedWith(array_merge($this->commonAddressData, $addressData), $nameFactory);

        $this->getCompany()->shouldReturn('');
        $this->getLines()->shouldReturn(['address 1']);
        $this->getPostCode()->shouldReturn('PO4 CODE');
    }
}
