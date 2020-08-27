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
namespace Amazon\PayV2\Plugin;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Model\Order\Payment\State\AuthorizeCommand as SalesAuthorizeCommand;
use Magento\Framework\Phrase;
use Amazon\PayV2\Gateway\Config\Config;

/**
 * Class AuthorizeCommand
 * @package Amazon\PayV2\Plugin
 */
class AuthorizeCommand
{
    /**
     * @param SalesAuthorizeCommand $subject
     * @param Phrase $result
     * @param OrderPaymentInterface $payment
     * @param $amount
     * @param OrderInterface $order
     * @return Phrase
     */
    public function afterExecute(
        SalesAuthorizeCommand $subject,
        Phrase $result,
        OrderPaymentInterface $payment,
        $amount,
        OrderInterface $order
    )
    {
        if ($payment->getMethod() == Config::CODE) {
            if ($order->getBaseCurrencyCode() != $order->getOrderCurrencyCode()) {
                $result = __(
                    $result->getText(),
                    $order->getBaseCurrency()->formatTxt($amount) .' ['. $order->formatPriceTxt($payment->getAmountOrdered()) .']'
                );
            }
        }

        return $result;
    }
}
