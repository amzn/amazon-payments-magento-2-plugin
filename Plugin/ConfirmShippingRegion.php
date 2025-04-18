<?php
/**
 * Copyright © Amazon.com, Inc. or its affiliates. All Rights Reserved.
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
namespace Amazon\Pay\Plugin;

use Magento\Checkout\Api\Data\ShippingInformationInterface;
use Magento\Checkout\Model\ShippingInformationManagement;
use Magento\Directory\Model\RegionFactory;

class ConfirmShippingRegion
{
    /**
     * @var RegionFactory
     */
    private $regionFactory;

    /**
     * Plugin constructor
     *
     * @param RegionFactory $regionFactory
     */
    public function __construct(RegionFactory $regionFactory) {
        $this->regionFactory = $regionFactory;
    }

    /**
     * Ensure regionId matches code in submitted shipping address (null if empty)
     *
     * @param ShippingInformationManagement $subject
     * @param int $cartId
     * @param ShippingInformationInterface $addressInformation
     * @return null
     */
    public function beforeSaveAddressInformation($subject, $cartId, $addressInformation)
    {
        if ($this->isExpressCheckout()) {
            $address = $addressInformation->getShippingAddress();
    
            $regionModel = $this->regionFactory->create();
            $regionId = $regionModel->loadByCode($address->getRegionCode(), $address->getCountryId())->getRegionId();
            $address->setRegionId($regionId);
        }

        return null;
    }

    private function isExpressCheckout() {
        $url = parse_url($_SERVER['HTTP_REFERER']);
        if (isset($url['query'])) {
            parse_str($url['query'], $queryParams);
            return isset($queryParams['amazonCheckoutSessionId']);
        }
        
        return false;
    }
}
