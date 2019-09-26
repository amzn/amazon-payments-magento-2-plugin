define([
    'Magento_Checkout/js/view/shipping-address/list',
    'Magento_Customer/js/model/address-list',
    'Amazon_Payment/js/model/storage',
    'ko'
], function (Component, addressList, amazonStorage, ko) {
    'use strict';

    return Component.extend({
        /**
         * Init address list
         */
        initObservable: function () {
            this._super();
            this.visible = ko.computed(function () {
                return addressList().length > 0 && !amazonStorage.isAmazonAccountLoggedIn();
            });

            return this;
        }
    });
});
