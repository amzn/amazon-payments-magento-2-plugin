/**
 * Copyright © Amazon.com, Inc. or its affiliates. All Rights Reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the "License").
 * You may not use this file except in compliance with the License.
 * A copy of the License is located at
 *
 *  http://aws.amazon.com/apache2.0
 *
 * or in the "license" file accompanying this file. This file is distributed
 * on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either
 * express or implied. See the License for the specific language governing
 * permissions and limitations under the License.
 */
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
