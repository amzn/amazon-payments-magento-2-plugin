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
namespace Amazon\Core\Client;

use Amazon\Core\Model\AmazonConfig;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\ScopeInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;

class ClientFactory implements ClientFactoryInterface
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var AmazonConfig
     */
    private $amazonConfig;

    /**
     * @var string
     */
    private $instanceName;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * ClientFactory constructor.
     *
     * @param ObjectManagerInterface $objectManager
     * @param AmazonConfig           $amazonConfig
     * @param LoggerInterface        $logger
     * @param string                 $instanceName
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        AmazonConfig $amazonConfig,
        LoggerInterface $logger,
        $instanceName = '\\AmazonPay\\ClientInterface'
    ) {
        $this->objectManager = $objectManager;
        $this->amazonConfig  = $amazonConfig;
        $this->instanceName  = $instanceName;
        $this->logger        = $logger;
    }

    /**
     * {@inheritDoc}
     */
    public function create($scopeId = null, $scope = ScopeInterface::SCOPE_STORE)
    {
        $config = [
            $this->amazonConfig->getClientPath('secretkey')  => $this->amazonConfig->getSecretKey($scope, $scopeId),
            $this->amazonConfig->getClientPath('accesskey')  => $this->amazonConfig->getAccessKey($scope, $scopeId),
            $this->amazonConfig->getClientPath('merchantid') => $this->amazonConfig->getMerchantId($scope, $scopeId),
            $this->amazonConfig->getClientPath('amazonregion')     => $this->amazonConfig->getRegion($scope, $scopeId),
            $this->amazonConfig->getClientPath('amazonsandbox')    => $this->amazonConfig->isSandboxEnabled($scope, $scopeId),
            $this->amazonConfig->getClientPath('clientid')   => $this->amazonConfig->getClientId($scope, $scopeId)
        ];

        $client = $this->objectManager->create($this->instanceName, ['amazonConfig' => $config]);

        if ($client instanceof LoggerAwareInterface && $this->amazonConfig->isLoggingEnabled($scope, $scopeId)) {
            $client->setLogger($this->logger);
        }

        return $client;
    }
}
