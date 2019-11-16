define([
    'jquery',
    'Magento_Checkout/js/view/shipping-address/address-renderer/default',
    'Amazon_PayV2/js/model/storage',
    'Amazon_PayV2/js/amazon-checkout'
], function ($, Component, amazonStorage, amazonCheckout) {
    'use strict';

    var editSelector = '.edit-address-link';

    if (!amazonStorage.isAmazonCheckout()) {
        return Component;
    }

    return Component.extend({
        defaults: {
            template: {
                name: Component.defaults.template,
                afterRender: function() {
                    if ($(editSelector).length) {
                        amazonCheckout.withAmazonCheckout(function(amazon) {
                            amazon.Pay.bindChangeAction(editSelector, {
                                amazonCheckoutSessionId: amazonStorage.getCheckoutSessionId(),
                                changeAction: 'changeAddress'
                            });
                        });
                    }
                }
            }
        },

        /**
         * Edit address (using amazon.Pay.bindChangeAction).
         */
        editAddress: function () { }
    });
});
