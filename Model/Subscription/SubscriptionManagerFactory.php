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

class SubscriptionManagerFactory
{
    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @var \Magento\Framework\ObjectManagerInterface $objectManager
     */
    protected $objectManager;

    /**
     * @var array
     */
    protected $subscriptionManagerPool;

    /**
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param array $subscriptionManagerPool
     */
    public function __construct(
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        array $subscriptionManagerPool = []
    ) {
        $this->moduleManager = $moduleManager;
        $this->objectManager = $objectManager;
        $this->subscriptionManagerPool = $subscriptionManagerPool;
    }

    public function initialize(array $data = [])
    {
        $manager = false;
        foreach($this->subscriptionManagerPool as $vendor => $subscriptionManager) {
            if ($vendor != 'default') {
                if ($this->moduleManager->isEnabled($subscriptionManager['module_name'])) {
                    $manager = $this->objectManager->create($subscriptionManager['module_manager'], $data);
                    foreach($subscriptionManager['module_classes'] as $name => $instance) {
                        $manager->{$name} =  $this->objectManager->create($instance);
                    }
                }
            }
        }

        if (!$manager) {
            $moduleManager = $this->subscriptionManagerPool['default']['module_manager'];
            $manager = $this->objectManager->create($moduleManager, $data);
        }
        return $manager;
    }
}