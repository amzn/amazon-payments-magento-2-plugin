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
    'ko',
    'Magento_Customer/js/customer-data',
    'Amazon_PayV2/js/model/amazon-payv2-config'
], function ($, ko, customerData, amazonPayV2Config) {
    'use strict';

    var isEnabled = amazonPayV2Config.isDefined(),
        sessionId,
        sectionKey = 'amazon-checkout-session';

    return {
        isEnabled: isEnabled,

        /**
         * Is checkout using Amazon PAYV2?
         *
         * @returns {boolean}
         */
        isAmazonCheckout: function () {
            return this.getCheckoutSessionId() !== null;
        },

        /**
         * Clear Amazon Checkout Session ID and revert checkout
         */
        clearAmazonCheckout: function() {
            customerData.set(sectionKey, false);
        },

        /**
         * Return Amazon Checkout Session ID
         *
         * @returns {*}
         */
        getCheckoutSessionId: function () {
            if (typeof sessionId === 'undefined') {
                var sessionData = customerData.get(sectionKey);
                sessionId = sessionData ? sessionData()['checkoutSessionId'] : null;
                if (!sessionId && window.location.search.indexOf('?amazonCheckoutSessionId=') != -1) {
                    sessionId = window.location.search.replace('?amazonCheckoutSessionId=', '');
                    this.reloadCheckoutSessionId();
                }
            }
            return sessionId;
        },

        /**
         * Reinit Amazon Checkout Session ID via Ajax
         */
        reloadCheckoutSessionId: function() {
            customerData.reload([sectionKey]);
        },

        /**
         * Return the Amazon Pay region
         */
        getRegion: function() {
            return amazonPayV2Config.getValue('region');
        },

        /**
         * @param defaultResult
         * @returns {boolean}
         */
        isPayOnly: function (defaultResult) {
            var sessionData = customerData.get(sectionKey);
            var sessionValue = sessionData ? sessionData()['isPayOnly'] : null;
            var result = typeof sessionValue === 'boolean' ? sessionValue : defaultResult;
            return result;
        }
    };
});
