/*global define*/

define(
    [
        'jquery',
        'uiComponent',
        'Magento_Customer/js/model/customer',
        'Magento_Checkout/js/model/quote',
        'Amazon_PayV2/js/model/storage',
        'Magento_Checkout/js/model/shipping-service',
        'Magento_Checkout/js/model/address-converter',
        'Magento_Checkout/js/action/create-shipping-address',
        'Magento_Checkout/js/checkout-data',
        'Magento_Checkout/js/model/checkout-data-resolver',
        'Magento_Checkout/js/model/step-navigator',
        'uiRegistry',
        'Amazon_PayV2/js/action/checkout-session-address-load',
        'Amazon_PayV2/js/model/shipping-address/form-address-state',
        'Amazon_PayV2/js/amazon-checkout'
    ],
    function (
        $,
        Component,
        customer,
        quote,
        amazonStorage,
        shippingService,
        addressConverter,
        createShippingAddress,
        checkoutData,
        checkoutDataResolver,
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
            isCustomerLoggedIn: customer.isLoggedIn,
            isAmazonCheckout: amazonStorage.isAmazonCheckout(),
            isPayOnly: false,
            rates: shippingService.getShippingRates(),

            /**
             * Init
             */
            initialize: function () {
                self = this;
                this._super();
                if (!this.isPayOnly && this.isAmazonCheckout) {
                    this.getShippingAddressFromAmazon();
                    if (amazonStorage.getIsEditPaymentFlag()) {
                        amazonStorage.setIsEditPaymentFlag(false);
                        stepNavigator.setHash('payment');
                    }
                }
            },

            /**
             * Retrieve shipping address from Amazon API
             */
            getShippingAddressFromAmazon: function () {
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
                });
            },

            /**
             * Set email address
             * @param email
             */
            setEmail: function(email) {
                $('#customer-email').val(email).trigger('change');
                checkoutData.setInputFieldEmailValue(email);
                checkoutData.setValidatedEmailValue(email);
                quote.guestEmail = email;
            }
        });
    }
);
