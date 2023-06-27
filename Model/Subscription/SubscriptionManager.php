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

class SubscriptionManager
{
    /**
     * @var \Amazon\Pay\Model\Subscription\SubscriptionManagerInterface
     */
    protected $manager;

    /**
     * @param \Amazon\Pay\Model\Subscription\SubscriptionManagerFactory $subscriptionFactory
     */
    public function __construct(\Amazon\Pay\Model\Subscription\SubscriptionManagerFactory $subscriptionFactory)
    {
        $this->manager = $subscriptionFactory->initialize();
    }

    public function hasSubscription($quote)
    {
        return $this->manager->hasSubscription($quote);
    }

    public function getFrequencyUnit($item)
    {
        $unit = $this->manager->getFrequencyUnit($item);
        if ($unit) {
            $unit = ucfirst($unit);
        }
        return $unit;
    }

    public function getFrequencyCount($item)
    {
        return $this->manager->getFrequencyCount($item);
    }

    public function isSubscription($item)
    {
        return $this->manager->isSubscription($item);
    }

    public function cancel($order, $subscription = false)
    {
        return $this->manager->cancel($order, $subscription);
    }

    public function getSubscriptionLabel()
    {
        return $this->manager->getSubscriptionLabel();
    }

    public function save($subscription)
    {
        return $this->manager->save($subscription);
    }

    public function getList($searchCriteria)
    {
        return $this->manager->getList($searchCriteria);
    }
}
