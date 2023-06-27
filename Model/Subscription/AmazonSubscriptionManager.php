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

class AmazonSubscriptionManager implements SubscriptionManagerInterface
{

    /**
     * @inheritdoc
     */
    public function hasSubscription($quote)
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function getFrequencyUnit($item)
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function getFrequencyCount($item)
    {
        return 0;
    }

    /**
     * @inheritdoc
     */
    public function isSubscription($item)
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function cancel($order, $subscription = false)
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function getSubscriptionLabel()
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function save($subscription)
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function getList($searchCriteria)
    {
        return false;
    }
}
