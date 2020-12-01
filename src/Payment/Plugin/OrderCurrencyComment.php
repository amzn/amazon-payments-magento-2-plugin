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
namespace Amazon\Payment\Plugin;

use Magento\Framework\Phrase;
use Magento\Sales\Model\Order\Payment;
use Amazon\Payment\Gateway\Config\Config;

/**
 * Class OrderCurrencyComment
 * @package Amazon\Payment\Plugin
 *
 * @deprecated As of February 2021, this Legacy Amazon Pay plugin has been
 * deprecated, in favor of a newer Amazon Pay version available through GitHub
 * and Magento Marketplace. Please download the new plugin for automatic
 * updates and to continue providing your customers with a seamless checkout
 * experience. Please see https://pay.amazon.com/help/E32AAQBC2FY42HS for details
 * and installation instructions.
 */
class OrderCurrencyComment
{
    /**
     * @param Payment $subject
     * @param $messagePrependTo
     * @return array|null
     */
    public function beforePrependMessage(Payment $subject, $messagePrependTo)
    {
        if ($subject->getMethod() == Config::CODE) {
            $order = $subject->getOrder();
            if ($order->getBaseCurrencyCode() != $order->getOrderCurrencyCode()) {
                if ($subject->getOrder()->getPayment()->getCreditmemo()) {
                    $displayCurrencyAmount = $subject->getCreditmemo()->getGrandTotal();
                }
                else {
                    $displayCurrencyAmount = $subject->getOrder()->getPayment()->getAmazonDisplayInvoiceAmount() ?: $subject->getAmountOrdered();
                }
                $messagePrependTo = __(
                    $messagePrependTo->getText(),
                    $order->getBaseCurrency()
                        ->formatTxt($messagePrependTo->getArguments()[0]) .' ['. $order->formatPriceTxt($displayCurrencyAmount) .']'
                );

                return [$messagePrependTo];
            }
        }

        return null;
    }

    /**
     * @param Payment $subject
     * @param $result
     * @return string
     */
    public function afterFormatPrice(Payment $subject, $result)
    {
        if ($subject->getMethod() == Config::CODE) {
            $order = $subject->getOrder();
            if (($order->getBaseCurrencyCode() != $order->getOrderCurrencyCode()
                && $subject->getMessage() instanceof Phrase
                && $subject->getMessage()->getText() == 'Canceled order online')
                || strpos($subject->getTransactionId(), '-void') !== FALSE
            ) {
                return $result .' ['. $order->formatPriceTxt($subject->getCreditmemo()->getGrandTotal()) .']';
            }
        }

        return $result;
    }
}
