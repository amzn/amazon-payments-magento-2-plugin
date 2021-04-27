define([
    'Magento_Customer/js/model/address-list',
    'Amazon_Pay/js/model/storage'
], function (addressList, amazonStorage) {
    'use strict';

    return function(Component) {
        if (!amazonStorage.isEnabled || !amazonStorage.isAmazonCheckout()) {
            return Component;
        }

        return Component.extend({
            /**
             * Init address list
             */
            initObservable: function () {
                this._super();
                this.visible = true;
                return this;
            },

            createRendererComponent: function (address, index) {
                if (address.getType() === 'new-customer-address') {
                    // Only display one address from Amazon
                    return this._super();
                }
            }
        });
    }
});
