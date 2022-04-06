define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list',
        'Amazon_Pay/js/model/amazon-pay-config',
        'Amazon_Pay/js/model/storage'
    ],
    function (
        Component,
        rendererList,
        amazonConfig,
        amazonStorage
    ) {
        'use strict';

        if ((amazonStorage.isAmazonCheckout() || amazonConfig.getValue('is_method_available'))
            && !amazonConfig.getValue('has_restricted_products')) {
            rendererList.push(
                {
                    type: amazonConfig.getCode(),
                    component: 'Amazon_Pay/js/view/payment/method-renderer/amazon-payment-method'
                }
            );
        }

        return Component.extend({});
    }
);
