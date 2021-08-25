/*global define*/
define(
    [
        'jquery',
        'Amazon_Pay/js/model/storage'
    ],
    function (
        $,
        amazonStorage
    ) {
        'use strict';

        return function(Component) {
            if (!amazonStorage.isAmazonCheckout()) {
                return Component;
            }

            return Component.extend({

                /**
                 * Initialize shipping
                 */
                initialize: function () {
                    this._super();
                    this.isNewAddressAdded(true);
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
    }
);
