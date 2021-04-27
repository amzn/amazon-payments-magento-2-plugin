define([
    'jquery',
    'Amazon_Pay/js/action/toggle-form-fields',
    'Amazon_Pay/js/model/storage',
    'Amazon_Pay/js/model/billing-address/form-address-state'
], function ($, toggleFormFields, amazonStorage, billingFormAddressState) {
    'use strict';

    return function(Component) {
        if (!amazonStorage.isAmazonCheckout()) {
            // DO NOT EXTEND SHARED BILLING ADDRESS FORM IF AMAZON CHECKOUT IS NOT INITIATED
            return Component;
        }

        var self;
        var formSelector = '#amazon-billing-address form';

        return Component.extend({
            defaults: {
                template: 'Amazon_Pay/billing-address',
                actionsTemplate: 'Amazon_Pay/billing-address/actions',
                detailsTemplate: 'Amazon_Pay/billing-address/details',
                formTemplate: {
                    name: 'Amazon_Pay/billing-address/form',
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
    }
});
