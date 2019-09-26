/**
 * Copyright 2016 Amazon.com, Inc. or its affiliates. All Rights Reserved.
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

define(
    [
        'jquery',
        'Magento_Checkout/js/model/address-converter',
        'Magento_Checkout/js/model/quote',
        'uiRegistry',
        'Magento_Checkout/js/checkout-data',
        'Magento_Checkout/js/model/checkout-data-resolver',
        'Amazon_Payment/js/model/storage'
    ],
    function ($, addressConverter, quote, registry, checkoutData, checkoutDataResolver, amazonStorage) {
        'use strict';

        /**
         * Populate shipping address form in shipping step from quote model         *
         */
        function populateShippingForm() {
            var shippingAddressData = checkoutData.getShippingAddressFromData();

            registry.async('checkoutProvider')(function (checkoutProvider) {
                checkoutProvider.set(
                    'shippingAddress',
                    $.extend({}, checkoutProvider.get('shippingAddress'), shippingAddressData)
                );
            });
            checkoutDataResolver.resolveShippingAddress();
        }

        /**
         * Populate shipping address form in shipping step from quote model
         * @private
         */
        return function () {
            //check to see if user is logged out of amazon (otherwise shipping form won't be in DOM)
            if (!amazonStorage.isAmazonAccountLoggedIn) {
                populateShippingForm();
            }
            //subscribe to logout and trigger shippingform population when logged out.
            amazonStorage.isAmazonAccountLoggedIn.subscribe(function (loggedIn) {
                if (!loggedIn) {
                    populateShippingForm();
                }
            });
        };
    }
);
