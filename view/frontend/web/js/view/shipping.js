/*global define*/
define(
    [
        'jquery',
        'Magento_Checkout/js/view/shipping',
        'Amazon_PayV2/js/model/storage'
    ],
    function (
        $,
        Component,
        amazonStorage
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
            }
        });
    }
);
