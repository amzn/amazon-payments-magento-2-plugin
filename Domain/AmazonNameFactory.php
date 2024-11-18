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

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\ObjectManagerInterface;

class AmazonNameFactory
{

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager = null;

    /**
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(
        ObjectManagerInterface $objectManager
    ) {
        $this->objectManager = $objectManager;
    }

    /**
     * AmazonNameFactory constructor
     *
     * @param array $data
     * @return AmazonName
     * @throws LocalizedException
     */
    public function create(array $data = [])
    {
        $nameParts = explode(' ', trim($data['name']), 2);
        $data[AmazonNameInterface::FIRST_NAME] = $nameParts[0];
        $data[AmazonNameInterface::LAST_NAME] = $nameParts[1] ?? '.';

        $amazonName = $this->objectManager->create(AmazonName::class, ['data' => $data]);

        return $amazonName;
    }
}
