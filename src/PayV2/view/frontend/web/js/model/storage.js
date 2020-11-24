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
    'jquery/jquery-storageapi'
], function ($, amazonPayV2Config) {
    'use strict';

    var isEnabled = amazonPayV2Config.isDefined(),
        storage = null,
        getStorage = function () {
            if (storage === null) {
                storage = $.initNamespaceStorage('amzn-checkout-session').localStorage;
            }
            return storage;
        };

    var isLwaEnabled = amazonPayV2Config.getValue('is_lwa_enabled');
    var isGuestCheckoutEnabled = amazonPayV2Config.getValue('is_guest_checkout_enabled');

    return {
        isEnabled: isEnabled,
        isLwaEnabled: isLwaEnabled,
        isGuestCheckoutEnabled: isGuestCheckoutEnabled,

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
            var param = '?amazonCheckoutSessionId=';
            if (typeof sessionId === 'undefined' && window.location.search.indexOf(param) != -1) {
                sessionId = window.location.search.replace(param, '');
                getStorage().set('id', sessionId);
            }
            else if(sessionId != window.location.search.replace(param, '') && window.location.search.replace(param, '') != '') {
                sessionId = window.location.search.replace(param, '');
                getStorage().set('id', sessionId);
            }
            return sessionId;
        },

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
