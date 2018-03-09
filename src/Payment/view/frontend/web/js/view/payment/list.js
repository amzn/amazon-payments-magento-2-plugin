define([
    'jquery',
    'underscore',
    'ko',
    'Magento_Checkout/js/view/payment/list',
    'Magento_Checkout/js/model/payment/method-list',
    'Magento_Checkout/js/model/checkout-data-resolver',
    'Magento_Checkout/js/model/address-converter',
    'Magento_Checkout/js/model/quote',
    'Amazon_Payment/js/action/populate-shipping-address',
    'Amazon_Payment/js/model/storage'

], function (
    $,
    _,
    ko,
    Component,
    paymentMethods,
    checkoutDataResolver,
    addressConverter,
    quote,
    populateShippingAddress,
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

            this._setupDeclineHandler();
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
            return amazonStorage.isAmazonAccountLoggedIn() && method !== 'amazon_payment' && method !== 'free';
        },

        /**
         * handle decline codes
         * @private
         */
        _setupDeclineHandler: function () {
            amazonStorage.amazonDeclineCode.subscribe(function (declined) {
                switch (declined) {
                    //hard decline
                    case 4273:
                        //populate shipping form
                        populateShippingAddress();
                        amazonStorage.amazonlogOut();
                        this._reloadPaymentMethods();
                        amazonStorage.amazonDeclineCode(false);
                        break;
                    //soft decline
                    case 7638:
                        amazonStorage.isPlaceOrderDisabled(true);
                        this._reInitializeAmazonWalletWidget();
                        this._hideEditableOptions();
                        amazonStorage.amazonDeclineCode(false);
                        break;
                    default:
                        amazonStorage.amazonDeclineCode(false);
                        break;
                }
            }, this);
        },

        /**
         * When payment methods exist on load hook into widget render to remove when widget has rendered
         * @private
         */
        _hidePaymentMethodsOnLoad: function () {
            if (paymentMethods().length > 0) {
                //if the payment methods are already set
                $(document).on('rendered', '#amazon_payment', function () {
                    _.each(paymentMethods(), function (payment) {
                        if (this._shouldRemovePaymentMethod(payment.method)) {
                            this.removeRenderer(payment.method);
                        }
                    }, self);
                });
            }
        },

        /**
         * reload payment methods on decline
         * @private
         */
        _reloadPaymentMethods: function () {
            _.each(paymentMethods(), function (paymentMethodData) {
                if (paymentMethodData.method === 'amazon_payment' && !amazonStorage.isAmazonAccountLoggedIn()) {
                    this.removeRenderer(paymentMethodData.method);
                } else {
                    this.createRenderer(paymentMethodData);
                }
            }, this);
        },

        /**
         * re-intialises Amazon wallet widget
         * @private
         */
        _reInitializeAmazonWalletWidget: function () {
            var child = this.getChild('amazon_payment');

            if (child) {
                child.renderPaymentWidget();
            }
        },

        /**
         * hides editable content and links to prevent unexptect behaviour
         * @private
         */
        _hideEditableOptions: function () {
            $('.payment-option.discount-code', '#payment').remove();
            $('.action-edit', '.shipping-information').remove();
            $('.opc-progress-bar-item._complete', '.opc-progress-bar').addClass('lock-step');
        }
    });
});
