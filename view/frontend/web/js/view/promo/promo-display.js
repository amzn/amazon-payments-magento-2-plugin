define([
    'jquery',
    'Amazon_Pay/js/amazon-checkout'
], function ($, amazonCheckout) {
    'use strict';



    return function (config) {

        $(document).ready(function () {

            amazonCheckout.withAmazonCheckout(function (amazon, args) {

                amazon.Pay.renderPromotionalMessage('#promotional-message', {
                    environment: config.environment,
                    merchantId: config.merchantId,
                    checkoutLanguage: config.languageCode,
                    productType: 'PayAndShip',
                    amount: {
                        value: config.amountValue,
                        currencyCode: config.currencyCode
                    },
                    placement: config.placement,
                    style: {
                        textColor: config.fontColor,
                        textSize: config.fontSize
                    }
                });
            });


        });
    };
});
