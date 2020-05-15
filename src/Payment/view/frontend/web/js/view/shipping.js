/*global define*/
define(
    [
        'jquery',
        'Magento_Checkout/js/view/shipping',
        'Magento_Customer/js/model/customer',
        'Amazon_Payment/js/model/storage'
    ],
    function (
        $,
        Component,
        customer,
        amazonStorage
    ) {
        'use strict';

        return Component.extend({

            /**
             * Initialize shipping
             */
            initialize: function () {
                this._super();
                this.isNewAddressAdded(amazonStorage.isAmazonAccountLoggedIn());
                amazonStorage.isAmazonAccountLoggedIn.subscribe(function (value) {
                    this.isNewAddressAdded(value);
                }, this);

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
             * Overridden validateShippingInformation for Amazon Pay to bypass validation
             *
             * @inheritDoc
             */
            validateShippingInformation: function () {
                if (!amazonStorage.isAmazonAccountLoggedIn()) {
                    return this._super();
                }

                if (!customer.isLoggedIn()) {
                    return this.validateGuestEmail();
                }

                return true;
            }
        });
    }
);
