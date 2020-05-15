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

    return Component.extend({
        defaults: {
            template: 'Amazon_PayV2/billing-address',
            actionsTemplate: 'Amazon_PayV2/billing-address/actions',
            detailsTemplate: 'Amazon_PayV2/billing-address/details',
            formTemplate: {
                name: 'Amazon_PayV2/billing-address/form',
                afterRender: function () {
                    self.triggerBillingDataValidateEvent();
                    var isValid = toggleFormFields(formSelector, false);
                    billingFormAddressState.isValid(isValid);
                    self.isAddressDetailsVisible(isValid);
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

        bindEditPaymentAction: function (elem) {
            var $elem = $(elem);
            amazon.Pay.bindChangeAction('#' + $elem.uniqueId().attr('id'), {
                amazonCheckoutSessionId: amazonStorage.getCheckoutSessionId(),
                changeAction: 'changePayment'
            });
            if (!amazonStorage.isPayOnly(true)) {
                $elem.click(function () {
                    amazonStorage.setIsEditPaymentFlag(true);
                });
            }
        }
    });
});
