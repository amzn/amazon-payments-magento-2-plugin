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
    'underscore',
    'mage/storage',
    'mage/url',
    'Magento_Customer/js/customer-data'
], function ($, _, remoteStorage, url, customerData) {
    'use strict';

    var callbacks = [];
    var localStorage = null;

    /**
     * Time in milliseconds a cached button configuration is reused across page views.
     *
     * Everything served from the cache is store level data (merchant id, public key id, currency,
     * button color, language, sandbox flag, product type). Signed payloads are not affected:
     * starting checkout always reloads the configuration with forceReload = true, see
     * amazon-button.js::_initCheckout(), so amazon.Pay.createCheckoutSession() is never called
     * with a payload taken from this cache.
     */
    var configCacheLifetime = 10 * 60 * 1000;

    var getLocalStorage = function () {
        if (localStorage === null) {
            localStorage = $.initNamespaceStorage('amzn-checkout-session-config').localStorage;
        }
        return localStorage;
    };

    /**
     * Check whether the cached configuration is too old to be reused
     *
     * Entries stored without a timestamp, i.e. by an earlier version of this module, are expired.
     *
     * @returns {Boolean}
     */
    var isCacheExpired = function () {
        var cachedAt = getLocalStorage().get('config_time');

        return !cachedAt || (new Date().getTime() - cachedAt) > configCacheLifetime;
    };

    return function (callback, forceReload = false) {
        var cartId = customerData.get('cart')()['data_id'] || window.checkout.storeId;
        var config = getLocalStorage().get('config') || false;
        if (forceReload
            || cartId !== getLocalStorage().get('cart_id')
            || typeof config.checkout_payload === 'undefined'
            || isCacheExpired()) {
            callbacks.push(callback);
            if (callbacks.length == 1) {
                remoteStorage.get(url.build('amazon_pay/checkout/config')).done(function (config) {
                    getLocalStorage().set('cart_id', cartId);
                    getLocalStorage().set('config', config);
                    getLocalStorage().set('config_time', new Date().getTime());
                    do {
                        callbacks.shift()(config);
                    } while (callbacks.length);
                });
            }
        } else {
            callback(getLocalStorage().get('config'));
        }
    };  
});
