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

namespace Amazon\Pay\Model;

use Magento\Sales\Api\Data\OrderInterface;

class AsyncManagement
{
    public const ACTION_AUTH   = 'authorization';
    public const ACTION_REFUND = 'refund';

    /**
     * @var \Amazon\Pay\Api\Data\AsyncInterfaceFactory
     */
    private $asyncFactory;

    /**
     * AsyncManagement constructor
     *
     * @param \Amazon\Pay\Api\Data\AsyncInterfaceFactory $asyncFactory
     */
    public function __construct(
        \Amazon\Pay\Api\Data\AsyncInterfaceFactory $asyncFactory
    ) {
        $this->asyncFactory = $asyncFactory;
    }

    /**
     * Queue pending authorization for async processing.
     *
     * @param string $pendingId (chargePermissionId)
     * @return void
     */
    public function queuePendingAuthorization($pendingId)
    {
        $this->asyncFactory->create()
            ->setPendingId($pendingId)
            ->setPendingAction(self::ACTION_AUTH)
            ->save();
    }

    /**
     * Queue pending refund for async processing.
     *
     * @param int $orderId
     * @param string $pendingId (refundId)
     * @return void
     */
    public function queuePendingRefund($orderId, $pendingId)
    {
        $this->asyncFactory->create()
            ->setOrderId($orderId)
            ->setPendingId($pendingId)
            ->setPendingAction(self::ACTION_REFUND)
            ->save();
    }
}
