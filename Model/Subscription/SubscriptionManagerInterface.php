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

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Sales\Api\Data\OrderInterface;

interface SubscriptionManagerInterface
{

    /**
     * Check if any of the quote items is a subscription
     *
     * @param CartRepositoryInterface $quote
     * @return mixed
     */
    public function hasSubscription($quote);

    /**
     * Get frequency unit
     *
     * @param CartItemInterface $item
     * @return mixed
     */
    public function getFrequencyUnit($item);

    /**
     * Get frequency count
     *
     * @param CartItemInterface $item
     * @return mixed
     */
    public function getFrequencyCount($item);

    /**
     * Is cart item a subscription
     *
     * @param CartItemInterface $item
     * @return bool
     */
    public function isSubscription($item);

    /**
     * Cancel order subscription
     *
     * @param OrderInterface $order
     * @param mixed $subscription
     * @return mixed
     */
    public function cancel($order, $subscription = false);

    /**
     * Get subscription label
     *
     * @return mixed
     */
    public function getSubscriptionLabel();

    /**
     * Save subscription
     *
     * @param \ParadoxLabs\Subscriptions\Api\Data\SubscriptionInterface $subscription
     * @return mixed
     */
    public function save($subscription);

    /**
     * Get subscription list
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return mixed
     */
    public function getList($searchCriteria);
}
