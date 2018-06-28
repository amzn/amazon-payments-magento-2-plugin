/*global define*/
define(
    [
        'jquery',
        'underscore',
        'ko',
        'uiComponent',
        'Amazon_Payment/js/model/storage',
        'mage/storage',
        'Magento_Checkout/js/model/error-processor',
        'Magento_Checkout/js/model/url-builder',
        'Magento_Checkout/js/model/full-screen-loader',
        'uiRegistry'
    ],
    function (
        $,
        _,
        ko,
        Component,
        amazonStorage,
        storage,
        errorProcessor,
        urlBuilder,
        fullScreenLoader,
        registry
    ) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Amazon_Payment/checkout-revert'
            },
            isAmazonAccountLoggedIn: amazonStorage.isAmazonAccountLoggedIn,
            isAmazonEnabled: ko.observable(registry.get('amazonPayment').isPwaEnabled),

            /**
             * Init
             */
            initialize: function () {
                this._super();
            },

            /**
             * Revert checkout
             */
            revertCheckout: function () {
                var serviceUrl = urlBuilder.createUrl('/amazon/order-ref', {});

                fullScreenLoader.startLoader();
                storage.delete(
                    serviceUrl
                ).done(
                    function () {
                        amazonStorage.amazonlogOut();
                        window.location.reload();
                    }
                ).fail(
                    function (response) {
                        fullScreenLoader.stopLoader();
                        errorProcessor.process(response);
                    }
                );
            }
        });
    }
);
