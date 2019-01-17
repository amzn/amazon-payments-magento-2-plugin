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
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/url-builder',
        'mage/storage',
        'mage/url',
        'Magento_Checkout/js/model/error-processor',
        'Magento_Customer/js/model/customer',
        'Magento_Checkout/js/model/full-screen-loader',
        'Amazon_Payment/js/model/storage'
    ],
    function (quote, urlBuilder, storage, url, errorProcessor, customer, fullScreenLoader, amazonStorage) {
        'use strict';

        return function (paymentData, redirectOnSuccess) {
            var serviceUrl, payload, intervalId;

            redirectOnSuccess = redirectOnSuccess !== false;

            /** Checkout for guest and registered customer. */
            if (!customer.isLoggedIn()) {
                serviceUrl = urlBuilder.createUrl('/guest-carts/:quoteId/payment-information', {
                    quoteId: quote.getQuoteId()
                });
                payload = {
                    cartId: quote.getQuoteId(),
                    email: quote.guestEmail,
                    paymentMethod: paymentData,
                    billingAddress: quote.billingAddress()
                };
            } else {
                serviceUrl = urlBuilder.createUrl('/carts/mine/payment-information', {});
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
                function () {
                    if (redirectOnSuccess) {
                        window.location.replace(url.build('checkout/onepage/success/'));
                    }
                }
            ).fail(
                function (response) {
                    errorProcessor.process(response);
                    amazonStorage.amazonDeclineCode(response.responseJSON.code);
                    fullScreenLoader.stopLoader(true);
                    if (response.responseJSON.code === 4273) {
                        intervalId = setInterval(function () {
                            clearInterval(intervalId);
                            window.location.replace(url.build('checkout/cart/'));
                        }, 5000);

                    }
                }
            );
        };
    }
);
