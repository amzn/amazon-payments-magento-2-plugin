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

namespace Amazon\Pay\Observer;

class SalesOrderShipmentTrackAfter implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Amazon\Pay\Model\Alexa
     */
    private $alexaModel;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    private $messageManager;

    public function __construct(
        \Amazon\Pay\Model\Alexa $alexaModel,
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        $this->alexaModel = $alexaModel;
        $this->messageManager = $messageManager;
    }

    /**
     * @inheritDoc
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $track = $observer->getEvent()->getTrack();
        /* @var $track \Magento\Sales\Model\Order\Shipment\Track */
        $shipment = $track->getShipment();
        try {
            $details = $this->alexaModel->addDeliveryNotification($track);
            if ($details) {
                $message = __(
                    'Amazon Pay has received shipping tracking information for carrier code %1 and tracking number %2',
                    $details['carrierCode'],
                    $details['trackingNumber']
                );
                $shipment->addComment($message)->save();
                $this->messageManager->addSuccessMessage($message);
            }
        } catch (\Exception $e) {
            $this->messageManager->addWarningMessage(__(
                'Alexa Notification: %1',
                $e->getMessage()
            ));
        }
    }
}
