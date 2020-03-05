define(
    [
        'ko',
        'jquery',
        'Magento_Checkout/js/view/payment/default',
        'uiComponent',
        'Magento_Checkout/js/checkout-data',
        'Magento_Checkout/js/model/checkout-data-resolver',
        'uiRegistry',
        'Amazon_PayV2/js/model/billing-address/form-address-state',
        'Amazon_PayV2/js/action/place-order'
    ],
    function (
        ko,
        $,
        Component,
        parentComponent,
        checkoutData,
        checkoutDataResolver,
        registry,
        billingFormAddressState,
        placeOrderAction
    ) {
        'use strict';

        return Component.extend({
            defaults: {
                isPlaceOrderActionAllowed: ko.observable(false),
                template: 'Amazon_PayV2/payment/amazon-payment-method'
            },

            initialize: function () {
                // NOTE: This method mostly copies this._super() implementation
                var billingAddressCode,
                    billingAddressData,
                    defaultAddressData;

                // BEGIN OF DIFF
                parentComponent.prototype.initialize.apply(this, arguments);
                this.initChildren();
                // Enable Place Order only if billing address is valid
                billingFormAddressState.isValid.subscribe(function (isValid) {
                    this.isPlaceOrderActionAllowed(isValid);
                }, this);
                // END OF DIFF
                checkoutDataResolver.resolveBillingAddress();

                billingAddressCode = 'billingAddress' + this.getCode();
                registry.async('checkoutProvider')(function (checkoutProvider) {
                    defaultAddressData = checkoutProvider.get(billingAddressCode);

                    if (defaultAddressData === undefined) {
                        // Skip if payment does not have a billing address form
                        return;
                    }
                    billingAddressData = checkoutData.getBillingAddressFromData();

                    if (billingAddressData) {
                        checkoutProvider.set(
                            billingAddressCode,
                            $.extend(true, {}, defaultAddressData, billingAddressData)
                        );
                    }
                    checkoutProvider.on(billingAddressCode, function (providerBillingAddressData) {
                        checkoutData.setBillingAddressFromData(providerBillingAddressData);
                    }, billingAddressCode);
                });

                return this;
            },

            initObservable: function () {
                this._super();
                this.selectPaymentMethod();
                return this;
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
            }

        });
    }
);
