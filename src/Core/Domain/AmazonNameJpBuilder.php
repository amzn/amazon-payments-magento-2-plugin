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
use Magento\Framework\Api\DataObjectHelper;

/**
 * Class Builder
 */
class AmazonNameJpBuilder
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
     * Set the first and last name
     * @param string $name
     * @return $this
     */
    public function setName($name)
    {
        $name                    = mb_convert_kana($name, 's', 'utf-8');
        $nameParts               = explode(' ', trim($name), 2);
        $this->data['firstName'] = $nameParts[0];
        $this->data['lastName']  = isset($nameParts[1]) ? $nameParts[1] : '.';
        return $this;
    }

    /**
     * @param AmazonName $amazonName
     * @return AmazonName
     */
    public function build(AmazonNameInterface $amazonName)
    {
        $this->dataObjectHelper->populateWithArray(
            $amazonName,
            $this->data,
            AmazonNameInterface::class
        );

        return $amazonName;
    }
}
