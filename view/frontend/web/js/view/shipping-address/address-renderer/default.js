define([
    'jquery',
    'uiRegistry',
    'Amazon_Pay/js/action/toggle-form-fields',
    'Amazon_Pay/js/model/storage',
    'Amazon_Pay/js/amazon-checkout'
], function ($, registry, toggleFormFields, amazonStorage, amazonCheckout) {
    'use strict';

    return function(Component) {
        if (!amazonStorage.isAmazonCheckout()) {
            return Component;
        }

        var self;
        var editSelector = '.edit-address-link';

        return Component.extend({
            defaults: {
                template: {
                    name: Component.defaults.template,
                    afterRender: function () {
                        if ($(editSelector).length) {
                            amazonCheckout.withAmazonCheckout(function (amazon) {
                                amazon.Pay.bindChangeAction(editSelector, {
                                    amazonCheckoutSessionId: amazonStorage.getCheckoutSessionId(),
                                    changeAction: 'changeAddress'
                                });
                            });
                        }
                        self.toggleShippingAddressFormFields();
                    }
                }
            },

            /** @inheritdoc */
            initObservable: function () {
                self = this;
                this._super();
                this.address.subscribe(function () {
                    self.toggleShippingAddressFormFields();
                });
                return this;
            },

            toggleShippingAddressFormFields: function () {
                var checkoutProvider = registry.get('checkoutProvider');
                checkoutProvider.trigger('shippingAddress.data.validate');
                if (checkoutProvider.get('shippingAddress.custom_attributes')) {
                    checkoutProvider.trigger('shippingAddress.custom_attributes.data.validate');
                }

                toggleFormFields('#co-shipping-form', true);
            },

            /**
             * Edit address (using amazon.Pay.bindChangeAction).
             */
            editAddress: function () {
            }
        });
    }
});
