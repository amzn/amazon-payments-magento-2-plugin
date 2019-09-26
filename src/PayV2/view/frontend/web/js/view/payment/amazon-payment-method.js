define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list',
        'Amazon_PayV2/js/model/storage'
    ],
    function (
        Component,
        rendererList,
        amazonStorage
    ) {
        'use strict';

        if (amazonStorage.isAmazonCheckout()) {
            rendererList.push(
                {
                    type: 'amazon_payment_v2',
                    component: 'Amazon_PayV2/js/view/payment/method-renderer/amazon-payment-method'
                }
            );
        }

        return Component.extend({});
    }
);