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
    'ko',
    'jquery',
    'Amazon_Pay/js/action/checkout-session-config-load',
    'Amazon_Pay/js/model/storage',
    'mage/url',
    'Amazon_Pay/js/amazon-checkout',
    'Magento_Customer/js/customer-data',
    'Magento_Checkout/js/model/payment/additional-validators',
    'mage/storage',
    'Magento_Checkout/js/model/error-processor'
], function (
        ko,
        $,
        checkoutSessionConfigLoad,
        amazonStorage,
        url,
        amazonCheckout,
        customerData,
        additionalValidators,
        storage,
        errorProcessor
    ) {
    'use strict';

    $.widget('amazon.AmazonButton', {
        options: {
            payOnly: null,
            placement: 'Cart',
            hideIfUnavailable: '',
            buttonType: 'Normal',
            isIosc: ko.observable($('button.iosc-place-order-button').length > 0)
        },

        drawing: false,

        _loadButtonConfig: function (callback, forceReload = false) {
            checkoutSessionConfigLoad(function (checkoutSessionConfig) {
                if (!$.isEmptyObject(checkoutSessionConfig)) {
                    var payload = checkoutSessionConfig['checkout_payload'];
                    var signature = checkoutSessionConfig['checkout_signature'];

                    if (this.buttonType === 'PayNow') {
                        payload = checkoutSessionConfig['paynow_payload'];
                        signature = checkoutSessionConfig['paynow_signature'];
                    }

                    callback({
                        merchantId: checkoutSessionConfig['merchant_id'],
                        publicKeyId: checkoutSessionConfig['public_key_id'],
                        ledgerCurrency: checkoutSessionConfig['currency'],
                        sandbox: checkoutSessionConfig['sandbox'],
                        checkoutLanguage: checkoutSessionConfig['language'],
                        productType: this._isPayOnly(checkoutSessionConfig['pay_only']) ? 'PayOnly' : 'PayAndShip',
                        placement: this.options.placement,
                        buttonColor: checkoutSessionConfig['button_color'],
                        createCheckoutSessionConfig: {
                            payloadJSON: payload,
                            signature: signature,
                            publicKeyId: checkoutSessionConfig['public_key_id'],
                        }
                    });

                    if (this.options.placement !== "Checkout") {
                        $(this.options.hideIfUnavailable).show();
                    }
                } else {
                    $(this.options.hideIfUnavailable).hide();
                }
            }.bind(this), forceReload);
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
        _create: function () {
            var self = this;
            if (this.options.placement === 'PayNow') {
                // PayNow is not a valid placement for Amazon Pay API, only used for changing payload
                this.options.placement = 'Checkout';
                this.buttonType = 'PayNow';
            }

            this._draw();

            if (this.options.placement === 'Product') {
                this._redraw();
            }
        },

        /**
         * Draw button
         **/
        _draw: function () {
            var self = this;

            if (!this.drawing) {
                this.drawing = true;
                var $buttonContainer = this.element;
                amazonCheckout.withAmazonCheckout(function (amazon, args) {
                    var $buttonRoot = $('<div></div>');
                    $buttonRoot.html('<img src="' + require.toUrl('images/loader-1.gif') + '" alt="" width="24" />');
                    $buttonContainer.empty().append($buttonRoot);

                    this._loadButtonConfig(function (buttonConfig) {
                        // do not use session config for decoupled button
                        delete buttonConfig.createCheckoutSessionConfig;
                        var amazonPayButton = amazon.Pay.renderButton('#' + $buttonRoot.empty().removeUniqueId().uniqueId().attr('id'), buttonConfig);
                        amazonPayButton.onClick(function() {
                            if (self.buttonType === 'PayNow' && !additionalValidators.validate()) {
                                return false;
                            }
                            //This is for compatibility with Iosc. We need to update the customer's Magento session before getting the final config and payload
                            if (self.buttonType === 'PayNow' && self.options.isIosc()) {
                                storage.post(
                                    'checkout/onepage/update',
                                    "{}",
                                    false
                                ).done(
                                    function (response) {
                                        if (!response.error) {
                                            self._initCheckout(amazonPayButton);
                                        } else {
                                            errorProcessor.process(response);
                                        }
                                    }
                                ).fail(
                                    function (response) {
                                        errorProcessor.process(response);
                                    }
                                );
                            }else{
                                self._initCheckout(amazonPayButton);
                            }
                        });

                        $('.amazon-button-container .field-tooltip').fadeIn();
                        self.drawing = false;
                    });
                }, this);
            }
        },

        _initCheckout: function (amazonPayButton) {
            this._loadButtonConfig(function (buttonConfig) {
                var initConfig = {createCheckoutSessionConfig: buttonConfig.createCheckoutSessionConfig};
                amazonPayButton.initCheckout(initConfig);
            }, true);
            customerData.invalidate('*');
        },

        /**
         * Redraw button if needed
         **/
        _redraw: function () {
            var self = this;

            amazonCheckout.withAmazonCheckout(function (amazon, args) {
                var cartData = customerData.get('cart');
                cartData.subscribe(function (updatedCart) {
                    if (!$(self.options.hideIfUnavailable).first().is(':visible')) {
                        self._draw();
                    }
                });
            });

        },

        click: function () {
            this.element.children().first().trigger('click');
        }
    });


    var cart = customerData.get('cart'),
        customer = customerData.get('customer'),
        canCheckoutWithAmazon = false;

    // to use Amazon Pay: customer needs to be logged in, or guest checkout allowed, or Amazon Sign-in enabled
    if (customer().firstname || amazonStorage.isGuestCheckoutEnabled || amazonStorage.isLwaEnabled) {
        canCheckoutWithAmazon = true;
    }

    if (amazonStorage.isEnabled && canCheckoutWithAmazon) {
        return $.amazon.AmazonButton;
    } else {
        return function(config, element) {
            customer.subscribe(function() {
                if (customer().firstname || amazonStorage.isGuestCheckoutEnabled || amazonStorage.isLwaEnabled) {
                    $(element).AmazonButton();
                }
            });
        };
    }
});
