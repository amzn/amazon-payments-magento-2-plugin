define([
    'jquery',
    'Amazon_Pay/js/amazon-checkout',
    'Magento_Catalog/js/price-box'
], function ($, amazonCheckout, priceBox) {
    'use strict';

    return function (config) {

        $(document).ready(function () {

            amazonCheckout.withAmazonCheckout(function (amazon) {
                if (config.amountValue > 0) {
                    renderPromotionalMessage(amazon);
                } else {
                    // if value (price) is not set on load we will need to watch for price updates
                    priceSubscribe(amazon);
                }
            });

            function priceSubscribe(amazon) {
                $('.price-box').on('updatePrice', function (event) {
                    // Targets the more general Magento core priceBox & functionality associated
                    const prices = $(event.target).data('magePriceBox').cache.displayPrices;

                    // We need a price amount value and it needs to be greater than zero
                    if (prices && prices.finalPrice && prices.finalPrice.amount > 0) {
                        // Updating config value, originally passed via template
                        config.amountValue = prices.finalPrice.amount;

                        // Clear the current contents of the banner area
                        cleanUpElements();

                        renderPromotionalMessage(amazon);
                    }
                });
            }

            /**
             * Makes request to amazon for the buy now pay later endpoint
             *
             * @param amazon
             */
            function renderPromotionalMessage(amazon) {
                amazon.Pay.renderPromotionalMessage(config.bannerSelector, {
                    environment: config.environment,
                    merchantId: config.merchantId,
                    checkoutLanguage: config.languageCode,
                    productType: config.productType,
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
            }

            /**
             * Amazon adds a shadow root to the banner element.
             * When we need to use a variable price value, subsequent requests are made
             * and thus attempts to add the shadow root. Need to remove the first
             */
            function cleanUpElements() {
                const oldElement = $(config.bannerSelector)[0];
                // Clones the element without its children or shadow root
                const newElement = oldElement.cloneNode(false);

                oldElement.parentNode.replaceChild(newElement, oldElement);

                // Each request also appends one of these modals so we'll remove the present one
                $('.amazonpay-dmw-learn-more-modal').remove();
            }
        });
    };
});
