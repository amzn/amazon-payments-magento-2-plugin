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
        'Magento_Checkout/js/model/error-processor',
        'Magento_Customer/js/customer-data',
        'Magento_Customer/js/model/customer',
        'Magento_Checkout/js/model/full-screen-loader',
        'Amazon_Pay/js/action/checkout-session-update',
        'Amazon_Pay/js/model/storage',
        'Amazon_Pay/js/model/order',
        'Magento_CheckoutAgreements/js/model/agreements-assigner'
    ],
    function (quote, urlBuilder, storage, errorProcessor, customerData, customer, fullScreenLoader, checkoutSessionUpdateAction, amazonStorage, orderModel, agreementsAssigner) {
        'use strict';

        return function (paymentData) {
            var serviceUrl, payload;

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

            agreementsAssigner(payload.paymentMethod);

            storage.post(
                serviceUrl,
                JSON.stringify(payload)
            ).done(
                function (response) {
                    // Redirect URL
                    if (response === true) {
                        checkoutSessionUpdateAction(function (redirectUrl) {
                            orderModel.place(
                                amazonStorage.getCheckoutSessionId()
                            ).done(
                                function (response) {
                                    if(response['success'] === false) {
                                        console.log('Failed to place website order: ');
                                        handleResponseError(response);
                                        return;
                                    }
                                    customerData.invalidate(['cart']);
                                    customerData.set('checkout-data', {
                                        'selectedShippingAddress': null,
                                        'shippingAddressFromData': null,
                                        'newCustomerShippingAddress': null,
                                        'selectedShippingRate': null,
                                        'selectedPaymentMethod': null,
                                        'selectedBillingAddress': null,
                                        'billingAddressFromData': null,
                                        'newCustomerBillingAddress': null
                                    });
                                    amazonStorage.clearAmazonCheckout();
                                    window.location.replace(redirectUrl);
                                }
                            )
                        });
                    } else {
                        console.log('Invalid Amazon RedirectUrl:');
                        handleResponseError(response);
                    }
                }
            ).fail(
                function (response) {
                    handleResponseError(response);
                }
            );
            function handleResponseError(response) {
                fullScreenLoader.stopLoader(true);
                console.log(response);
                errorProcessor.process(response);
            }
        };
    }
);
