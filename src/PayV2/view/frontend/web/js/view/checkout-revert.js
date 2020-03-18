/*global define*/
define(
    [
        'uiComponent',
        'Amazon_PayV2/js/model/storage'
    ],
    function (
        Component,
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
                amazonStorage.clearAmazonCheckout();
                window.location.replace(window.checkoutConfig.checkoutUrl);
            }
        });
    }
);
