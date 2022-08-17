define([
    'Magento_Vault/js/view/payment/method-renderer/vault',
], function (VaultComponent) {
    'use strict';

    return VaultComponent.extend({
        defaults: {
            template: 'Amazon_Pay/payment/vault',
            logo: 'Amazon_Pay/images/logo/Black-L.png'
        },

        getLogoUrl: function () {
            return require.toUrl(this.logo);
        },

        getPaymentDescriptor: function () {
            return this.details.paymentPreferences[0].paymentDescriptor;
        },

        getData: function () {
            var data = {
                method: this.getCode()
            };

            data['additional_data'] = {};
            data['additional_data']['public_hash'] = this.publicHash;

            return data;
        }
    });
});
