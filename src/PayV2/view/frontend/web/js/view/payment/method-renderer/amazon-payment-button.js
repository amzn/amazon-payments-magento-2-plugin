define(
    [
        'jquery',
        'Magento_Checkout/js/view/payment/default',
        'Amazon_PayV2/js/action/place-order'
    ],
    function (
        $,
        Component,
        placeOrderAction
    ) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Amazon_PayV2/payment/amazon-payment-button'
            },

            initObservable: function () {
                this._super();
                return this;
            },

            /**
             * Save order
             */
            placeOrder: function (data, event) {
                return false;
            }

        });
    }
);