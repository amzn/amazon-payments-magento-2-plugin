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

use Amazon\Core\Api\Data\AmazonCustomerInterface;
use Amazon\Core\Api\Data\AmazonNameInterface;
use Magento\Framework\Api\AbstractSimpleObject;

class AmazonCustomer extends \Magento\Framework\DataObject implements AmazonCustomerInterface
{
    /**
     * {@inheritdoc}
     */
    public function getEmail()
    {
        return $this->getData('email');
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->getData('id');
    }

    /**
     * {@inheritdoc}
     */
    public function getFirstName()
    {
        return $this->getData(AmazonNameInterface::FIRST_NAME);
    }

    /**
     * {@inheritdoc}
     */
    public function getLastName()
    {
        return $this->getData(AmazonNameInterface::LAST_NAME);
    }
}
