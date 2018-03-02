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
     * @var AmazonAddressInterface[]
     */
    private $addressDecoratorPool;

    /**
     * @var AmazonNameFactory
     */
    private $addressNameFactory;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param AmazonNameFactory $addressNameFactory
     * @param array $addressDecoratorPool Per-country custom decorators of incoming address data.
     *                                         The key as an "ISO 3166-1 alpha-2" country code and
     *                                         the value as an FQCN of a child of AmazonAddress.
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        AmazonNameFactory $addressNameFactory,
        array $addressDecoratorPool = []
    ) {
        $this->objectManager = $objectManager;
        $this->addressNameFactory = $addressNameFactory;
        $this->addressDecoratorPool = $addressDecoratorPool;
    }

    /**
     * @param array $responseData
     *
     * @return AmazonAddressInterface
     * @throws LocalizedException
     */
    public function create(array $responseData = []): AmazonAddressInterface
    {
        $address = $responseData['address'];
        $addressName = $this->addressNameFactory->create(
            ['name' => $address['Name'], 'country' => $address['CountryCode']]
        );

        $data = [
            'city' => $address['City'],
            'postCode' => $address['PostalCode'],
            'countryCode' => $address['CountryCode'],
            'telephone' => $address['Phone'] ?? '',
            'state' => $address['StateOrRegion'] ?? '',
            'name' => $address['Name'],
            'firstName' => $addressName->getFirstName(),
            'lastName' => $addressName->getLastName(),
            'lines' => $this->getLines($address)
        ];

        $amazonAddress = $this->objectManager->create(AmazonAddress::class, ['addressData' => $data]);

        $countryCode = strtoupper($address['CountryCode']);
        if (empty($this->addressDecoratorPool[$countryCode])) {
            return $amazonAddress;
        }

        $amazonAddress = $this->objectManager->create(
            $this->addressDecoratorPool[$countryCode],
            [
                'amazonAddress' => $amazonAddress,
                'responseData' => $responseData
            ]
        );

        if (!$amazonAddress instanceof AmazonAddress) {
            throw new LocalizedException(
                __(
                    'Address country handler %1 must be of type %2',
                    [$this->addressDecoratorPool[$countryCode], AmazonAddress::class]
                )
            );
        }

        return $amazonAddress;
    }

    /**
     * Returns address lines.
     *
     * @param array $responseData
     * @return array
     */
    private function getLines(array $responseData = []): array
    {
        $lines = [];
        for ($i = 1; $i <= 3; $i++) {
            $lines[$i] = $responseData['AddressLine' . $i] ?? '';
        }

        return $lines;
    }
}
