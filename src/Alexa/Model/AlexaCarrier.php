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
namespace Amazon\Alexa\Model;

class AlexaCarrier extends \Magento\Framework\Model\AbstractModel
{
    protected function _construct()
    {
        $this->_init(\Amazon\Alexa\Model\ResourceModel\AlexaCarrier::class);
    }

    /**
     * Return Alexa Carrier Code
     *
     * @param $carrierTitle
     * @param $code
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function loadByCarrierTitle($carrierTitle, $code)
    {
        $this->_getResource()->load($this, trim($carrierTitle));

        $carrierCode = $this->getData('carrier_code');

        if ($carrierCode) {
            return $carrierCode;
        }

        if (stripos($code, 'usps') !== false) {
            return 'USPS';
        }

        if (stripos($code, 'ups') !== false) {
            return 'UPS';
        }

        if (stripos($code, 'fedex') !== false) {
            return 'FEDEX';
        }
        return strtoupper($code);
    }
}
