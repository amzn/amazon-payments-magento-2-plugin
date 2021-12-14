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
        'Amazon_Pay/js/action/checkout-vault-charge',
        'Amazon_Pay/js/model/storage',
        'Magento_CheckoutAgreements/js/model/agreements-assigner'
    ],
    function (quote, urlBuilder, storage, errorProcessor, customerData, customer, fullScreenLoader, checkoutVaultChargeAction, amazonStorage, agreementsAssigner) {
        'use strict';

        return function (paymentData) {
            var serviceUrl, payload;
           
            serviceUrl = urlBuilder.createUrl('/carts/mine/set-payment-information', {});
            payload = {
                cartId: quote.getQuoteId(),
                paymentMethod: paymentData,
                billingAddress: quote.billingAddress()
            };
            
            console.log(paymentData);
            fullScreenLoader.startLoader();

            agreementsAssigner(payload.paymentMethod);

            return storage.post(
                serviceUrl,
                JSON.stringify(payload)
            ).done(
                function (response) {
                    // Redirect URL
                    if (response === true) {
                        checkoutVaultChargeAction(function (redirectUrl) {
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
                        });
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
