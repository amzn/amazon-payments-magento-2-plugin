/*global define*/

define(
    [
        'jquery',
        "uiComponent",
        'ko',
        'Magento_Customer/js/model/customer',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/action/select-shipping-address',
        'Magento_Checkout/js/model/shipping-rate-processor/new-address',
        'Magento_Checkout/js/action/set-shipping-information',
        'Amazon_Payment/js/model/storage',
        'Magento_Checkout/js/model/shipping-service',
        'Magento_Checkout/js/model/address-converter',
        'mage/storage',
        'Magento_Checkout/js/model/full-screen-loader',
        'Magento_Checkout/js/model/error-processor',
        'Magento_Checkout/js/model/url-builder',
        'Magento_Checkout/js/checkout-data',
        'Magento_Checkout/js/model/checkout-data-resolver'
    ],
    function (
        $,
        Component,
        ko,
        customer,
        quote,
        selectShippingAddress,
        shippingProcessor,
        setShippingInformationAction,
        amazonStorage,
        shippingService,
        addressConverter,
        storage,
        fullScreenLoader,
        errorProcessor,
        urlBuilder,
        checkoutData,
        checkoutDataResolver
    ) {
        'use strict';
        var self;

        return Component.extend({
            defaults: {
                template: 'Amazon_Payment/checkout-widget-address'
            },
            options: {
                sellerId: window.amazonPayment.merchantId,
                addressWidgetDOMId: 'addressBookWidgetDiv',
                widgetScope: window.amazonPayment.loginScope
            },
            isCustomerLoggedIn: customer.isLoggedIn,
            isAmazonAccountLoggedIn: amazonStorage.isAmazonAccountLoggedIn,
            isAmazonEnabled: ko.observable(window.amazonPayment.isPwaEnabled),
            rates: shippingService.getShippingRates(),
            initialize: function () {
                self = this;
                this._super();
            },
            /**
             * Call when component template is rendered
             */
            initAddressWidget: function () {
                self.renderAddressWidget();
            },
            /**
             * render Amazon address Widget
             */
            renderAddressWidget: function () {
                new OffAmazonPayments.Widgets.AddressBook({
                    sellerId: self.options.sellerId,
                    scope: self.options.widgetScope,
                    onOrderReferenceCreate: function (orderReference) {
                        var orderid = orderReference.getAmazonOrderReferenceId();
                        amazonStorage.setOrderReference(orderid);
                    },
                    onAddressSelect: function (orderReference) {
                        self.getShippingAddressFromAmazon();
                    },
                    design: {
                        designMode: 'responsive'
                    },
                    onError: function (error) {
                    }
                }).bind(self.options.addressWidgetDOMId);
            },

            /**
             * Get shipping address from Amazon API
             */
            getShippingAddressFromAmazon: function () {
                amazonStorage.isShippingMethodsLoading(true);
                shippingService.isLoading(true);
                var serviceUrl = urlBuilder.createUrl('/amazon-shipping-address/:amazonOrderReference', {amazonOrderReference: amazonStorage.getOrderReference()}),
                    payload = {
                        addressConsentToken: amazonStorage.getAddressConsentToken()
                };

                storage.put(
                    serviceUrl,
                    JSON.stringify(payload)
                ).done(
                    function (data) {
                        var amazonAddress = data.shift(),
                            addressData = addressConverter.formAddressDataToQuoteAddress(amazonAddress);

                        //if telephone is blank set it to 00000000 so it passes the required validation
                        addressData.telephone = !(addressData.telephone) ? '0000000000' : addressData.telephone;

                        //fill in blank street fields
                        if ($.isArray(addressData.street)) {
                            for (var i = addressData.street.length; i <= 2; i++) {
                                addressData.street[i] = '';
                            }
                        }
                        checkoutData.setShippingAddressFromData(addressConverter.quoteAddressToFormAddressData(addressData));
                        checkoutDataResolver.resolveEstimationAddress();
                    }
                ).fail(
                    function (response) {
                        errorProcessor.process(response);
                        //remove shipping loader and set shipping rates to 0 on a fail
                        shippingService.setShippingRates([]);
                        amazonStorage.isShippingMethodsLoading(false);
                    }
                );
            },

            getAmazonOrderReference: function () {
                return amazonStorage.getOrderReference();
            },

            getAddressConsentToken: function () {
                return amazonStorage.getAddressConsentToken();
            }
        });
    }
);

