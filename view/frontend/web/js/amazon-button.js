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
    'Magento_Checkout/js/model/error-processor',
    'Magento_Ui/js/model/messageList',
    'Amazon_Pay/js/amazon-add-to-cart',
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
        errorProcessor,
        globalMessageList,
        amazonAddToCart
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
        amazonPayButton: null,
        currencyCode: null,

        _loadButtonConfig: function (callback, forceReload = false) {
            checkoutSessionConfigLoad(function (checkoutSessionConfig) {
                if (!$.isEmptyObject(checkoutSessionConfig)) {
                    var payload = checkoutSessionConfig['checkout_payload'];
                    var signature = checkoutSessionConfig['checkout_signature'];

                    if (this.buttonType === 'PayNow') {
                        payload = checkoutSessionConfig['paynow_payload'];
                        signature = checkoutSessionConfig['paynow_signature'];
                    }

                    self.currencyCode = checkoutSessionConfig['currency'];

                    var buttonConfig = {
                        merchantId: checkoutSessionConfig['merchant_id'],
                        publicKeyId: checkoutSessionConfig['public_key_id'],
                        ledgerCurrency: self.currencyCode,
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
                    };

                    if (this._shouldUseEstimatedAmount()
                        && !(JSON.parse(checkoutSessionConfig.checkout_payload).recurringMetadata)
                    ) {
                        buttonConfig.estimatedOrderAmount = this._getEstimatedAmount();
                    }

                    callback(buttonConfig);

                    if (this.options.placement !== "Checkout") {
                        $(this.options.hideIfUnavailable).show();
                    }
                } else {
                    $(this.options.hideIfUnavailable).hide();
                }
            }.bind(this), forceReload);
        },

        _getEstimatedAmount: function () {
            var subtotal = (parseFloat(customerData.get('cart')().subtotalAmount) || 0).toFixed(2);

            if (self.currencyCode === 'JPY') {
                subtotal = parseFloat(subtotal).toFixed(0);
            }

            return {
                amount: subtotal,
                currencyCode: self.currencyCode
            };
        },

        /**
         * @param {boolean} isCheckoutSessionPayOnly
         * @returns {boolean}
         * @private
         */
         _isPayOnly: function (isCheckoutSessionPayOnly) {
            if (this.options.payOnly === null) {
                this.options.payOnly = isCheckoutSessionPayOnly;
            }

            return this.options.payOnly;
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
            this._subscribeToCartUpdates();
        },

        /**
         * Draw button
         */
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
                        // remove session config to decouple button, allowing onclick adjustment
                        if(self._isAmazonPayShownAsPaymentMethod() || self.options.placement === 'Product') {
                            delete buttonConfig.createCheckoutSessionConfig;
                        }

                        try {
                            self.amazonPayButton = amazon.Pay.renderButton('#' + $buttonRoot.empty().removeUniqueId().uniqueId().attr('id'), buttonConfig);
                        } catch (e) {
                            console.log('Amazon Pay button render error: ' + e);
                            return;
                        }

                        if (self._isAmazonPayShownAsPaymentMethod()) {
                            self.amazonPayButton.onClick(function() {
                                if (!additionalValidators.validate() || self.options.disabled) {
                                    return false;
                                }
                                self._initCheckout();
                            });
                        } else if (self.options.placement === 'Product') {
                            amazonAddToCart.register(self);
                        }

                        $('.amazon-button-container .field-tooltip').fadeIn();
                        self.drawing = false;

                        if (self.buttonType === 'PayNow' && self.options.payOnly) {
                            var checkoutData = customerData.get('checkout-data');
                            self._toggleApbAvailability(checkoutData);
                            checkoutData.subscribe(function (data) {
                                self._toggleApbAvailability(data);
                            });
                        }
                    });
                }, this);
            }
        },

        // PayNow indicating this is an APB button
        _isAmazonPayShownAsPaymentMethod: function () {
            return (this.buttonType === 'PayNow');
        },

        _initCheckout: function () {
            if( this.options.isIosc()) {
                this._initOneStepCheckout();
            } else {
                this._initAmazonCheckout();
            }
        },

        _initOneStepCheckout: function () {
            //This is for compatibility with Iosc. We need to update the customer's Magento session before getting the final config and payload
            storage.post(
                'checkout/onepage/update',
                "{}",
                false
            ).done(
                function (response) {
                    if (!response.error) {
                        self._initAmazonCheckout();
                    } else {
                        errorProcessor.process(response);
                    }
                }
            ).fail(
                function (response) {
                    errorProcessor.process(response);
                }
            );
        },

        _initAmazonCheckout: function () {
            var self = this;

            if (self.buttonType === 'PayNow' && self._isPayOnly()) {
                if (!customerData.get('checkout-data')().selectedBillingAddress) {
                    var setBillingAddressAction = require('Magento_Checkout/js/action/set-billing-address');
                    setBillingAddressAction(globalMessageList);
                }
            }

            this._loadButtonConfig(function (buttonConfig) {
                var initConfig = {createCheckoutSessionConfig: buttonConfig.createCheckoutSessionConfig};
                self.amazonPayButton.initCheckout(initConfig);
            }, true);
            customerData.invalidate('*');
        },

        /**
         * Update button if needed
         **/
        _subscribeToCartUpdates: function () {
            var self = this;

            amazonCheckout.withAmazonCheckout(function (amazon, args) {
                var cartData = customerData.get('cart');
                cartData.subscribe(function (updatedCart) {
                    if (self.amazonPayButton && self.buttonType !== 'PayNow') {
                        var hasSubscription = false;
                        var isSubscriptionOption = (option) => option.label && option.label === amazonStorage.getSubscriptionLabel();

                        // If any cart item has a subscription option, we should redraw the button to exclude estimatedOrderAmount
                        updatedCart.items.forEach((item) => {
                            if (item.options.length && item.options.some(isSubscriptionOption)) {
                                hasSubscription = true;
                                return;
                            }
                        });

                        if (hasSubscription) {
                            self._draw();
                        } else {
                            if (self.options.placement === 'Cart') {
                                delete self.amazonPayButton;
                            }

                            if (self.amazonPayButton
                                && self._shouldUseEstimatedAmount()
                                && updatedCart.summary_count !== 0
                            ) {
                                self.amazonPayButton.updateButtonInfo(self._getEstimatedAmount());
                            }
                        }
                    }
                });
            });
        },

        _toggleApbAvailability: function (checkoutData) {
            var billingAddress = checkoutData.selectedBillingAddress;
            var quote = require('Magento_Checkout/js/model/quote');

            var apb = document.querySelectorAll('#PayWithAmazonButton')[0]
                .querySelector('div')
                .shadowRoot
                .querySelector('div');

            if (!billingAddress && !quote.billingAddress()) {
                apb.classList.remove('amazonpay-button-enabled');
                apb.classList.add('amazonpay-button-disabled');
                this.options.disabled = true;
            } else {
                apb.classList.remove('amazonpay-button-disabled');
                apb.classList.add('amazonpay-button-enabled');
                this.options.disabled = false;
            }
        },

        _shouldUseEstimatedAmount: function () {
            return this.options.buttonType !== 'PayNow'
                && this.options.placement !== 'Product'
                && !amazonStorage.isMulticurrencyEnabled;
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
