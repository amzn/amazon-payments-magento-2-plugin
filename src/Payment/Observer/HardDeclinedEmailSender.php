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
namespace Amazon\Payment\Observer;

use Amazon\Payment\Helper\Email;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class HardDeclinedEmailSender implements ObserverInterface
{
    /**
     * @var Email
     */
    private $emailHelper;

    /**
     * SoftDeclinedEmailSender constructor.
     *
     * @param Email $emailHelper
     */
    public function __construct(Email $emailHelper)
    {
        $this->emailHelper = $emailHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(Observer $observer)
    {
        $this->emailHelper->sendAuthorizationHardDeclinedEmail($observer->getOrder());
    }
}
