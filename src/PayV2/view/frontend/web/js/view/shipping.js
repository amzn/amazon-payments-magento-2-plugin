/*global define*/
define(
    [
        'jquery',
        'underscore',
        'ko',
        'Magento_Checkout/js/view/shipping',
        'Magento_Customer/js/model/customer',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/action/set-shipping-information',
        'Magento_Checkout/js/model/step-navigator',
        'Amazon_PayV2/js/model/storage',
        'Amazon_PayV2/js/action/toggle-shipping-form'
    ],
    function (
        $,
        _,
        ko,
        Component,
        customer,
        quote,
        setShippingInformationAction,
        stepNavigator,
        amazonStorage,
        toggleShippingForm
    ) {
        'use strict';

        return Component.extend({

            /**
             * Initialize shipping
             */
            initialize: function () {
                this._super();
                if (amazonStorage.isAmazonCheckout()) {
                    this.isNewAddressAdded(true);
                }
                return this;
            },

            /**
             * Validate guest email
             */
            validateGuestEmail: function () {
                var loginFormSelector = 'form[data-role=email-with-possible-login]';

                $(loginFormSelector).validation();

                return $(loginFormSelector + ' input[type=email]').valid();
            },

            /**
             * "Next"
             */
            setShippingInformation: function () {
                toggleShippingForm.toggleFields(); // Display error fields if needed
                this._super();
            }
        });
    }
);
