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
        isShippingMethodsLoading = ko.observable(false),
        queryCheckoutSessionId = window.location.search.replace('?amazonCheckoutSessionId=', ''),
        cacheKey = 'is-amazon-checkout',
        sectionKey = 'amazon-checkout-session';

    return {
        isEnabled: isEnabled,
        isShippingMethodsLoading: isShippingMethodsLoading,

        /**
         * Is checkout using Amazon PAYV2?
         *
         * @returns {boolean}
         */
        isAmazonCheckout: function() {
            var isAmazon = window.location.search.indexOf('amazonCheckoutSessionId') != -1; // via redirect
            if (isAmazon) {
                customerData.set(cacheKey, true);
            }
            return customerData.get(cacheKey)() === true;
        },

        /**
         * Revert to standard checkout (e.g. onepage)
         */
        revertCheckout: function() {
            customerData.set(cacheKey, false);
        },

        /**
         * Clear Amazon Checkout Session ID and revert checkout
         */
        clearAmazonCheckout: function() {
            customerData.set(sectionKey, false);
            this.revertCheckout();
        },

        /**
         * Return Amazon Checkout Session ID
         *
         * @returns {*}
         */
        getCheckoutSessionId: function() {
            var checkoutSessionData = customerData.get(sectionKey);
            if (queryCheckoutSessionId) {
                return queryCheckoutSessionId;
            } else if (checkoutSessionData) {
                return checkoutSessionData()['checkoutSessionId'];
            }
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
        }
    };
});
