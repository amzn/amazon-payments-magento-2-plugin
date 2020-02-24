define(
    [
        'jquery',
        'Magento_Checkout/js/view/payment/default',
        'ko',
        'Magento_Customer/js/model/customer',
        'Magento_Customer/js/customer-data',
        'Magento_Checkout/js/model/quote',
        'Amazon_Payment/js/model/storage',
        'amazonCore',
        'mage/storage',
        'Magento_Checkout/js/model/full-screen-loader',
        'Amazon_Payment/js/action/place-order',
        'Magento_Checkout/js/action/get-totals',
        'Magento_Checkout/js/model/error-processor',
        'Magento_Checkout/js/model/address-converter',
        'Magento_Checkout/js/action/select-billing-address',
        'Magento_Checkout/js/model/payment/additional-validators',
        'Magento_Checkout/js/model/url-builder',
        'amazonPaymentConfig',
        'uiRegistry',
        'Amazon_Payment/js/messages'
    ],
    function (
        $,
        Component,
        ko,
        customer,
        customerData,
        quote,
        amazonStorage,
        amazonCore,
        storage,
        fullScreenLoader,
        placeOrderAction,
        getTotalsAction,
        errorProcessor,
        addressConverter,
        selectBillingAddress,
        additionalValidators,
        urlBuilder,
        amazonPaymentConfig,
        registry,
        amazonMessages
    ) {
        'use strict';

        var countryData = customerData.get('directory-data');

        return Component.extend({
            defaults: {
                template: 'Amazon_Payment/payment/amazon-payment-widget',
                paymentWidgetDOMId: 'walletWidgetDiv',
                presentmentDOMId: 'tr.totals.charge',
                apInputDOMId: '#amazon_payment',
                customerEmail: '#customer-email'
            },
            isCustomerLoggedIn: customer.isLoggedIn,
            isAmazonAccountLoggedIn: amazonStorage.isAmazonAccountLoggedIn,
            isPwaVisible: amazonStorage.isPwaVisible,
            shippingAddress: quote.shippingAddress,
            billingAddress: quote.billingAddress,
            isPlaceOrderDisabled: amazonStorage.isPlaceOrderDisabled,

            /**
             * Inits
             */
            initialize: function () {
                this._super();
            },

            /**
             * Init payment widget
             */
            initPaymentWidget: function () {
                var $amazonPayment = $(this.apInputDOMId);

                this.initDefaultValues();
                this.renderPaymentWidget();
                $amazonPayment.trigger('click'); //activate Amazon Pay method on render
                $amazonPayment.trigger('rendered');
            },

            /**
             * Init potentially asynchronous values
             */
            initDefaultValues: function () {
                registry.get('amazonPayment', function (amazonPayment) {
                    this.widgetScope = amazonPayment.loginScope;
                    this.sellerId = amazonPayment.merchantId;
                    this.presentmentCurrency = amazonPayment.presentmentCurrency;
                    this.useMultiCurrency = amazonPayment.useMultiCurrency;
                }.bind(this));
            },

            /**
             * render Amazon Pay Widget
             */
            renderPaymentWidget: function () {
                var widget = new OffAmazonPayments.Widgets.Wallet({ // eslint-disable-line no-undef
                    sellerId: this.sellerId,
                    scope: this.widgetScope,
                    amazonOrderReferenceId: amazonStorage.getOrderReference(),

                    /**
                     * Payment select callback
                     */
                    onPaymentSelect: function () { // orderReference
                        amazonStorage.isPlaceOrderDisabled(true);
                        this.setBillingAddressFromAmazon();
                    }.bind(this),
                    design: {
                        designMode: 'responsive'
                    },

                    /**
                     * Error callback
                     */
                    onError: amazonCore.handleWidgetError
                });
                if (this.useMultiCurrency) {
                    widget.setPresentmentCurrency(this.presentmentCurrency);
                    $(this.presentmentDOMId).hide();
                }
                else {
                    $(this.presentmentDOMId).show();
                }
                widget.bind(this.paymentWidgetDOMId);
                amazonMessages.displayMessages();
            },

            /**
             * Return payment code
             */
            getCode: function () {
                return 'amazon_payment';
            },

            /**
             * Is widget active?
             */
            isActive: function () {
                return true;
            },

            /**
             * Return country name
             */
            getCountryName: function (countryId) {
                return countryData()[countryId] !== undefined ? countryData()[countryId].name : '';
            },

            /**
             * Check if country name set
             */
            checkCountryName: function (countryId) {
                return countryData()[countryId] !== undefined;
            },

            /**
             * Save billing address
             */
            setBillingAddressFromAmazon: function () {
                var serviceUrl = urlBuilder.createUrl('/amazon-billing-address/:amazonOrderReference', {
                        amazonOrderReference: amazonStorage.getOrderReference()
                    }),
                    payload = {
                        addressConsentToken: amazonStorage.getAddressConsentToken()
                    };

                fullScreenLoader.startLoader();

                storage.put(
                    serviceUrl,
                    JSON.stringify(payload)
                ).done(
                    function (data) {
                        var amazonAddress = data.shift(), addressData;

                        addressData = addressConverter.formAddressDataToQuoteAddress(amazonAddress);
                        addressData.telephone = !addressData.telephone ? '0000000000' : addressData.telephone;

                        selectBillingAddress(addressData);
                        amazonStorage.isPlaceOrderDisabled(false);

                        if (window.checkoutConfig.amazonLogin.amazon_customer_email) {
                            var customerField = $(this.customerEmail).val();

                            if (!customerField) {
                                $(this.customerEmail).val(window.checkoutConfig.amazonLogin.amazon_customer_email);
                            }
                        }
                    }
                ).fail(
                    function (response) {
                        errorProcessor.process(response);
                    }
                ).always(
                    function () {
                        fullScreenLoader.stopLoader();
                    }
                );
            },

            /**
             * Return Magento billing object
             */
            getData: function () {
                return {
                    'method': this.item.method,
                    'additional_data': {
                        'sandbox_simulation_reference': amazonStorage.sandboxSimulationReference()
                    }
                };
            },

            /**
             * Save order
             */
            placeOrder: function (data, event) {
                var placeOrder;

                if (event) {
                    event.preventDefault();
                }

                if (this.validate() && additionalValidators.validate()) {
                    this.isPlaceOrderActionAllowed(false);
                    placeOrder = placeOrderAction(this.getData(), this.redirectAfterPlaceOrder);

                    $.when(placeOrder).fail(function () {
                        this.isPlaceOrderActionAllowed(true);
                    }.bind(this)).done(this.afterPlaceOrder.bind(this));

                    return true;
                }

                return false;
            }
        });
    }
);
