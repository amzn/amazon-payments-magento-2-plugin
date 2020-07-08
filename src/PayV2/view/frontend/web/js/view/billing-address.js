define([
    'jquery',
    'Magento_Checkout/js/view/billing-address',
    'Amazon_PayV2/js/action/toggle-form-fields',
    'Amazon_PayV2/js/model/storage',
    'Amazon_PayV2/js/model/billing-address/form-address-state'
], function ($, Component, toggleFormFields, amazonStorage, billingFormAddressState) {
    'use strict';

    if (!amazonStorage.isAmazonCheckout()) {
        // DO NOT EXTEND SHARED BILLING ADDRESS FORM IF AMAZON CHECKOUT IS NOT INITIATED
        return Component;
    }

    var self;
    var formSelector = '#amazon-billing-address form';

    return Component.extend({
        defaults: {
            template: 'Amazon_PayV2/billing-address',
            actionsTemplate: 'Amazon_PayV2/billing-address/actions',
            detailsTemplate: 'Amazon_PayV2/billing-address/details',
            formTemplate: {
                name: 'Amazon_PayV2/billing-address/form',
                afterRender: function () {
                    self.triggerBillingDataValidateEvent();
                    var isValid = toggleFormFields(formSelector, !self.isAddressEditable);
                    billingFormAddressState.isValid(isValid);
                    self.isAddressFormVisible(!isValid);
                    if (self.isAddressEditable) {
                        self.isAddressDetailsVisible(isValid);
                    }
                }
            },
            isAddressLoaded: billingFormAddressState.isLoaded,
            isAddressEditable: true,
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

        editAddress: function () {
            this._super();
            this.isAddressFormVisible(true);
        },

        cancelAddressEdit: function () {
            this._super();
            this.isAddressFormVisible(false);
        },

        updateAddress: function () {
            this._super();
            var isValid = !this.source.get('params.invalid');
            if (this.isAddressEditable) {
                this.isAddressFormVisible(!isValid);
            }
            billingFormAddressState.isValid(isValid);
        },

        canUseCancelBillingAddress: function () {
            return this.isAddressEditable ? this._super() : false;
        }
    });
});
