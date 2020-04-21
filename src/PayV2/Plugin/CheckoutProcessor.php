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

class CheckoutProcessor
{
    /**
     * @var \Amazon\PayV2\Model\AmazonConfig
     */
    private $amazonConfig;

    /**
     * @var \Amazon\PayV2\Helper\Data
     */
    private $amazonHelper;

    /**
     * @var  \Magento\Checkout\Model\Session
     */
    private $checkoutSession;

    /**
     * CheckoutProcessor constructor.
     * @param \Amazon\PayV2\Model\AmazonConfig $amazonConfig
     * @param \Amazon\PayV2\Helper\Data $amazonHelper
     * @param \Magento\Checkout\Model\Session $checkoutSession
     */
    public function __construct(
        \Amazon\PayV2\Model\AmazonConfig $amazonConfig,
        \Amazon\PayV2\Helper\Data $amazonHelper,
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        $this->amazonConfig = $amazonConfig;
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
        $shippingConfig = &$jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']
        ['children']['shippingAddress'];
        $paymentConfig = &$jsLayout['components']['checkout']['children']['steps']['children']['billing-step']
        ['children']['payment'];

        if ($this->amazonConfig->isEnabled()) {
            $shippingConfig['component'] = 'Amazon_PayV2/js/view/shipping';
            $shippingConfig['children']['customer-email']['component'] = 'Amazon_PayV2/js/view/form/element/email';
            $shippingConfig['children']['address-list']['component'] = 'Amazon_PayV2/js/view/shipping-address/list';
            $shippingConfig['children']['address-list']['rendererTemplates']['new-customer-address']
            ['component'] = 'Amazon_PayV2/js/view/shipping-address/address-renderer/default';

            $paymentConfig['children']['payments-list']['component'] = 'Amazon_PayV2/js/view/payment/list';
            $paymentConfig['children']['payments-list']['children'][\Amazon\PayV2\Gateway\Config\Config::CODE . '-form']['component'] = 'Amazon_PayV2/js/view/billing-address';

            unset($paymentConfig['children']['renders']['children']['amazonlogin']); // legacy
        } else {
            unset($shippingConfig['children']['customer-email']['children']['amazon-payv2-button-region']);
            unset($shippingConfig['children']['before-form']['children']['amazon-payv2-address']);
            unset($paymentConfig['children']['renders']['children']['amazon_payment_v2-method']);
        }

        return $jsLayout;
    }
}
