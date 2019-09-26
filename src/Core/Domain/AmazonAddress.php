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

class AmazonAddress extends \Magento\Framework\DataObject implements AmazonAddressInterface
{
    /**
     * {@inheritdoc}
     */
    public function getFirstName()
    {
        return $this->getData(AmazonAddressInterface::FIRST_NAME);
    }

    /**
     * {@inheritdoc}
     */
    public function getLastName()
    {
        return $this->getData(AmazonAddressInterface::LAST_NAME);
    }

    /**
     * {@inheritdoc}
     */
    public function getLines()
    {
        return $this->getData(AmazonAddressInterface::LINES);
    }

    /**
     * {@inheritdoc}
     */
    public function getLine($lineNumber)
    {
        if (isset($this->getData(AmazonAddressInterface::LINES)[$lineNumber])) {
            return $this->getData(AmazonAddressInterface::LINES)[$lineNumber];
        }
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getCity()
    {
        return $this->getData(AmazonAddressInterface::CITY);
    }

    /**
     * {@inheritdoc}
     */
    public function getState()
    {
        return $this->getData(AmazonAddressInterface::STATE_OR_REGION);
    }

    /**
     * {@inheritdoc}
     */
    public function getPostCode()
    {
        return $this->getData(AmazonAddressInterface::POSTAL_CODE);
    }

    /**
     * {@inheritdoc}
     */
    public function getCountryCode()
    {
        return $this->getData(AmazonAddressInterface::COUNTRY_CODE);
    }

    /**
     * {@inheritdoc}
     */
    public function getTelephone()
    {
        return $this->getData(AmazonAddressInterface::TELEPHONE);
    }

    /**
     * {@inheritdoc}
     */
    public function getCompany()
    {
        return $this->getData(AmazonAddressInterface::COMPANY);
    }
}
