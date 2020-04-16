/*global define*/

define(
    [
        'jquery',
        'uiComponent',
        'ko',
        'Magento_Checkout/js/model/quote',
        'Amazon_PayV2/js/model/storage',
        'Magento_Checkout/js/model/address-converter',
        'Magento_Checkout/js/action/create-shipping-address',
        'Magento_Checkout/js/checkout-data',
        'Magento_Checkout/js/model/checkout-data-resolver',
        'Magento_Customer/js/model/address-list',
        'Magento_Checkout/js/model/step-navigator',
        'uiRegistry',
        'Amazon_PayV2/js/action/checkout-session-address-load',
        'Amazon_PayV2/js/model/shipping-address/form-address-state',
        'Amazon_PayV2/js/amazon-checkout'
    ],
    function (
        $,
        Component,
        ko,
        quote,
        amazonStorage,
        addressConverter,
        createShippingAddress,
        checkoutData,
        checkoutDataResolver,
        addressList,
        stepNavigator,
        registry,
        checkoutSessionAddressLoad,
        shippingFormAddressState,
        amazonCheckout
    ) {
        'use strict';

        var self;

        require([amazonCheckout.getCheckoutModuleName()]);

        return Component.extend({
            defaults: {
                template: 'Amazon_PayV2/checkout-address'
            },
            isInitialized: ko.observable(false),

            /**
             * Init
             */
            initialize: function () {
                self = this;
                this._super();
                if (!amazonStorage.isPayOnly(true) && amazonStorage.isAmazonCheckout()) {
                    this.getShippingAddressFromAmazon();
                    if (amazonStorage.getIsEditPaymentFlag()) {
                        amazonStorage.setIsEditPaymentFlag(false);
                        stepNavigator.setHash('payment');
                    }
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
                            shippingFormAddressState.lastTelephone(checkoutProvider.shippingAddress.telephone);
                        }
                        if (!addressData.regionId) {
                            shippingFormAddressState.lastRegionId(checkoutProvider.shippingAddress.region_id);
                        }
                    }

                    // Save shipping address
                    addressConvert = addressConverter.quoteAddressToFormAddressData(addressData);
                    checkoutData.setShippingAddressFromData(addressConvert);
                    checkoutProvider.set('shippingAddress', addressConvert);

                    checkoutDataResolver.resolveEstimationAddress();

                    self.isInitialized(true);
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
