define([
    'uiComponent',
    'Magento_Checkout/js/model/shipping-service',
    'Amazon_PayV2/js/action/toggle-shipping-form'
], function (Component, shippingService, toggleShippingForm) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Amazon_PayV2/shipping-address/inline-form'
        },

        /**
         * Init inline form
         */
        initObservable: function () {
            this._super();
            shippingService.isLoading.subscribe(function(isLoading) {
                if (!isLoading) {
                    toggleShippingForm.toggleFields();
                }
            });
            return this;
        },

        /**
         * Show/hide inline form depending on Amazon checkout status
         */
        manipulateInlineForm: function () {
            toggleShippingForm.toggleFields();
        }
    });
});
