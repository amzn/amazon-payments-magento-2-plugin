define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list',
        'Amazon_PayV2/js/model/amazon-payv2-config',
        'Amazon_PayV2/js/model/storage'
    ],
    function (
        Component,
        rendererList,
        amazonConfig,
        amazonStorage
    ) {
        'use strict';

        if (!amazonStorage.isAmazonCheckout()) {
            rendererList.push(
                {
                    type: amazonConfig.getCode(),
                    component: 'Amazon_PayV2/js/view/payment/method-renderer/amazon-payment-button'
                }
            );
        }

        return Component.extend({});
    }
);
