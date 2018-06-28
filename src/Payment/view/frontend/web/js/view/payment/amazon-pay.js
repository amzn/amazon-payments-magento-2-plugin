define([
    'uiComponent',
    'Magento_Checkout/js/model/payment/renderer-list'

], function (
    Component,
    rendererList
) {
    'use strict';

    rendererList.push(
        {
            type: 'amazon_payment',
            component: 'Amazon_Payment/js/view/payment/method-renderer/amazon-payment-widget'
        }
    );

    /** Add view logic here if needed */
    return Component.extend({});
});
