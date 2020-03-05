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
        'Amazon_PayV2/js/model/storage',
        'Magento_Checkout/js/model/shipping-service',
        'Magento_Checkout/js/model/address-converter',
        'Magento_Checkout/js/action/create-billing-address',
        'Magento_Checkout/js/action/create-shipping-address',
        'mage/storage',
        'Magento_Checkout/js/model/full-screen-loader',
        'Magento_Checkout/js/model/error-processor',
        'Magento_Checkout/js/model/url-builder',
        'Magento_Checkout/js/checkout-data',
        'Magento_Checkout/js/model/checkout-data-resolver',
        'Magento_Customer/js/model/address-list',
        'uiRegistry',
        'Amazon_PayV2/js/model/amazon-payv2-config',
        'Amazon_PayV2/js/amazon-checkout'
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
        createBillingAddress,
        createShippingAddress,
        storage,
        fullScreenLoader,
        errorProcessor,
        urlBuilder,
        checkoutData,
        checkoutDataResolver,
        addressList,
        registry,
        amazonConfig,
        amazonCheckout
    ) {
        'use strict';

        var self;

        require([amazonCheckout.getCheckoutModuleName()]);

        amazonStorage.reloadCheckoutSessionId();

        return Component.extend({
            defaults: {
                isBillingAddress: false,
                template: 'Amazon_PayV2/checkout-address'
            },
            isCustomerLoggedIn: customer.isLoggedIn,
            isAmazonCheckout: amazonStorage.isAmazonCheckout(),
            rates: shippingService.getShippingRates(),

            /**
             * Init
             */
            initialize: function () {
                self = this;
                this._super();
                if (this.isAmazonCheckout) {
                    this.getAddressFromAmazon();
                }
            },

            /**
             * Call when component template is rendered
             */
            initAddress: function () {
                var addressDataList = $.extend({}, this.isBillingAddress ? quote.billingAddress() : quote.shippingAddress());

                // Only display one address from Amazon
                addressList.removeAll();

                // Remove empty street array values for list view
                if ($.isArray(addressDataList.street)) {
                    addressDataList.street = addressDataList.street.filter(function (el) {
                        return el != null;
                    });
                }

                if (!$.isEmptyObject(addressDataList)) {
                    addressList.push(addressDataList);
                    this.setEmail(addressDataList.email);
                }
            },

            /**
             * Retrieve address from Amazon API
             */
            getAddressFromAmazon: function () {
                var serviceUrl, payload;
                var addressType = this.isBillingAddress ? 'billing' : 'shipping';

                // Only display one address from Amazon
                addressList.removeAll();

                amazonStorage.isShippingMethodsLoading(true);
                shippingService.isLoading(true);
                serviceUrl = urlBuilder.createUrl('/amazon-v2-' + addressType + '-address/:amazonCheckoutSessionId', {
                    amazonCheckoutSessionId: amazonStorage.getCheckoutSessionId()
                }),

                storage.put(
                    serviceUrl
                ).done(
                    function (data) {

                        // Invalid checkout session
                        if (!data.length) {
                            //self.resetCheckout();
                            return;
                        }

                        var amazonAddress = data.shift(),
                            addressData = self.isBillingAddress ? createBillingAddress(amazonAddress) : createShippingAddress(amazonAddress),
                            checkoutProvider = registry.get('checkoutProvider'),
                            addressConvert,
                            i;

                        //console.log(amazonAddress);
                        //console.log(addressData);

                        self.setEmail(amazonAddress.email);

                        // Fill in blank street fields
                        if ($.isArray(addressData.street)) {
                            for (i = addressData.street.length; i <= 2; i++) {
                                addressData.street[i] = '';
                            }
                        }

                        // Amazon does not return telephone or non-US regionIds, so use previous provider values
                        var checkoutAddress = checkoutProvider[addressType + 'Address'];
                        if (checkoutAddress) {
                            addressData.telephone = checkoutAddress.telephone;
                            if (!addressData.regionId) {
                                addressData.regionId = checkoutAddress.region_id;
                            }
                        }

                        // Save address
                        addressConvert = addressConverter.quoteAddressToFormAddressData(addressData);
                        checkoutData['set' + addressType.charAt(0).toUpperCase() + addressType.slice(1) + 'AddressFromData'].call(checkoutData, addressConvert);

                        if (self.isBillingAddress) {
                            checkoutProvider.set('billingAddress' + amazonConfig.getCode(), addressConvert);
                            checkoutData.setSelectedBillingAddress(addressData.getKey());
                            checkoutData.setNewCustomerBillingAddress(addressConvert);
                            checkoutDataResolver.resolveBillingAddress();
                        } else {
                            checkoutProvider.set('shippingAddress', addressConvert);
                            checkoutDataResolver.resolveEstimationAddress();
                        }

                        self.initAddress();
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
             * Set email address
             * @param email
             */
            setEmail: function(email) {
                $('#customer-email').val(email);
                checkoutData.setInputFieldEmailValue(email);
                checkoutData.setValidatedEmailValue(email);
                quote.guestEmail = email;
            },

            /**
             * Revert to standard checkout
             */
            resetCheckout: function() {
                amazonStorage.clearAmazonCheckout();
                window.location =  window.checkoutConfig.checkoutUrl;
            }
        });
    }
);
