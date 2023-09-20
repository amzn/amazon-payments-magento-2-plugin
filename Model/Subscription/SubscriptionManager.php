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

use Magento\Framework\Api\Search\SearchCriteriaInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Sales\Api\Data\OrderInterface;
use ParadoxLabs\Subscriptions\Api\Data\SubscriptionInterface;

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

    /**
     * Check if any of the quote items is a subscription
     *
     * @param CartInterface $quote
     * @return bool
     */
    public function hasSubscription($quote)
    {
        return $this->manager->hasSubscription($quote);
    }

    /**
     * Get frequency unit
     *
     * @param CartItemInterface $item
     * @return mixed
     */
    public function getFrequencyUnit($item)
    {
        $unit = $this->manager->getFrequencyUnit($item);
        if ($unit) {
            $unit = ucfirst($unit);
        }
        return $unit;
    }

    /**
     * Get frequency count
     *
     * @param CartItemInterface $item
     * @return mixed
     */
    public function getFrequencyCount($item)
    {
        return $this->manager->getFrequencyCount($item);
    }

    /**
     * Is quote item a subscription
     *
     * @param CartItemInterface $item
     * @return bool
     */
    public function isSubscription($item)
    {
        return $this->manager->isSubscription($item);
    }

    /**
     * Cancel an order subscription
     *
     * @param OrderInterface $order
     * @param SubscriptionInterface $subscription
     * @return mixed
     */
    public function cancel($order, $subscription = false)
    {
        return $this->manager->cancel($order, $subscription);
    }

    /**
     * Get subscription label
     *
     * @return mixed
     */
    public function getSubscriptionLabel()
    {
        return $this->manager->getSubscriptionLabel();
    }

    /**
     * Save subscription
     *
     * @param SubscriptionInterface $subscription
     * @return mixed
     */
    public function save($subscription)
    {
        return $this->manager->save($subscription);
    }

    /**
     * Get subscription list
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return mixed
     */
    public function getList($searchCriteria)
    {
        return $this->manager->getList($searchCriteria);
    }
}
