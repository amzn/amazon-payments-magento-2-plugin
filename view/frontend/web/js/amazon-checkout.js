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
    'require',
    'Amazon_Pay/js/model/storage',
], function (requre, amazonStorage) {
    'use strict';
    return {
        /**
         * Return the appropriate (region-specific) checkout.js module name
         */
        getCheckoutModuleName: function() {
            switch(amazonStorage.getRegion()) {
                case 'de':
                    return 'amazonPayCheckoutDE';
                    break;
                case 'uk':
                    return 'amazonPayCheckoutUK';
                    break;
                case 'jp':
                    return 'amazonPayCheckoutJP';
                    break;
                case 'us':
                default:
                    return 'amazonPayCheckoutUS';
                    break;
            }
        },

        /**
         * Wrapper for accessing window.amazon safely
         */
        withAmazonCheckout: function(cb, _this) {
            var args = Array.prototype.slice.call(arguments, 2);
            return require([this.getCheckoutModuleName()], function() {
                return cb.apply(_this, [amazon].concat(args));
            });
        }
    };
});
