<?php
/**
 * Copyright 2020 Amazon.com, Inc. or its affiliates. All Rights Reserved.
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

use Magento\Framework\Phrase;
use Magento\Sales\Model\Order\Payment;
use Amazon\Pay\Gateway\Config\Config;

class OrderCurrencyComment
{
    /**
     * Format currency to match payment currency from invoice or credit memo
     *
     * @param Payment $subject
     * @param mixed $messagePrependTo
     * @return array|null
     */
    public function beforePrependMessage(Payment $subject, $messagePrependTo)
    {
        if ($subject->getMethod() == Config::CODE) {
            $order = $subject->getOrder();
            if ($order->getBaseCurrencyCode() != $order->getOrderCurrencyCode()) {
                if ($subject->getOrder()->getPayment()->getCreditmemo()) {
                    $displayCurrencyAmount = $subject->getCreditmemo()->getGrandTotal();
                } else {
                    $displayCurrencyAmount = $subject->getOrder()->getPayment()->getAmazonDisplayInvoiceAmount() ?:
                        $subject->getAmountOrdered();
                }
                $messagePrependTo = __(
                    $messagePrependTo->getText(),
                    $order->getBaseCurrency()
                        ->formatTxt($messagePrependTo->getArguments()[0]) .' ['.
                        $order->formatPriceTxt($displayCurrencyAmount) .']'
                );

                return [$messagePrependTo];
            }
        }

        return null;
    }

    /**
     * Handle currency code differences on voids/cancels
     *
     * @param Payment $subject
     * @param string $result
     * @return string
     */
    public function afterFormatPrice(Payment $subject, $result)
    {
        if ($subject->getMethod() == Config::CODE) {
            $order = $subject->getOrder();
            if ($order->getBaseCurrencyCode() != $order->getOrderCurrencyCode() &&
                (($subject->getMessage() instanceof Phrase
                && $subject->getMessage()->getText() == 'Canceled order online')
                || strpos($subject->getTransactionId(), '-void') !== false)
            ) {
                return $result .' ['. $order->formatPriceTxt($subject->getAmountOrdered()) .']';
            }
        }

        return $result;
    }
}
