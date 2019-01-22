define(
    [
        'Magento_Checkout/js/view/payment/default',
        'Amazon_Payment/js/model/storage',
    ],
    function (
        Component,
        amazonStorage
    ) {
        'use strict';

        return Component.extend(
            {
                defaults: {
                    template: 'Amazon_Payment/payment/amazonlogin'
                },
                getCode: function () {
                    return 'amazonlogin';
                },
                isActive: function () {
                    return true;
                },
                isPwaVisible: function () {
                    return amazonStorage.isPwaVisible && amazonStorage.isAmazonEnabled;
                }
            }
        );
    }
);
