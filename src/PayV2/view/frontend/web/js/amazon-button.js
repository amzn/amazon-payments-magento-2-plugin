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
                payOnly: null,
                forcePayOnly: false,
                placement: amazonPayV2Config.getValue('placement'),
            },

            /**
             * @returns {boolean}
             * @private
             */
            _isPayOnly: function () {
                var result = this.options.forcePayOnly || amazonStorage.isPayOnly(true);
                if (result && this.options.payOnly !== null) {
                    result = this.options.payOnly;
                }
                return result;
            },

            /**
             * Create button
             */
            _create: function () {
                var $buttonContainer = this.element;
                amazonCheckout.withAmazonCheckout(function (amazon, args) {
                    var buttonPreferences = {
                            merchantId: amazonPayV2Config.getValue('merchantId'),
                            createCheckoutSession: {
                                url: url.build('amazon_payv2/checkout/createSession'),
                                method: 'PUT'
                            },
                            ledgerCurrency: amazonPayV2Config.getValue('currency'),
                            checkoutLanguage: amazonPayV2Config.getValue('language'),
                            productType: this._isPayOnly() ? 'PayOnly' : 'PayAndShip',
                            placement: this.options.placement,
                            sandbox: amazonPayV2Config.getValue('sandbox'),
                        },
                        buttonPreferencesJson = JSON.stringify(buttonPreferences);
                    if ($buttonContainer.data('button-preferences') !== buttonPreferencesJson) {
                        $buttonContainer.empty();
                        $buttonContainer.data('button-preferences', buttonPreferencesJson);

                        var $buttonRoot = $('<div></div>');
                        $buttonRoot.uniqueId();
                        $buttonContainer.append($buttonRoot);

                        amazon.Pay.renderButton('#' + $buttonRoot.attr('id'), buttonPreferences);
                        $('.amazon-button-container-v2 .field-tooltip').fadeIn();
                    }
                }, this);
            },

            click: function () {
                this.element.children().first().trigger('click');
            }
        });

        return $.amazon.AmazonButton;
    }
});
