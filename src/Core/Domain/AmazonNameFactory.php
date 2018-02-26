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
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\StoreManagerInterface;

class AmazonNameFactory
{
    /**
     * @var AmazonNameInterface
     */
    private $amazonName;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager = null;

    /**
     * @var array
     */
    private $perCountryNameHandlers;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param AmazonNameInterface $amazonName
     * @param array $perCountryNameHandlers Per-country custom handlers of incoming name data.
     *                                         The key as an "ISO 3166-1 alpha-2" country code and
     *                                         the value as an FQCN of a child of AmazonAddress.
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        AmazonNameInterface $amazonName,
        array $perCountryNameHandlers = []
    ) {
        $this->objectManager          = $objectManager;
        $this->amazonName             = $amazonName;
        $this->perCountryNameHandlers = $perCountryNameHandlers;
    }

    /**
     * @param array $data
     * @return AmazonName
     * @throws LocalizedException
     */
    public function create(array $data = [])
    {
        $instanceClassName = AmazonNameBuilder::class;

        if (isset($data['country'])) {
            $countryCode = strtoupper($data['country']);

            $this->perCountryNameHandlers = array_change_key_case($this->perCountryNameHandlers, CASE_UPPER);

            if (!empty($this->perCountryNameHandlers[$countryCode])) {
                $instanceClassName = (string) $this->perCountryNameHandlers[$countryCode];
            }
        }

        $amazonNameBuilder = $this->objectManager->create($instanceClassName);

        return $amazonNameBuilder
            ->setName($data['name'])
            ->build($this->amazonName);
    }
}
