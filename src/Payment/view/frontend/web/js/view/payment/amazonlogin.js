define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';
        rendererList.push(
            {
                type: 'amazonlogin',
                component: 'Amazon_Payment/js/view/payment/method-renderer/amazonlogin'
            }
        );

    // Add view logic here if needed

        return Component.extend({});
    }
);
