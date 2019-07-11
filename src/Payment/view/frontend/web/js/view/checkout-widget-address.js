/*global define*/

define(
    [
        'jquery',
        'uiComponent',
        'ko',
        'Magento_Customer/js/model/customer',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/action/select-shipping-address',
        'Magento_Checkout/js/model/shipping-rate-processor/new-address',
        'Magento_Checkout/js/action/set-shipping-information',
        'Amazon_Payment/js/model/storage',
        'amazonCore',
        'Magento_Checkout/js/model/shipping-service',
        'Magento_Checkout/js/model/address-converter',
        'mage/storage',
        'Magento_Checkout/js/model/full-screen-loader',
        'Magento_Checkout/js/model/error-processor',
        'Magento_Checkout/js/model/url-builder',
        'Magento_Checkout/js/checkout-data',
        'Magento_Checkout/js/model/checkout-data-resolver',
        'uiRegistry'
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
        amazonCore,
        shippingService,
        addressConverter,
        storage,
        fullScreenLoader,
        errorProcessor,
        urlBuilder,
        checkoutData,
        checkoutDataResolver,
        registry
    ) {
        'use strict';

        var self;

        return Component.extend({
            defaults: {
                template: 'Amazon_Payment/checkout-widget-address'
            },
            options: {
                sellerId: registry.get('amazonPayment').merchantId,
                addressWidgetDOMId: 'addressBookWidgetDiv',
                widgetScope: registry.get('amazonPayment').loginScope
            },
            isCustomerLoggedIn: customer.isLoggedIn,
            isAmazonAccountLoggedIn: amazonStorage.isAmazonAccountLoggedIn,
            isAmazonEnabled: ko.observable(registry.get('amazonPayment').isPwaEnabled),
            rates: shippingService.getShippingRates(),

            /**
             * Init
             */
            initialize: function () {
                self = this;
                this._super();
            },

            /**
             * Call when component template is rendered
             */
            initAddressWidget: function () {
                if(amazonStorage.amazonDefined()) {
                    self.renderAddressWidget();
                } else {
                    var subscription = amazonStorage.amazonDefined.subscribe(function (defined) { //eslint-disable-line vars-on-top
                        if (defined) {
                            self.renderAddressWidget();
                            subscription.dispose();
                        }
                    });
                }
            },

            /**
             * render Amazon address Widget
             */
            renderAddressWidget: function () {
                new OffAmazonPayments.Widgets.AddressBook({ // eslint-disable-line no-undef
                    sellerId: self.options.sellerId,
                    scope: self.options.widgetScope,

                    /**
                     * Order reference creation callback
                     */
                    onOrderReferenceCreate: function (orderReference) {
                        var orderid = orderReference.getAmazonOrderReferenceId();

                        amazonStorage.setOrderReference(orderid);
                    },

                    /**
                     * Address select callback
                     */
                    onAddressSelect: function () { // orderReference
                        self.getShippingAddressFromAmazon();
                    },
                    design: {
                        designMode: 'responsive'
                    },

                    /**
                     * Error callback
                     */
                    onError: amazonCore.handleWidgetError
                }).bind(self.options.addressWidgetDOMId);
            },

            /**
             * Get shipping address from Amazon API
             */
            getShippingAddressFromAmazon: function () {
                var serviceUrl, payload;

                amazonStorage.isShippingMethodsLoading(true);
                shippingService.isLoading(true);
                serviceUrl = urlBuilder.createUrl('/amazon-shipping-address/:amazonOrderReference', {
                    amazonOrderReference: amazonStorage.getOrderReference()
                }),
                    payload = {
                        addressConsentToken: amazonStorage.getAddressConsentToken()
                    };

                storage.put(
                    serviceUrl,
                    JSON.stringify(payload)
                ).done(
                    function (data) {
                        var amazonAddress = data.shift(),
                            addressData = addressConverter.formAddressDataToQuoteAddress(amazonAddress),
                            i;

                        //if telephone is blank set it to 00000000 so it passes the required validation
                        addressData.telephone = !addressData.telephone ? '0000000000' : addressData.telephone;

                        //fill in blank street fields
                        if ($.isArray(addressData.street)) {
                            for (i = addressData.street.length; i <= 2; i++) {
                                addressData.street[i] = '';
                            }
                        }
                        checkoutData.setShippingAddressFromData(
                            addressConverter.quoteAddressToFormAddressData(addressData)
                        );
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

            /**
             * Get Amazon Order Reference ID
             */
            getAmazonOrderReference: function () {
                return amazonStorage.getOrderReference();
            },

            /**
             * Get Amazon Address Consent Token
             */
            getAddressConsentToken: function () {
                return amazonStorage.getAddressConsentToken();
            }
        });
    }
);
