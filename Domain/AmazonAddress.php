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

namespace Amazon\Pay\Domain;

class AmazonAddress extends \Magento\Framework\DataObject implements AmazonAddressInterface
{
    /**
     * @inheritDoc
     */
    public function getFirstName()
    {
        return $this->getData(AmazonAddressInterface::FIRST_NAME);
    }

    /**
     * @inheritDoc
     */
    public function getLastName()
    {
        return $this->getData(AmazonAddressInterface::LAST_NAME);
    }

    /**
     * @inheritDoc
     */
    public function getLines()
    {
        return $this->getData(AmazonAddressInterface::LINES);
    }

    /**
     * @inheritDoc
     */
    public function getLine($lineNumber)
    {
        if (isset($this->getData(AmazonAddressInterface::LINES)[$lineNumber])) {
            return $this->getData(AmazonAddressInterface::LINES)[$lineNumber];
        }
        return null;
    }

    /**
     * @inheritDoc
     */
    public function shiftLines($times = 1)
    {
        while ($times > 0) {
            $lines = $this->getData(AmazonAddressInterface::LINES);
            $newlines = [];
            $numberOfLines = count($lines);
            for ($i = 1; $i <= $numberOfLines; $i++) {
                $newlines[$i] = isset($lines[$i + 1]) ? $lines[$i + 1] : '';
            }
            $this->setData(AmazonAddressInterface::LINES, $newlines);

            $times--;
        }

        return $this->getLines();
    }

    /**
     * @inheritDoc
     */
    public function getCity()
    {
        return $this->getData(AmazonAddressInterface::CITY);
    }

    /**
     * @inheritDoc
     */
    public function getState()
    {
        return $this->getData(AmazonAddressInterface::STATE_OR_REGION);
    }

    /**
     * @inheritDoc
     */
    public function getPostCode()
    {
        return $this->getData(AmazonAddressInterface::POSTAL_CODE);
    }

    /**
     * @inheritDoc
     */
    public function getCountryCode()
    {
        return $this->getData(AmazonAddressInterface::COUNTRY_CODE);
    }

    /**
     * @inheritDoc
     */
    public function getTelephone()
    {
        return $this->getData(AmazonAddressInterface::TELEPHONE);
    }

    /**
     * @inheritDoc
     */
    public function getCompany()
    {
        return $this->getData(AmazonAddressInterface::COMPANY);
    }

    /**
     * @inheritDoc
     */
    public function setCompany($company)
    {
        $this->setData(AmazonAddressInterface::COMPANY, $company);

        return $this->getCompany();
    }
}
