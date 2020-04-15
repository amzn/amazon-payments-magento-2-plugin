/*global define*/
define(
    [
        'jquery',
        'underscore',
        'ko',
        'Magento_Checkout/js/view/shipping',
        'Magento_Customer/js/model/customer',
        'Magento_Checkout/js/action/set-shipping-information',
        'Magento_Checkout/js/model/step-navigator',
        'Amazon_Payment/js/model/storage'
    ],
    function (
        $,
        _,
        ko,
        Component,
        customer,
        setShippingInformationAction,
        stepNavigator,
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
             * New setShipping Action for Amazon Pay to bypass validation
             */
            setShippingInformation: function () {

                /**
                 * Set Amazon shipping info
                 */
                function setShippingInformationAmazon() {
                    setShippingInformationAction().done(
                        function () {
                            stepNavigator.next();
                        }
                    );
                }

                if (amazonStorage.isAmazonAccountLoggedIn() && customer.isLoggedIn()) {
                    setShippingInformationAmazon();
                } else if (amazonStorage.isAmazonAccountLoggedIn() && !customer.isLoggedIn()) {

                    if (this.validateGuestEmail()) {
                        setShippingInformationAmazon();
                    }
                    //if using guest checkout or guest checkout with amazon pay we need to use the main validation
                } else if (this.validateShippingInformation()) {
                    setShippingInformationAmazon();
                }
            }
        });
    }
);
