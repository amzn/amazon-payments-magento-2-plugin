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

use Amazon\Core\Api\Data\AmazonNameInterface;
use Magento\Framework\Api\AbstractSimpleObject;

class AmazonName extends AbstractSimpleObject implements AmazonNameInterface
{
    const FIRST_NAME = 'first_name';
    const LAST_NAME  = 'last_name';

    /**
     * {@inheritdoc}
     */
    public function getFirstName()
    {
        return $this->_get(self::FIRST_NAME);
    }

    /**
     * {@inheritdoc}
     */
    public function getLastName()
    {
        return $this->_get(self::LAST_NAME);
    }

    /**
     * {@inheritdoc}
     */
    public function setFirstName($name)
    {
        return $this->setData(self::FIRST_NAME, $name);
    }

    /**
     * {@inheritdoc}
     */
    public function setLastName($name)
    {
        return $this->setData(self::LAST_NAME, $name);
    }
}
