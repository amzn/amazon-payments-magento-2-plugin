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

class AsyncUpdater
{
    /**
     * @var AsyncManagement\ChargeFactory
     */
    private $chargeFactory;

    /**
     * @var AsyncManagement\RefundFactory
     */
    private $refundFactory;

    /**
     * @var \Amazon\Pay\Api\Data\AsyncInterfaceFactory
     */
    private $asyncFactory;

    /**
     * @var \Magento\Framework\Notification\NotifierInterface
     */
    private $adminNotifier;

    /**
     * @var \Amazon\Pay\Logger\AsyncIpnLogger
     */
    private $asyncLogger;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * AsyncUpdater constructor
     *
     * @param \Amazon\Pay\Model\AsyncManagement\ChargeFactory $chargeFactory
     * @param \Amazon\Pay\Model\AsyncManagement\RefundFactory $refundFactory
     * @param \Amazon\Pay\Api\Data\AsyncInterfaceFactory $asyncFactory
     * @param \Magento\Framework\Notification\NotifierInterface $adminNotifier
     * @param \Amazon\Pay\Logger\AsyncIpnLogger $asyncLogger
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Amazon\Pay\Model\AsyncManagement\ChargeFactory $chargeFactory,
        \Amazon\Pay\Model\AsyncManagement\RefundFactory $refundFactory,
        \Amazon\Pay\Api\Data\AsyncInterfaceFactory $asyncFactory,
        \Magento\Framework\Notification\NotifierInterface $adminNotifier,
        \Amazon\Pay\Logger\AsyncIpnLogger $asyncLogger,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->chargeFactory = $chargeFactory;
        $this->refundFactory = $refundFactory;
        $this->asyncFactory = $asyncFactory;
        $this->adminNotifier = $adminNotifier;
        $this->asyncLogger = $asyncLogger;
        $this->logger = $logger;
    }

    /**
     * Process pending transactions
     *
     * @param Async $async
     * @return void
     */
    public function processPending($async)
    {
        try {
            $async->getResource()->beginTransaction();
            $async->setLockOnLoad(true);
            $asyncComplete = false;

            switch ($async->getPendingAction()) {
                case AsyncManagement::ACTION_AUTH:
                    $asyncComplete = $this->chargeFactory->create()->processStateChange($async->getPendingId());
                    break;
                case AsyncManagement::ACTION_REFUND:
                    $asyncComplete = $this->refundFactory->create()->processRefund($async->getPendingId());
                    break;
            }

            if ($asyncComplete) {
                $this->completePending($async);
            }

            $async->getResource()->commit();
        } catch (\Exception $e) {
            $this->logger->error($e);
            $this->asyncLogger->error($e);
            $async->getResource()->rollBack();
        }
    }

    /**
     * Complete successful async pending action
     *
     * @param Async $async
     * @return void
     */
    protected function completePending($async)
    {
        $async->setIsPending(false)->save();
    }
}
