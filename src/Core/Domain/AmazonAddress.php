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
     * @var AmazonName
     */
    private $amazonName;

    /**
     * @var AmazonNameFactory
     */
    private $amazonNameFactory;

    /**
     * @param AmazonNameFactory $addressNameFactory
     * @param array $data
     */
    public function __construct(AmazonNameFactory $amazonNameFactory, $data)
    {
        $this->amazonNameFactory = $amazonNameFactory;
        parent::__construct($data);
    }

    /**
     * {@inheritdoc}
     */
    public function getFirstName()
    {
        return $this->getAmazonName()->getFirstName();
    }

    /**
     * {@inheritdoc}
     */
    public function getLastName()
    {
        return $this->getAmazonName()->getLastName();
    }

    /**
     * {@inheritdoc}
     */
    public function getLines()
    {
        return $this->getData('lines');
    }

    /**
     * {@inheritdoc}
     */
    public function getLine($lineNumber)
    {
        if (isset($this->getData('lines')[$lineNumber])) {
            return $this->getData('lines')[$lineNumber];
        }
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getCity()
    {
        return $this->getData('city');
    }

    /**
     * {@inheritdoc}
     */
    public function getState()
    {
        return $this->getData('state');
    }

    /**
     * {@inheritdoc}
     */
    public function getPostCode()
    {
        return $this->getData('postCode');
    }

    /**
     * {@inheritdoc}
     */
    public function getCountryCode()
    {
        return $this->getData('countryCode');
    }

    /**
     * {@inheritdoc}
     */
    public function getTelephone()
    {
        return $this->getData('telephone');
    }

    /**
     * {@inheritdoc}
     */
    public function getCompany()
    {
        return $this->getData('company');
    }

    /**
     * Get AmazonName
     *
     * @return AmazonName
     */
    private function getAmazonName()
    {
        if (null === $this->getData('amazonName')) {
            $this->setData('amazonName', $this->amazonNameFactory
                ->create(['name' => $this->getData('name'), 'country' => $this->getData('countryCode')]));
        }
        return $this->getData('amazonName');
    }
}
