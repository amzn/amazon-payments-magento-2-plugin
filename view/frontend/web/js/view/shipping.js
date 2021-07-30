/*global define*/
define(
    [
        'jquery',
        'Amazon_Pay/js/model/storage',
        'uiRegistry',
        'Magento_Checkout/js/model/checkout-data-resolver'
    ],
    function (
        $,
        amazonStorage,
        registry,
        checkoutDataResolver
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
                    this.showFormPopUp();
                    this._super();
                    this.isNewAddressAdded(true);
                    this.refreshShippingRegion();
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

                refreshShippingRegion: function() {
                    var checkoutProvider = registry.get('checkoutProvider');

                    checkoutProvider.on('shippingAddress.region_id', function (regionId) {
                        checkoutDataResolver.resolveEstimationAddress();
                    });
                }
            });
        }
    }
);
