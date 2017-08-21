/*global define*/
define(
    [
        'jquery',
        "underscore",
        'ko',
        'Magento_Checkout/js/view/shipping',
        'Magento_Customer/js/model/customer',
        'Magento_Checkout/js/action/set-shipping-information',
        'Magento_Checkout/js/model/step-navigator',
        'Amazon_Payment/js/model/storage',
        'Magento_Checkout/js/model/shipping-service',
        'Magento_Checkout/js/model/quote',
        'Magento_Customer/js/model/address-list'
    ],
    function (
        $,
        _,
        ko,
        Component,
        customer,
        setShippingInformationAction,
        stepNavigator,
        amazonStorage,
        shippingService,
        quote,
        addressList
    ) {
        'use strict';
        return Component.extend({
            initialize: function () {
                this.isFormInline = !window.checkoutConfig.isCustomerLoggedIn;
                this._super();
                return this;
            },
            initObservable: function () {
                this._super();
                amazonStorage.isAmazonAccountLoggedIn.subscribe(function (value) {
                    this.isNewAddressAdded(value);
                }, this);
                return this;
            },
            validateGuestEmail: function () {
                var loginFormSelector = 'form[data-role=email-with-possible-login]';
                $(loginFormSelector).validation();
                return $(loginFormSelector + ' input[type=email]').valid();
            },
            /**
             * New setShipping Action for Amazon Pay to bypass validation
             */
            setShippingInformation: function () {
                function setShippingInformationAmazon()
                {
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
                } else {
                    //if using guest checkout or guest checkout with amazon pay we need to use the main validation
                    if (this.validateShippingInformation()) {
                        setShippingInformationAmazon();
                    }
                }
            }
        });
    }
);
