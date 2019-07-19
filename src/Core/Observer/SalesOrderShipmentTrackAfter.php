<?php
/**
 * Copyright 2016 Amazon.com, Inc. or its affiliates. All Rights Reserved.
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
namespace Amazon\Core\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class SalesOrderShipmentTrackAfter implements ObserverInterface
{
    /**
     * @var \Amazon\Core\Helper\Data
     */
    private $coreHelper;

    /**
     * @var \Amazon\Core\Model\Alexa
     */
    private $alexa;

    /**
     * SalesOrderShipmentTrackAfter constructor.
     * @param \Amazon\Core\Helper\Data $coreHelper
     * @param \Amazon\Core\Model\Alexa $alexa
     */
    public function __construct(
        \Amazon\Core\Helper\Data $coreHelper,
        \Amazon\Core\Model\Alexa $alexa
    ) {
        $this->coreHelper = $coreHelper;
        $this->alexa      = $alexa;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(Observer $observer)
    {
        if (!$this->coreHelper->isPwaEnabled() || !$this->coreHelper->isAlexaEnabled()) {
            return;
        }

        /** @var \Magento\Sales\Model\Order\Shipment\Track $track */
        $track = $observer->getEvent()->getTrack();

        if ($track->getShipment()->getOrder()->getPayment()->getMethod() == 'amazon_payment') {
            $this->alexa->addDeliveryNotification($track);
        }
    }
}
