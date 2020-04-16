define([
    'jquery',
    'Magento_Checkout/js/view/shipping-address/address-renderer/default',
    'Amazon_PayV2/js/model/storage',
    'Amazon_PayV2/js/action/bind-amazon-change-action'
], function ($, Component, amazonStorage, bindAmazonChangeAction) {
    'use strict';

    var editSelector = '.shipping-address-item .edit-address-link';

    if (!amazonStorage.isAmazonCheckout()) {
        return Component;
    }

    return Component.extend({
        defaults: {
            template: {
                name: Component.defaults.template,
                afterRender: function() {
                    if ($(editSelector).length) {
                        bindAmazonChangeAction(editSelector, 'changeAddress');
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
