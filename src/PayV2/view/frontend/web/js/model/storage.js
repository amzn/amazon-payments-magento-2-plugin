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
    'Amazon_PayV2/js/model/amazon-payv2-config'
], function ($, amazonPayV2Config) {
    'use strict';

    var isEnabled = amazonPayV2Config.isDefined(),
        storage = $.initNamespaceStorage('amzn-checkout-session').localStorage;

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
            storage.removeAll();
        },

        /**
         * Return Amazon Checkout Session ID
         *
         * @returns {*}
         */
        getCheckoutSessionId: function () {
            var sessionId = storage.get('id');
            if (typeof sessionId === 'undefined' && window.location.search.indexOf('?amazonCheckoutSessionId=') != -1) {
                sessionId = window.location.search.replace('?amazonCheckoutSessionId=', '');
                storage.set('id', sessionId);
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
            storage.set('is_edit_billing_clicked', value);
            return this;
        },

        /**
         * @returns {boolean}
         */
        getIsEditPaymentFlag: function () {
            return storage.get('is_edit_billing_clicked');
        }
    };
});
