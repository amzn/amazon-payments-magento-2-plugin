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

define(
    [
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/url-builder',
        'mage/storage',
        'mage/url',
        'Magento_Checkout/js/model/error-processor',
        'Magento_Customer/js/model/customer',
        'Magento_Checkout/js/model/full-screen-loader',
        'Amazon_PayV2/js/model/storage',
        'mage/translate'
    ],
    function (quote, urlBuilder, storage, url, errorProcessor, customer, fullScreenLoader, amazonStorage, $t) {
        'use strict';

        return function (paymentData, redirectOnSuccess) {
            var serviceUrl, payload;

            redirectOnSuccess = redirectOnSuccess !== false;

            /** Checkout for guest and registered customer. */
            if (!customer.isLoggedIn()) {
                serviceUrl = urlBuilder.createUrl('/guest-carts/:quoteId/set-payment-information', {
                    quoteId: quote.getQuoteId()
                });
                payload = {
                    cartId: quote.getQuoteId(),
                    email: quote.guestEmail,
                    paymentMethod: paymentData,
                    billingAddress: quote.billingAddress()
                };
            } else {
                serviceUrl = urlBuilder.createUrl('/carts/mine/set-payment-information', {});
                payload = {
                    cartId: quote.getQuoteId(),
                    paymentMethod: paymentData,
                    billingAddress: quote.billingAddress()
                };
            }

            fullScreenLoader.startLoader();

            return storage.post(
                serviceUrl,
                JSON.stringify(payload)
            ).done(
                function (response) {
                    // Redirect URL
                    if (response !== true && response.indexOf('http') == 0) {
                        amazonStorage.clearAmazonCheckout();
                        window.location.replace(response);
                    } else {
                        fullScreenLoader.stopLoader(true);
                        console.log('Invalid Amazon RedirectUrl:');
                        console.log(response);
                        errorProcessor.process(response);
                    }
                }
            ).fail(
                function (response) {
                    errorProcessor.process(response);
                    console.log(response);
                    fullScreenLoader.stopLoader(true);
                }
            );

        };
    }
);
