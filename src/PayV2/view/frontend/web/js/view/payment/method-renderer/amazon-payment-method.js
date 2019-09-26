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
                template: 'Amazon_PayV2/payment/amazon-payment-method'
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