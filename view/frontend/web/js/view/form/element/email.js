define([
    'jquery',
    'Magento_Checkout/js/view/form/element/email',
    'Magento_Customer/js/customer-data',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/checkout-data',
    'mage/validation'
], function ($, Component, customerData, quote, checkoutData) {
    'use strict';

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
});
