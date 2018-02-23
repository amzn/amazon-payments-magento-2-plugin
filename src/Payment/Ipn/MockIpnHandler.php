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
namespace Amazon\Payment\Ipn;

use AmazonPay\IpnHandler as AmazonIpnHandler;

/**
 * Class MockIpnHandler
 *
 * Mock IPN Handler for use with Behat Tests, skips signature verification
 */
class MockIpnHandler extends AmazonIpnHandler
{
    /**
     * @var \ReflectionClass
     */
    private $parent;

    /**
     * MockIpnHandler constructor.
     *
     * @param array      $requestHeaders
     * @param string     $requestBody
     * @param null|array $ipnConfig
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct($requestHeaders, $requestBody, $ipnConfig = null)
    {
        $reflection   = new \ReflectionClass($this);
        $this->parent = $reflection->getParentClass();

        $bodyProperty = $this->parent->getProperty('body');
        $bodyProperty->setAccessible(true);
        $bodyProperty->setValue($this, $requestBody);

        $this->getMessage();
    }

    private function getMessage()
    {
        $method = $this->parent->getMethod('getMessage');
        $method->setAccessible(true);
        $method->invoke($this);
    }
}
