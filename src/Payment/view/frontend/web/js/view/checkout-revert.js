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
        'Magento_Checkout/js/model/url-builder'
    ],
    function (
        $,
        _,
        ko,
        Component,
        amazonStorage,
        storage,
        errorProcessor,
        urlBuilder
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
                var serviceUrl = urlBuilder.createUrl('/amazon/order-ref', {});
                storage.delete(
                    serviceUrl
                ).done(
                    function () {
                        amazonStorage.amazonlogOut();
                    }
                ).fail(
                    function (response) {
                        errorProcessor.process(response);
                    }
                );
            }
        });
    }
);
