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
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\StoreManagerInterface;

class AmazonCustomerFactory
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager = null;

    /**
     * @var AmazonCustomer
     */
    private $amazonCustomer;

    /**
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        AmazonCustomerInterface $amazonCustomer
    ) {
        $this->objectManager  = $objectManager;
        $this->amazonCustomer = $amazonCustomer;
    }

    /**
     * @param array $data
     * @return AmazonCustomer
     */
    public function create(array $data = [])
    {
        $amazonCustomerBuilder = $this->objectManager->create(AmazonCustomerBuilder::class);

        return $amazonCustomerBuilder
            ->setData($data)
            ->build($this->amazonCustomer);
    }
}
