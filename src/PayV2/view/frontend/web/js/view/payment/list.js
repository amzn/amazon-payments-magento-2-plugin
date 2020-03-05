define([
    'jquery',
    'underscore',
    'ko',
    'Magento_Checkout/js/view/payment/list',
    'Magento_Checkout/js/model/payment/method-list',
    'Magento_Checkout/js/model/checkout-data-resolver',
    'Magento_Checkout/js/model/address-converter',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/action/select-payment-method',
    'Amazon_PayV2/js/model/amazon-payv2-config',
    'Amazon_PayV2/js/model/storage'

], function (
    $,
    _,
    ko,
    Component,
    paymentMethods,
    checkoutDataResolver,
    addressConverter,
    quote,
    selectPaymentMethodAction,
    amazonConfig,
    amazonStorage
) {
    'use strict';

    var self;

    return Component.extend({
        /**
         * Initialize view.
         *
         * @returns {Component} Chainable.
         */
        initialize: function () {
            self = this;
            this._hidePaymentMethodsOnLoad(); //hide methods on load

            //subscribe to payment methods to remove other payment methods from render list
            paymentMethods.subscribe(function (changes) {
                checkoutDataResolver.resolvePaymentMethod();
                //remove renderer for "deleted" payment methods
                _.each(changes, function (change) {
                    if (this._shouldRemovePaymentMethod(change.value.method)) {
                        this.removeRenderer(change.value.method);
                        change.status = 'deleted';
                    }
                }, this);
            }, this, 'arrayChange');

            this._super();

            return this;
        },

        /**
         * Check if a payment method is applicable with Amazon Pay
         * @param {String} method
         * @returns {Boolean}
         * @private
         */
        _shouldRemovePaymentMethod: function (method) {
            return amazonStorage.isAmazonCheckout() && method !== amazonConfig.getCode() && method !== 'free';
        },

        /**
         * When payment methods exist on load hook into widget render to remove when widget has rendered
         * @private
         */
        _hidePaymentMethodsOnLoad: function () {
            if (paymentMethods().length > 0) {
                //if the payment methods are already set
                $(document).on('rendered', '#amazon-payment', function () {
                    _.each(paymentMethods(), function (payment) {
                        if (this._shouldRemovePaymentMethod(payment.method)) {
                            this.removeRenderer(payment.method);
                        }
                    }, self);
                });
            }
        }
    });
});
