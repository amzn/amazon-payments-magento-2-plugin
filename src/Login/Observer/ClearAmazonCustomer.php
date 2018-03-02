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
namespace Amazon\Login\Observer;

use Amazon\Login\Helper\Session as SessionHelper;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class ClearAmazonCustomer implements ObserverInterface
{
    /**
     * @var SessionHelper
     */
    private $sessionHelper;

    /**
     * @param SessionHelper $sessionHelper
     */
    public function __construct(SessionHelper $sessionHelper)
    {
        $this->sessionHelper = $sessionHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(Observer $observer)
    {
        $this->sessionHelper->clearAmazonCustomer();
    }
}
