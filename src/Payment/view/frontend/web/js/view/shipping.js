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
        'Amazon_Payment/js/model/storage',
        'Magento_Customer/js/model/address-list',
        'Magento_Checkout/js/model/quote',
        'mage/translate'
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
        addressList,
        quote,
        $t
    ) {
        'use strict';

        return Component.extend({
            isFormInline: ko.observable(addressList().length === 0),
            formSelector: '#co-shipping-form',

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
                    this.isFormInline(true);

                    if (this.validateShippingInformation()) {
                        setShippingInformationAmazon();
                    }
                } else if (amazonStorage.isAmazonAccountLoggedIn() && !customer.isLoggedIn()) {

                    if (this.validateGuestEmail() && this.validateShippingInformation()) {
                        setShippingInformationAmazon();
                    }
                //if using guest checkout or guest checkout with amazon pay we need to use the main validation
                } else if (this.validateShippingInformation()) {
                    setShippingInformationAmazon();
                }
            },

            /**
             * Validate shipping method
             */
            validateShippingInformation: function () {
                var loginFormSelector = 'form[data-role=email-with-possible-login]',
                    emailValidationResult = customer.isLoggedIn(),
                    errorCount = 0,
                    elem;

                if (!quote.shippingMethod()) {
                    this.errorValidationMessage($t('Please specify a shipping method.'));

                    return false;
                }

                if (!customer.isLoggedIn()) {
                    $(loginFormSelector).validation();
                    emailValidationResult = Boolean($(loginFormSelector + ' input[name=username]').valid());
                }

                if (this.isFormInline()) {
                    this.source.set('params.invalid', false);
                    this.source.trigger('shippingAddress.data.validate');

                    if (this.source.get('shippingAddress.custom_attributes')) {
                        this.source.trigger('shippingAddress.custom_attributes.data.validate');
                    }

                    // jscs:disable requireCamelCaseOrUpperCaseIdentifiers

                    if (this.source.get('params.invalid') ||
                        !quote.shippingMethod().method_code ||
                        !quote.shippingMethod().carrier_code ||
                        !emailValidationResult
                    ) {
                        // jscs:enable requireCamelCaseOrUpperCaseIdentifiers

                        $(this.formSelector).find('.field').each(function () {
                            if ($(this).hasClass('_error')) {
                                errorCount++;
                                $(this).show();
                            } else {
                                $(this).css('display', 'none');
                            }
                        });

                        elem = $(this.formSelector);

                        if (elem && errorCount > 0) { // eslint-disable-line max-depth
                            $(this.formSelector).show();
                        } else {
                            $(this.formSelector).hide();
                        }

                        return false;
                    }
                }

                return this._super();
            }
        });
    }
);
