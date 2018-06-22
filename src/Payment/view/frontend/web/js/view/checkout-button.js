/*global define*/

define(
    [
        'jquery',
        'uiComponent',
        'ko',
        'Magento_Customer/js/model/customer',
        'Amazon_Payment/js/model/storage'
    ],
    function (
        $,
        Component,
        ko,
        customer,
        amazonStorage
    ) {
        'use strict';

        return Component.extend(
            {
                defaults: {
                    template: 'Amazon_Payment/checkout-button'
                },
                isCustomerLoggedIn: customer.isLoggedIn,
                isAmazonAccountLoggedIn: amazonStorage.isAmazonAccountLoggedIn,
                isPwaVisible: amazonStorage.isPwaVisible,

                /**
                 * Init
                 */
                initialize: function () {
                    this._super();
                }
            }
        );
    }
);

