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
define([
    'jquery',
    'Magento_Customer/js/customer-data',
    'Amazon_PayV2/js/model/amazon-payv2-config',
    'Amazon_PayV2/js/model/storage',
    'mage/url',
    'Amazon_PayV2/js/amazon-checkout',
], function ($, customerData, amazonPayV2Config, amazonStorage, url, amazonCheckout) {
    'use strict';

    if (amazonStorage.isEnabled) {
        $.widget('amazon.AmazonButton', {
            options: {
                payOnly: false,
                placement: amazonPayV2Config.getValue('placement'),
                selector: '.amazon-checkout-button'
            },

            /**
             * Create button
             */
            _create: function () {
                amazonCheckout.withAmazonCheckout(function(amazon, args) {
                    amazon.Pay.renderButton(this.options.selector, {
                        merchantId: amazonPayV2Config.getValue('merchantId'),
                        createCheckoutSession: {
                            url: url.build('amazon_payv2/checkout/createSession'),
                            method: 'PUT'
                        },
                        ledgerCurrency: amazonPayV2Config.getValue('currency'),
                        checkoutLanguage: amazonPayV2Config.getValue('language'),
                        productType: this.options.payOnly ? 'PayOnly' : 'PayAndShip',
                        placement: this.options.placement,
                        sandbox: amazonPayV2Config.getValue('sandbox'),
                    });
                    $('.amazon-button-container-v2 .field-tooltip').fadeIn();
                }, this);
            }
        });

        return $.amazon.AmazonButton;
    }
});
