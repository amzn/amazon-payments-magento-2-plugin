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
        'Magento_Checkout/js/model/payment/additional-validators',
        'Magento_Checkout/js/model/quote',
        'uiRegistry',
        'Amazon_Pay/js/model/amazon-pay-config',
        'Amazon_Pay/js/model/billing-address/form-address-state',
        'Amazon_Pay/js/model/storage',
        'Amazon_Pay/js/action/checkout-session-address-load',
        'Amazon_Pay/js/action/checkout-session-payment-descriptor-load',
        'Amazon_Pay/js/action/place-order'
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
        additionalValidators,
        quote,
        registry,
        amazonConfig,
        billingFormAddressState,
        amazonStorage,
        checkoutSessionAddressLoad,
        checkoutSessionPaymentDescriptorLoad,
        placeOrderAction
    ) {
        'use strict';

        var self;

        return Component.extend({
            defaults: {
                isAmazonCheckout: ko.observable(amazonStorage.isAmazonCheckout()),
                isBillingAddressVisible: ko.observable(false),
                isIosc: ko.observable($('button.iosc-place-order-button').length > 0),
                paymentDescriptor: ko.observable(''),
                logo: 'Amazon_Pay/images/logo/Black-L.png',
                template: 'Amazon_Pay/payment/amazon-payment-method'
            },

            initialize: function () {
                self = this;
                parentComponent.prototype.initialize.apply(this, arguments);
                this.initChildren();
                if (amazonStorage.isAmazonCheckout()) {
                    this.initPaymentDescriptor();
                    this.initBillingAddress();
                    this.selectPaymentMethod();
                }
            },

            bindEditPaymentAction: function (elem) {
                var $elem = $(elem);
                amazon.Pay.bindChangeAction('#' + $elem.uniqueId().attr('id'), {
                    amazonCheckoutSessionId: amazonStorage.getCheckoutSessionId(),
                    changeAction: 'changePayment'
                });
            },

            getLogoUrl: function() {
                return require.toUrl(this.logo);
            },

            initPaymentDescriptor: function () {
                checkoutSessionPaymentDescriptorLoad(function (paymentDescriptor) {
                    self.paymentDescriptor(paymentDescriptor);
                });
            },

            initBillingAddress: function () {
                var checkoutProvider = registry.get('checkoutProvider');
                checkoutSessionAddressLoad('billing', function (amazonAddress) {
                    if ($.isEmptyObject(amazonAddress)) {
                        return;
                    }

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
                    checkoutProvider.set('billingAddress' + (window.checkoutConfig.displayBillingOnPaymentMethod ? self.getCode() : 'shared'), formAddress);
                    checkoutDataResolver.resolveBillingAddress();

                    self.isBillingAddressVisible(true);
                    billingFormAddressState.isLoaded(true);
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

                if (this.validate() && additionalValidators.validate()) {
                    //this.isPlaceOrderActionAllowed(false);
                    placeOrder = placeOrderAction(this.getData());
                }

                return false;
            },

            /**
             * Redirect Place Order clicks to Amazon button in Pay Now flow for OSC
             */
            payNow: function () {
                $('#PayWithAmazonButton')
                    .find('div')
                    .trigger('click');
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
