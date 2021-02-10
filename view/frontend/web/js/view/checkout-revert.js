/*global define*/
define(
    [
        'uiComponent',
        'Amazon_Pay/js/action/checkout-session-cancel',
        'Amazon_Pay/js/model/storage'
    ],
    function (
        Component,
        checkoutSessionCancelAction,
        amazonStorage
    ) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Amazon_Pay/checkout-revert'
            },
            isAmazonCheckout: amazonStorage.isAmazonCheckout(),

            /**
             * Init
             */
            initialize: function () {
                this._super();
            },

            /**
             * Revert checkout
             */
            revertCheckout: function () {
                checkoutSessionCancelAction(function () {
                    amazonStorage.clearAmazonCheckout();
                    window.location.replace(window.checkoutConfig.checkoutUrl);
                });
            }
        });
    }
);
