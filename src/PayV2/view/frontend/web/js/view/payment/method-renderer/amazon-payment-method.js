define(
    [
        'ko',
        'jquery',
        'Magento_Checkout/js/view/payment/default',
        'uiComponent',
        'Magento_Checkout/js/action/create-billing-address',
        'Magento_Checkout/js/checkout-data',
        'Magento_Checkout/js/model/address-converter',
        'Magento_Checkout/js/model/checkout-data-resolver',
        'Magento_Checkout/js/model/quote',
        'uiRegistry',
        'Amazon_PayV2/js/model/billing-address/form-address-state',
        'Amazon_PayV2/js/model/storage',
        'Amazon_PayV2/js/action/checkout-session-address-load',
        'Amazon_PayV2/js/action/place-order'
    ],
    function (
        ko,
        $,
        Component,
        parentComponent,
        createBillingAddress,
        checkoutData,
        addressConverter,
        checkoutDataResolver,
        quote,
        registry,
        billingFormAddressState,
        amazonStorage,
        checkoutSessionAddressLoad,
        placeOrderAction
    ) {
        'use strict';

        var self;

        return Component.extend({
            defaults: {
                isAmazonButtonVisible: ko.observable(!amazonStorage.isAmazonCheckout()),
                isBillingAddressVisible: ko.observable(false),
                isPlaceOrderActionAllowed: ko.observable(false),
                template: 'Amazon_PayV2/payment/amazon-payment-method'
            },

            initialize: function () {
                self = this;
                parentComponent.prototype.initialize.apply(this, arguments);
                this.initChildren();
                if (amazonStorage.isAmazonCheckout()) {
                    this.initBillingAddress();
                }
            },

            initBillingAddress: function () {
                billingFormAddressState.isValid.subscribe(function (isValid) {
                    this.isPlaceOrderActionAllowed(isValid);
                }, this);

                var billingAddressCode = 'billingAddress' + this.getCode();
                var checkoutProvider = registry.get('checkoutProvider');
                checkoutSessionAddressLoad('billing', function (amazonAddress) {
                    self.setEmail(amazonAddress.email);

                    var quoteAddress = createBillingAddress(amazonAddress);

                    // Fill in blank street fields
                    if ($.isArray(quoteAddress.street)) {
                        for (var i = quoteAddress.street.length; i <= 2; i++) {
                            quoteAddress.street[i] = '';
                        }
                    }

                    // Amazon does not return telephone or non-US regionIds, so use previous provider values
                    var checkoutShipping = $.extend(true, {}, checkoutProvider.shippingAddress);
                    var checkoutBilling = $.extend(true, {}, checkoutProvider.billingAddress);
                    if (!quoteAddress.telephone) {
                        quoteAddress.telephone = checkoutBilling.telephone || checkoutShipping.telephone;
                    }
                    if (!quoteAddress.regionId) {
                        quoteAddress.regionId = checkoutBilling.region_id;
                    }

                    // Save billing address
                    checkoutData.setSelectedBillingAddress(quoteAddress.getKey());
                    var formAddress = addressConverter.quoteAddressToFormAddressData(quoteAddress);
                    checkoutData.setBillingAddressFromData(formAddress);
                    checkoutData.setNewCustomerBillingAddress(formAddress);
                    checkoutProvider.set(billingAddressCode, formAddress);
                    checkoutDataResolver.resolveBillingAddress();

                    self.isBillingAddressVisible(true);
                });
            },

            /**
             * Save order
             */
            placeOrder: function (data, event) {
                var placeOrder;

                if (event) {
                    event.preventDefault();
                }

                if (this.validate()) {
                    //this.isPlaceOrderActionAllowed(false);
                    placeOrder = placeOrderAction(this.getData());
                }

                return false;
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
