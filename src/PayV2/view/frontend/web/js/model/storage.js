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
    'Amazon_PayV2/js/model/amazon-payv2-config',
    'mage/storage',
    'Magento_Checkout/js/model/url-builder',
    'Amazon_PayV2/js/action/checkout-session-cancel',
    'Magento_Checkout/js/model/quote'
], function ($, amazonPayV2Config, mageStorage, urlBuilder, checkoutSessionCancelAction, quote) {
    'use strict';

    var isEnabled = amazonPayV2Config.isDefined(),
        storage = null,
        getStorage = function () {
            if (storage === null) {
                storage = $.initNamespaceStorage('amzn-checkout-session').localStorage;
            }
            return storage;
        };

    return {
        isEnabled: isEnabled,

        /**
         * Is checkout using Amazon PAYV2?
         *
         * @returns {boolean}
         */
        isAmazonCheckout: function () {
            return typeof this.getCheckoutSessionId() === 'string';
        },

        /**
         * Clear Amazon Checkout Session ID and revert checkout
         */
        clearAmazonCheckout: function() {
            getStorage().removeAll();
        },

        /**
         * Return Amazon Checkout Session ID
         *
         * @returns {*}
         */
        getCheckoutSessionId: function () {
            var sessionId = getStorage().get('id');
            if (typeof sessionId === 'undefined' && window.location.search.indexOf('?amazonCheckoutSessionId=') != -1) {
                sessionId = window.location.search.replace('?amazonCheckoutSessionId=', '');
                getStorage().set('id', sessionId);
            }

            // If we got a sessionId here, optimistically return it, but validate it asynchronously.
            if (sessionId && !this.sessionValidationTriggered) {
                var serviceUrl = urlBuilder.createUrl('/amazon-v2-checkout-session/:cartId/validate', {
                    cartId: quote.getQuoteId()
                });
                mageStorage.get(serviceUrl).done(function (data) {
                    if (!data) {
                        checkoutSessionCancelAction(function () {
                            this.clearAmazonCheckout();
                            window.location.replace(window.checkoutConfig.checkoutUrl);
                        }.bind(this));
                    }
                }.bind(this)).fail(function (response) {
                    checkoutSessionCancelAction(function () {
                        this.clearAmazonCheckout();
                        window.location.replace(window.checkoutConfig.checkoutUrl);
                    }.bind(this));
                }.bind(this));
                this.sessionValidationTriggered = true;
            }
            return sessionId;
        },
        sessionValidationTriggered: false,

        /**
         * Return the Amazon Pay region
         */
        getRegion: function() {
            return amazonPayV2Config.getValue('region');
        },

        /**
         * @param value
         * @returns {exports}
         */
        setIsEditPaymentFlag: function (value) {
            getStorage().set('is_edit_billing_clicked', value);
            return this;
        },

        /**
         * @returns {boolean}
         */
        getIsEditPaymentFlag: function () {
            return getStorage().get('is_edit_billing_clicked');
        }
    };
});
