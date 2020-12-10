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
namespace Amazon\Payment\Plugin;

use Amazon\Core\Helper\Data;
use Magento\Checkout\Model\Session;

/**
 * @deprecated As of February 2021, this Legacy Amazon Pay plugin has been
 * deprecated, in favor of a newer Amazon Pay version available through GitHub
 * and Magento Marketplace. Please download the new plugin for automatic
 * updates and to continue providing your customers with a seamless checkout
 * experience. Please see https://pay.amazon.com/help/E32AAQBC2FY42HS for details
 * and installation instructions.
 */
class CheckoutProcessor
{
    /**
     * @var Data
     */
    private $amazonHelper;

    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * CheckoutProcessor constructor.
     *
     * @param Data $amazonHelper
     * @param Session $checkoutSession
     */
    public function __construct(
        Data $amazonHelper,
        Session $checkoutSession
    ) {
        $this->amazonHelper = $amazonHelper;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * Checkout LayoutProcessor after process plugin.
     *
     * @param \Magento\Checkout\Block\Checkout\LayoutProcessor $processor
     * @param array $jsLayout
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterProcess(\Magento\Checkout\Block\Checkout\LayoutProcessor $processor, $jsLayout)
    {
        $quote = $this->checkoutSession->getQuote();

        $shippingConfig = &$jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']
        ['children']['shippingAddress'];
        $paymentConfig = &$jsLayout['components']['checkout']['children']['steps']['children']['billing-step']
        ['children']['payment'];

        if (!$quote->isVirtual() && $this->amazonHelper->isPwaEnabled()) {
            $paymentConfig['children']['payments-list']['component'] = 'Amazon_Payment/js/view/payment/list';
        } else {
            unset($shippingConfig['children']['customer-email']['children']['amazon-button-region']);
            unset($shippingConfig['children']['before-form']['children']['amazon-widget-address']);

            unset($paymentConfig['children']['renders']['children']['amazon_payment']);
            unset($paymentConfig['children']['beforeMethods']['children']['amazon-sandbox-simulator']);
            unset($paymentConfig['children']['payments-list']['children']['amazon_payment-form']);
        }

        return $jsLayout;
    }
}
