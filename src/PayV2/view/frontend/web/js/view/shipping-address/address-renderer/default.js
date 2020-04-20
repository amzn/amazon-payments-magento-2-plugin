define([
    'jquery',
    'Magento_Checkout/js/view/shipping-address/address-renderer/default',
    'uiRegistry',
    'Amazon_PayV2/js/action/toggle-form-fields',
    'Amazon_PayV2/js/model/storage',
    'Amazon_PayV2/js/amazon-checkout'
], function ($, Component, registry, toggleFormFields, amazonStorage, amazonCheckout) {
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

                    var checkoutProvider = registry.get('checkoutProvider');
                    checkoutProvider.trigger('shippingAddress.data.validate');
                    if (checkoutProvider.get('shippingAddress.custom_attributes')) {
                        checkoutProvider.trigger('shippingAddress.custom_attributes.data.validate');
                    }

                    toggleFormFields('#co-shipping-form')
                }
            }
        },

        /**
         * Edit address (using amazon.Pay.bindChangeAction).
         */
        editAddress: function () { }
    });
});
