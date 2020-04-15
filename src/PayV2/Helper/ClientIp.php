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
namespace Amazon\PayV2\Helper;

use Magento\Store\Model\ScopeInterface;

class ClientIp extends \Amazon\Core\Helper\ClientIp
{
    /**
     * @param string $scope
     * @param mixed $scopeCode
     * @return array
     */
    public function getAllowedIps($scope = ScopeInterface::SCOPE_STORE, $scopeCode = null)
    {
        $allowedIpsString = $this->scopeConfig->getValue('payment/amazon_payment_v2/allowed_ips', $scope, $scopeCode);
        return empty($allowedIpsString) ? [] : explode(',', $allowedIpsString);
    }
}
