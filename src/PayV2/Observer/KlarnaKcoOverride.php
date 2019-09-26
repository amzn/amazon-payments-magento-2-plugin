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
namespace Amazon\PayV2\Observer;

use Magento\Framework\Event\Observer;

class KlarnaKcoOverride implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Amazon\PayV2\Model\AmazonConfig
     */
    private $amazonConfig;

    /**
     * @var \Amazon\Login\Helper\Session
     */
    private $sessionHelper;

    /**
     * KlarnaKcoOverride constructor.
     * @param \Amazon\PayV2\Model\AmazonConfig $amazonConfig
     * @param \Amazon\Login\Helper\Session $sessionHelper
     */
    public function __construct(
        \Amazon\PayV2\Model\AmazonConfig $amazonConfig,
        \Amazon\Login\Helper\Session $sessionHelper
    ) {
        $this->amazonConfig = $amazonConfig;
        $this->sessionHelper = $sessionHelper;
    }

    public function execute(Observer $observer)
    {
        if ($this->amazonConfig->isEnabled() && $this->sessionHelper->isAmazonLoggedIn()) {
            // Force customer to use default (Amazon) checkout
            $observer->getOverrideObject()->setForceDisabled(true);
        }
    }
}
