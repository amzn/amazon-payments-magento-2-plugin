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
namespace Amazon\Alexa\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class SalesOrderShipmentTrackAfter implements ObserverInterface
{
    /**
     * @var \Amazon\Alexa\Model\AlexaConfig
     */
    private $alexaConfig;

    /**
     * @var \Amazon\Core\Model\AmazonConfig
     */
    private $amazonConfig;

    /**
     * @var \Amazon\Alexa\Model\Alexa
     */
    private $alexaModel;

    /**
     * SalesOrderShipmentTrackAfter constructor.
     * @param \Amazon\Alexa\Model\AlexaConfig $alexaConfig
     * @param \Amazon\Core\Model\AmazonConfig $amazonConfig
     * @param \Amazon\Alexa\Model\Alexa $alexaModel
     */
    public function __construct(
        \Amazon\Alexa\Model\AlexaConfig $alexaConfig,
        \Amazon\Core\Model\AmazonConfig $amazonConfig,
        \Amazon\Alexa\Model\Alexa $alexaModel
    ) {
        $this->alexaConfig  = $alexaConfig;
        $this->amazonConfig = $amazonConfig;
        $this->alexaModel   = $alexaModel;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(Observer $observer)
    {
        if (!$this->amazonConfig->isPwaEnabled() || !$this->alexaConfig->isAlexaEnabled()) {
            return;
        }

        /** @var \Magento\Sales\Model\Order\Shipment\Track $track */
        $track = $observer->getEvent()->getTrack();

        if ($track->getShipment()->getOrder()->getPayment()->getMethod() == 'amazon_payment') {
            $this->alexaModel->addDeliveryNotification($track);
        }
    }
}
