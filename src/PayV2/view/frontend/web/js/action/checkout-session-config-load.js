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
    var localStorage = $.initNamespaceStorage('amzn-checkout-session-config').localStorage;
    return function (callback) {
        var cartId = customerData.get('cart')()['data_id'] || 0;
        if (cartId !== localStorage.get('cart_id')) {
            callbacks.push(callback);
            if (callbacks.length == 1) {
                remoteStorage.get(url.build('amazon_payv2/checkout/config')).done(function (config) {
                    localStorage.set('cart_id', cartId);
                    localStorage.set('config', config);
                    do {
                        callbacks.shift()(config);
                    } while (callbacks.length);
                });
            }
        } else {
            callback(localStorage.get('config'));
        }
    };
});
