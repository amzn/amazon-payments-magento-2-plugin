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
        'Magento_Checkout/js/action/create-shipping-address',
        'mage/storage',
        'Magento_Checkout/js/model/full-screen-loader',
        'Magento_Checkout/js/model/error-processor',
        'Magento_Checkout/js/model/url-builder',
        'Magento_Checkout/js/checkout-data',
        'Magento_Checkout/js/model/checkout-data-resolver',
        'Magento_Customer/js/model/address-list',
        'uiRegistry',
        'Amazon_PayV2/js/action/checkout-session-address-load',
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
        createShippingAddress,
        storage,
        fullScreenLoader,
        errorProcessor,
        urlBuilder,
        checkoutData,
        checkoutDataResolver,
        addressList,
        registry,
        checkoutSessionAddressLoad,
        amazonCheckout
    ) {
        'use strict';

        var self;

        require([amazonCheckout.getCheckoutModuleName()]);

        return Component.extend({
            defaults: {
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
                if (!amazonStorage.isPayOnly(true) && this.isAmazonCheckout) {
                    this.getShippingAddressFromAmazon();
                }
            },

            /**
             * Call when component template is rendered
             */
            initAddress: function () {
                var addressDataList = $.extend({}, quote.shippingAddress());

                // Only display one address from Amazon
                addressList.removeAll();

                // Remove empty street array values for list view
                if ($.isArray(addressDataList.street)) {
                    addressDataList.street = addressDataList.street.filter(function (el) {
                        return el != null;
                    });
                }

                addressList.push(addressDataList);
                this.setEmail(addressDataList.email);
            },

            /**
             * Retrieve shipping address from Amazon API
             */
            getShippingAddressFromAmazon: function () {
                // Only display one address from Amazon
                addressList.removeAll();
                checkoutSessionAddressLoad('shipping', function (amazonAddress) {
                    var addressData = createShippingAddress(amazonAddress),
                        checkoutProvider = registry.get('checkoutProvider'),
                        addressConvert;

                    self.setEmail(amazonAddress.email);

                    // Fill in blank street fields
                    if ($.isArray(addressData.street)) {
                        for (var i = addressData.street.length; i <= 2; i++) {
                            addressData.street[i] = '';
                        }
                    }

                    // Amazon does not return telephone or non-US regionIds, so use previous provider values
                    if (checkoutProvider.shippingAddress) {
                        if (!addressData.telephone) {
                            addressData.telephone = checkoutProvider.shippingAddress.telephone;
                        }
                        if (!addressData.regionId) {
                            addressData.regionId = checkoutProvider.shippingAddress.region_id;
                        }
                    }

                    // Save shipping address
                    addressConvert = addressConverter.quoteAddressToFormAddressData(addressData);
                    checkoutData.setShippingAddressFromData(addressConvert);
                    checkoutProvider.set('shippingAddress', addressConvert);

                    checkoutDataResolver.resolveEstimationAddress();

                    self.initAddress();
                });
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
            }
        });
    }
);
