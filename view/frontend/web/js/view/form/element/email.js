define([
    'jquery',
    'Magento_Customer/js/customer-data',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/checkout-data',
    'Amazon_Pay/js/model/storage',
    'mage/validation'
], function ($, customerData, quote, checkoutData, amazonStorage) {
    'use strict';

    return function(Component) {
        if (!amazonStorage.isEnabled) {
            return Component;
        }

        return Component.extend({
            defaults: {
                email: checkoutData.getInputFieldEmailValue(),
                template: 'Amazon_Pay/form/element/email'
            },

            /**
             * Init email validator
             */
            initialize: function () {
                this._super();

                if (this.email()) {

                    if ($.validator.methods['validate-email'].call(this, this.email())) {
                        quote.guestEmail = this.email();
                        checkoutData.setValidatedEmailValue(this.email());
                    }
                    checkoutData.setInputFieldEmailValue(this.email());
                }

                return this;
            }
        });
    }
});
