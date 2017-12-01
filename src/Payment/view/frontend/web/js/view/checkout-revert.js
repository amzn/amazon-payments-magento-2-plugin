/*global define*/
define(
    [
        'jquery',
        "underscore",
        'ko',
        'uiComponent',
        'Amazon_Payment/js/model/storage',
        'mage/storage',
        'Magento_Checkout/js/model/error-processor',
        'Magento_Checkout/js/model/url-builder',
        'Magento_Checkout/js/model/full-screen-loader'
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
        fullScreenLoader
    ) {
        'use strict';

        var self;

        return Component.extend({
            defaults: {
                template: 'Amazon_Payment/checkout-revert'
            },
            isAmazonAccountLoggedIn: amazonStorage.isAmazonAccountLoggedIn,
            isAmazonEnabled: ko.observable(window.amazonPayment.isPwaEnabled),
            initialize: function () {
                self = this;
                this._super();
            },
            revertCheckout: function () {
                fullScreenLoader.startLoader();
                var serviceUrl = urlBuilder.createUrl('/amazon/order-ref', {});
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
