define([
    'jquery',
    'Magento_Checkout/js/view/form/element/email',
    'Magento_Customer/js/customer-data',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/checkout-data',
    'mage/validation'
], function ($, Component, customerData, quote, checkoutData) {
    'use strict';

    function getAmazonCustomerEmail()
    {
        if (window.checkoutConfig.hasOwnProperty('amazonLogin') &&
            typeof window.checkoutConfig.amazonLogin.amazon_customer_email === "string"
        ) {
            return window.checkoutConfig.amazonLogin.amazon_customer_email;
        }
        return '';
    }

    return Component.extend({
        defaults: {
            email: checkoutData.getInputFieldEmailValue() || getAmazonCustomerEmail(),
            template: 'Amazon_Payment/form/element/email'
        },
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
