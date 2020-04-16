define([
    'jquery',
    'Magento_Checkout/js/view/billing-address',
    'Magento_Checkout/js/model/quote',
    'Amazon_PayV2/js/action/bind-amazon-change-action',
    'Amazon_PayV2/js/model/storage',
    'Amazon_PayV2/js/model/billing-address/form-address-state'
], function ($, Component, quote, bindAmazonChangeAction, amazonStorage, billingFormAddressState) {
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
                        bindAmazonChangeAction(editSelector, 'changePayment');
                    }
                }
            },
            formTemplate: {
                name: 'Amazon_PayV2/billing-address/form',
                afterRender: function () {
                    self.triggerBillingDataValidateEvent();

                    var $form = $(formSelector);
                    $form.find('.field').hide();

                    var $errorFields = $form.find('.field._error');
                    $errorFields.show();

                    var isValid = $errorFields.length === 0;
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
