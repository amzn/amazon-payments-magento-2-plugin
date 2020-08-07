/*global define*/
define(
    [
        'jquery',
        'Magento_Checkout/js/view/shipping',
        'Magento_Customer/js/model/customer',
        'Amazon_Payment/js/model/storage',
        'Amazon_Payment/js/messages'
    ],
    function (
        $,
        Component,
        customer,
        amazonStorage,
        amazonMessages
    ) {
        'use strict';

        return Component.extend({
            noShippingAddressSelectedMsg: 'No shipping address has been selected for this order, please try to refresh the page or add a new shipping address in the Address Book widget.',

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
                    if (!(amazonStorage.isAmazonShippingAddressSelected() && this.validateGuestEmail())) {
                        amazonMessages.addMessage('error', this.noShippingAddressSelectedMsg);
                        amazonMessages.displayMessages();

                        return false;
                    }
                }

                if (!(amazonStorage.isAmazonShippingAddressSelected())) {
                    amazonMessages.addMessage('error', this.noShippingAddressSelectedMsg);
                    amazonMessages.displayMessages();

                    return false;
                }

                return true;
            }
        });
    }
);
