/*global define*/

define(
    [
        'jquery',
        "uiComponent",
        'ko',
        'Amazon_Payment/js/model/storage',
        'amazonPaymentConfig',
        'Magento_Customer/js/customer-data'
    ],
    function (
        $,
        Component,
        ko,
        amazonStorage,
        amazonPaymentConfig,
        customerData
    ) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Amazon_Payment/minicart/minicart-button'
            },
            isAmazonEnabled: ko.observable(amazonPaymentConfig.getValue('isPwaEnabled')),
            isAmazonAccountLoggedIn: amazonStorage.isAmazonAccountLoggedIn,
            isPwaVisible: amazonStorage.isPwaVisible,
            initialize: function () {
                var self = this;
                this._super();

                var minicart = $("[data-block='minicart']");

                // set observable flag on every minicart update
                minicart.on('contentUpdated', function () {
                    var quoteHasExcludedItems = customerData.get('cart')().amazon_quote_has_excluded_item;
                    amazonStorage.isQuoteDirty(quoteHasExcludedItems);
                });
            }
        });
    }
);
