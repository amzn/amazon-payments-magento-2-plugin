define([
    'jquery',
    'Magento_Checkout/js/view/billing-address',
    'Magento_Checkout/js/model/quote',
    'Amazon_PayV2/js/action/toggle-form-fields',
    'Amazon_PayV2/js/model/storage',
    'Amazon_PayV2/js/model/billing-address/form-address-state'
], function ($, Component, quote, toggleFormFields, amazonStorage, billingFormAddressState) {
    'use strict';

    var self;
    var formSelector = '#amazon-payment form';
    var editSelector = '#amazon-payment .action-edit-address';

    return Component.extend({
        defaults: {
            template: 'Amazon_PayV2/billing-address',
            isAddressFormVisible: false,
            actionsTemplate: 'Amazon_PayV2/billing-address/actions',
            detailsTemplate: {
                name: 'Amazon_PayV2/billing-address/details',
                afterRender: function () {
                    if ($(editSelector).length) {
                        amazon.Pay.bindChangeAction(editSelector, {
                            amazonCheckoutSessionId: amazonStorage.getCheckoutSessionId(),
                            changeAction: 'changePayment'
                        });
                    }
                }
            },
            formTemplate: {
                name: 'Amazon_PayV2/billing-address/form',
                afterRender: function () {
                    self.triggerBillingDataValidateEvent();
                    var isValid = toggleFormFields(formSelector);
                    billingFormAddressState.isValid(isValid);
                    self.isAddressFormVisible(!isValid);
                }
            },
        },

        /**
         * @returns {exports}
         */
        initialize: function () {
            this._super();
            self = this;
            return this;
        },

        triggerBillingDataValidateEvent: function () {
            this.source.trigger(this.dataScopePrefix + '.data.validate');

            if (this.source.get(this.dataScopePrefix + '.custom_attributes')) {
                this.source.trigger(this.dataScopePrefix + '.custom_attributes.data.validate');
            }
        },

        updateAddress: function () {
            this._super();
            billingFormAddressState.isValid(!this.source.get('params.invalid'));
        },

        cancelAddressEdit: function () {
            quote.billingAddress(null);
            amazonStorage.clearAmazonCheckout();
            window.location = window.checkoutConfig.checkoutUrl;
        },

        editAddress: function () {
            if (!amazonStorage.isPayOnly(true)) {
                amazonStorage.setIsEditPaymentFlag(true);
            }
        }
    });
});
