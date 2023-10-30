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

class ParadoxLabsSubscriptionManager implements SubscriptionManagerInterface
{

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * ParadoxLabsSubscriptionManager constructor
     *
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * Check quote items for subscription
     *
     * @param CartInterface $quote
     * @return bool
     */
    public function hasSubscription($quote)
    {
        return $this->quoteManager->quoteContainsSubscription($quote);
    }

    /**
     * Get frequency unit
     *
     * @param CartItemInterface $item
     * @return mixed
     */
    public function getFrequencyUnit($item)
    {
        return $this->itemManager->getFrequencyUnit($item);
    }

    /**
     * Get frequency count
     *
     * @param CartItemInterface $item
     * @return mixed
     */
    public function getFrequencyCount($item)
    {
        return $this->itemManager->getFrequencyCount($item);
    }

    /**
     * Is cart item a subscription
     *
     * @param CartItemInterface $item
     * @return bool
     */
    public function isSubscription($item)
    {
        return $this->itemManager->isSubscription($item);
    }

    /**
     * Cancel order subscription
     *
     * @param OrderInterface $order
     * @param SubscriptionInterface $subscription
     * @return void
     */
    public function cancel($order, $subscription = false)
    {
        $this->searchCriteriaBuilder->addFilter('keyword_fulltext', '%' . $order->getIncrementId(), 'like');
        $subscriptions = $this->subscriptionRepository->getList($this->searchCriteriaBuilder->create())->getItems();
        if (!empty($subscriptions)) {
            $subscription = array_shift($subscriptions);
            $subscription->setStatus('canceled');
            $this->subscriptionRepository->save($subscription);
        }
    }

    /**
     * Get subscription label
     *
     * @return mixed
     */
    public function getSubscriptionLabel()
    {
        return $this->subscriptionConfig->getSubscriptionLabel();
    }

    /**
     * Save subscription
     *
     * @param SubscriptionInterface $subscription
     * @return mixed
     */
    public function save($subscription)
    {
        return $this->subscriptionRepository->save($subscription);
    }

    /**
     * Get subscription list
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return mixed
     */
    public function getList($searchCriteria)
    {
        return $this->subscriptionRepository->getList($searchCriteria);
    }
}
