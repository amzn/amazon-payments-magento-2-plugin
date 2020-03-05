define([
    'jquery',
    'Magento_Checkout/js/view/billing-address',
    'Magento_Checkout/js/model/quote',
    'Amazon_PayV2/js/model/storage',
    'Amazon_PayV2/js/model/billing-address/form-address-state'
], function ($, Component, quote, amazonStorage, billingFormAddressState) {
    'use strict';

    var self;
    var formSelector = '#amazon-payment form';
    var editSelector = '#amazon-payment .action-edit-address';

    return Component.extend({
        defaults: {
            template: 'Amazon_PayV2/billing-address',
            isAmazonButtonVisible: !amazonStorage.isAmazonCheckout(),
            actionsTemplate: {
                name: 'Amazon_PayV2/billing-address/actions',
                afterRender: function () {
                    if ($(editSelector).length) {
                        amazon.Pay.bindChangeAction(editSelector, {
                            amazonCheckoutSessionId: amazonStorage.getCheckoutSessionId(),
                            changeAction: 'changePayment'
                        });
                    }
                }
            },
            buttonTemplate: 'Amazon_PayV2/billing-address/button',
            detailsTemplate: 'Amazon_PayV2/billing-address/details',
            formTemplate: {
                name: 'Amazon_PayV2/billing-address/form',
                afterRender: function () {
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
        }
    });
});
