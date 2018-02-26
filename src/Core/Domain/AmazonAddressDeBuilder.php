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
use Magento\Framework\Api\DataObjectHelper;

/**
 * Class Builder
 */
class AmazonAddressDeBuilder
{
    /**
     * @var array
     */
    private $data = [];

    /**
     * @param DataObjectHelper $dataObjectHelper
     */
    public function __construct(
        DataObjectHelper $dataObjectHelper
    ) {
        $this->dataObjectHelper = $dataObjectHelper;
    }

    /**
     * @param array $data
     * @return $this
     */
    public function setData(array $data)
    {
        $this->data = $data;
        return $this;
    }

    /**
     * @param array $address Unmodified Amazon address array
     * @param array $lines
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setAddress(array $address, array $lines)
    {
        if (!empty($lines[3])) {
            // replace all lines
            $this->data['lines'] = [ $lines[3] ];
            $firstTwoLines = $lines[1] . ' ' . $lines[2];

            if (is_numeric($lines[1]) || $this->isPackstationAddress($firstTwoLines)) {
                // PO Box
                $this->data['lines'][] = $firstTwoLines;
            } else {
                $this->data['company'] = $firstTwoLines;
            }
        } elseif (!empty($lines[2])) {
            // replace all lines
            $this->data['lines'] = [ $lines[2] ];

            if (!empty($lines[1])) {
                if (is_numeric($lines[1]) || $this->isPackstationAddress($lines[1])) {
                    // PO Box
                    $this->data['lines'][] = $lines[1];
                } else {
                    $this->data['company'] = $lines[1];
                }
            }
        } elseif (!empty($lines[1])) {
            // replace all lines
            $this->data['lines'] = [ $lines[1] ];
        }
        return $this;
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
     * @param AmazonAddress $amazonAddress
     * @return AmazonAddress
     */
    public function build(AmazonAddressInterface $amazonAddress)
    {
        $amazonAddress->setLines($this->data['lines']);
        unset($this->data['lines']);

        $this->dataObjectHelper->populateWithArray(
            $amazonAddress,
            $this->data,
            AmazonAddressInterface::class
        );

        return $amazonAddress;
    }
}
