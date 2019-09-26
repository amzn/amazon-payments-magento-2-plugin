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
namespace Amazon\Core\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;

class ClientIp extends AbstractHelper
{
    /**
     * @var string
     */
    private $clientIp;

    /**
     * @var bool
     */
    private $clientHasAllowedIp;

    /**
     * @param Context       $context
     */
    public function __construct(Context $context)
    {
        parent::__construct($context);
        // e.g. X-Forwarded-For can have a comma-separated list of IPs
        $this->clientIp           = explode(',', $context->getRemoteAddress()->getRemoteAddress())[0];
        $allowedIps               = $this->getAllowedIps();
        $this->clientHasAllowedIp = empty($allowedIps) ? true : in_array($this->clientIp, $allowedIps);
    }

    /**
     * @return string
     */
    public function getRemoteClientIp()
    {
        return $this->clientIp;
    }

    /**
     * @param string      $scope
     * @param string|null $scopeCode
     *
     * @return string[]
     */
    public function getAllowedIps($scope = ScopeInterface::SCOPE_STORE, $scopeCode = null)
    {
        $allowedIpsString = $this->scopeConfig->getValue('payment/amazon_payment/allowed_ips', $scope, $scopeCode);
        return empty($allowedIpsString) ? [] : explode(',', $allowedIpsString);
    }

    /**
     * @return bool
     */
    public function clientHasAllowedIp()
    {
        return $this->clientHasAllowedIp;
    }
}
