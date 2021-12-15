/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Vault/js/view/payment/method-renderer/vault',
    'Amazon_Pay/js/action/place-order-vault'
], function (
        VaultComponent,
        placeOrderVaultAction
    ) {
    'use strict';

    return VaultComponent.extend({
        defaults: {
            template: 'Amazon_Pay/payment/amazon-payment-method-vault',
            logo: 'Amazon_Pay/images/logo/Black-L.png'
        },

        /**
         * @returns {String}
         */
        getToken: function () {
            return this.publicHash;
        },

        placeOrder: function (data, event) {
            var placeOrder;

            if (event) {
                event.preventDefault();
            }

            if (this.validate()) {
                placeOrder = placeOrderVaultAction(this.getData());
            }

            return false;
        },

         getLogoUrl: function() {
            return require.toUrl(this.logo);
        },


    });
});
