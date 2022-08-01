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
    'Amazon_Pay/js/model/amazon-pay-config',
    'jquery/jquery-storageapi'
], function ($, amazonPayConfig) {
    'use strict';

    var isEnabled = amazonPayConfig.isDefined(),
        storage = null,
        getStorage = function () {
            if (storage === null) {
                storage = $.initNamespaceStorage('amzn-checkout-session').localStorage;
            }
            return storage;
        };

    var isLwaEnabled = amazonPayConfig.getValue('is_lwa_enabled');
    var isGuestCheckoutEnabled = amazonPayConfig.getValue('is_guest_checkout_enabled');
    var isMulticurrencyEnabled = amazonPayConfig.getValue('is_multicurrency_enabled');

    return {
        isEnabled: isEnabled,
        isLwaEnabled: isLwaEnabled,
        isGuestCheckoutEnabled: isGuestCheckoutEnabled,
        isMulticurrencyEnabled: isMulticurrencyEnabled,

        /**
         * Is checkout using Amazon Pay?
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
            var param = 'amazonCheckoutSessionId';

            var myParams = new URLSearchParams(window.location.search);
            if (myParams.has(param)) {
                var paramSessionId = myParams.get(param);
                if (typeof sessionId === 'undefined' || paramSessionId != sessionId) {
                    sessionId = paramSessionId;
                    getStorage().set('id', sessionId);
                }
            }
            return sessionId;
        },

        /**
         * Return the Amazon Pay region
         */
        getRegion: function() {
            return amazonPayConfig.getValue('region');
        }
    };
});
