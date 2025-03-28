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
namespace Amazon\Pay\Plugin;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Magento\Sales\Model\Order\Payment;
use Amazon\Pay\Gateway\Config\Config;

class SendEmail
{
    /**
     * @var OrderSender
     */
    private $orderSender;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * SendEmail constructor
     *
     * @param OrderSender $orderSender
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        OrderSender $orderSender,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->orderSender = $orderSender;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Send order confirmation email once state moves to Processing
     *
     * @param Order $subject
     * @param Order $result
     * @param string $state
     */
    public function afterSetState(Order $subject, Order $result, string $state)
    {
        if ($subject->getPayment()?->getMethod() == Config::CODE) {
            if ($this->scopeConfig->getValue('sales_email/order/enabled')) {
                $subject->setCanSendNewEmailFlag(false);

                if ($subject->getState() == Order::STATE_PROCESSING
                    && !empty($subject->getStatusHistories())
                    && !$subject->getEmailSent())
                {
                    $subject->setCanSendNewEmailFlag(true);
                    $this->orderSender->send($subject);
                }
            }
        }

        return $result;
    }
}
