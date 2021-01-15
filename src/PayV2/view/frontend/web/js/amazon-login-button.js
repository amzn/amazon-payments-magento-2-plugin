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
    'Amazon_PayV2/js/action/checkout-session-config-load',
    'Amazon_PayV2/js/model/storage',
    'mage/url',
    'Amazon_PayV2/js/amazon-checkout',
    'Magento_Customer/js/customer-data'
], function ($, checkoutSessionConfigLoad, amazonStorage, url, amazonCheckout, customerData) {
    'use strict';

    if (amazonStorage.isEnabled) {
        $.widget('amazon.AmazonLoginButton', {
            options: {
                payOnly: null,
                placement: 'Cart',
            },

            _loadButtonConfig: function (config, callback) {
                checkoutSessionConfigLoad(function (checkoutSessionConfig) {
                    callback({
                        merchantId: checkoutSessionConfig['merchant_id'],
                        ledgerCurrency: checkoutSessionConfig['currency'],
                        buttonColor: checkoutSessionConfig['button_color'],
                        checkoutLanguage: checkoutSessionConfig['language'],
                        productType: 'SignIn',
                        placement: this.options.placement,
                        sandbox: checkoutSessionConfig['sandbox'],
                        // configure sign in
                        signInConfig: {
                            payloadJSON: checkoutSessionConfig['login_payload'],
                            signature: checkoutSessionConfig['login_signature'],
                            publicKeyId: checkoutSessionConfig['public_key_id']
                        }
                    });
                }.bind(this));
            },

            /**
             * @param {boolean} isCheckoutSessionPayOnly
             * @returns {boolean}
             * @private
             */
            _isPayOnly: function (isCheckoutSessionPayOnly) {
                var result = isCheckoutSessionPayOnly;
                if (result && this.options.payOnly !== null) {
                    result = this.options.payOnly;
                }
                return result;
            },

            /**
             * Create button
             */
            _create: function (config) {
                var $buttonContainer = this.element;
                amazonCheckout.withAmazonCheckout(function (amazon, args) {
                    var $buttonRoot = $('<div></div>');
                    $buttonRoot.html('<img src="' + require.toUrl('images/loader-1.gif') + '" alt="" width="24" />');
                    $buttonContainer.empty().append($buttonRoot);
                    this._loadButtonConfig(config, function (buttonConfig) {
                        amazon.Pay.renderButton('#' + $buttonRoot.empty().uniqueId().attr('id'), buttonConfig);
                        $('.amazon-button-container-v2 .field-tooltip').fadeIn();
                        $('.login-with-amazon').click(function() { customerData.invalidate('*'); });
                    });
                }, this);
            },

            click: function () {
                this.element.children().first().trigger('click');
            }
        });

        return $.amazon.AmazonLoginButton;
    }
});
