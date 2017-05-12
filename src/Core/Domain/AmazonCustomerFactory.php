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

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\StoreManagerInterface;

class AmazonCustomerFactory
{
    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager = null;

    /**
     * @var array
     */
    protected $perCountryCustomerHandlers;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param array $perCountryCustomerHandlers Per-country custom handlers of incoming name data.
     *                                         The key as an "ISO 3166-1 alpha-2" country code and
     *                                         the value as an FQCN of a child of AmazonAddress.
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        array $perCountryCustomerHandlers = []
    ) {
        $this->objectManager = $objectManager;
        $this->perCountryCustomerHandlers = array_change_key_case($perCountryCustomerHandlers, CASE_UPPER);
    }

    /**
     * @param array $data
     * @return AmazonName
     * @throws LocalizedException
     */
    public function create(array $data = [])
    {
        $instanceClassName = AmazonCustomer::class;
        $countryCode = strtoupper($data['country']);

        if (!empty($this->perCountryCustomerHandlers[$countryCode])) {
            $instanceClassName = (string) $this->perCountryCustomerHandlers[$countryCode];
        }

        $instance = $this->objectManager->create($instanceClassName, $data);

        if (!$instance instanceof AmazonCustomer) {
            throw new LocalizedException(
                __('Customer country handler %1 must be of type %2', [$instanceClassName, AmazonCustomer::class])
            );
        }

        return $instance;
    }
}
