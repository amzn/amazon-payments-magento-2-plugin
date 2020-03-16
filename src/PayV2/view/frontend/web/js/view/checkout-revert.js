/*global define*/
define(
    [
        'uiComponent',
        'Amazon_PayV2/js/action/checkout-session-cancel',
        'Amazon_PayV2/js/model/storage'
    ],
    function (
        Component,
        checkoutSessionCancelAction,
        amazonStorage
    ) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Amazon_PayV2/checkout-revert'
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
                    amazonStorage.revertCheckout();
                    window.location.replace(window.checkoutConfig.checkoutUrl);
                });
            }
        });
    }
);
