define(
    [
        'jquery',
        'Magento_Checkout/js/view/payment/default',
        'ko',
        'Amazon_Payment/js/model/storage',
        'mage/storage',
        'amazonPaymentConfig',
        'uiRegistry',
        'Amazon_Login/js/view/login-button'
    ],
    function (
        $,
        Component,
        ko,
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
