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

namespace Amazon\Pay\Model\Subscription;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Module\Manager as ModuleManager;

class SubscriptionDependenceManagementFactory
{
    const PARADOX_CONFIG = 'ParadoxLabs\Subscriptions\Model\Config';

    const PARADOX_REPOSITORY = 'ParadoxLabs\Subscriptions\Model\SubscriptionRepository';
    
    /**
     * @var ModuleManager
     */
    private $moduleManager;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @param ModuleManager $moduleManager
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(
        ModuleManager $moduleManager,
        ObjectManagerInterface $objectManager
    ) {
        $this->moduleManager = $moduleManager;
        $this->objectManager = $objectManager;
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function create($data = [])
    {
        if ($this->moduleManager->isEnabled('ParadoxLabs_AdaptiveSubscriptions') && $data['instanceName']) {
            return $this->objectManager->create($data['instanceName']);
        }

        return null;
    }
 }
