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

use Amazon\Core\Helper\Data;
use Magento\Framework\ObjectManagerInterface;
use AmazonPay\IpnHandlerInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;

/**
 * @deprecated As of February 2021, this Legacy Amazon Pay plugin has been
 * deprecated, in favor of a newer Amazon Pay version available through GitHub
 * and Magento Marketplace. Please download the new plugin for automatic
 * updates and to continue providing your customers with a seamless checkout
 * experience. Please see https://pay.amazon.com/help/E32AAQBC2FY42HS for details
 * and installation instructions.
 */
class IpnHandlerFactory implements IpnHandlerFactoryInterface
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var string
     */
    private $instanceName;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Data
     */
    private $coreHelper;

    public function __construct(
        ObjectManagerInterface $objectManager,
        LoggerInterface $logger,
        Data $coreHelper,
        $instanceName = '\\AmazonPay\\IpnHandlerInterface'
    ) {
        $this->objectManager      = $objectManager;
        $this->instanceName       = $instanceName;
        $this->logger             = $logger;
        $this->coreHelper         = $coreHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function create($headers, $body)
    {
        $handler = $this->objectManager->create(
            $this->instanceName,
            ['requestHeaders' => $headers, 'requestBody' => $body]
        );

        if ($handler instanceof LoggerAwareInterface && $this->coreHelper->isLoggingEnabled()) {
            $handler->setLogger($this->logger);
        }

        return $handler;
    }
}
