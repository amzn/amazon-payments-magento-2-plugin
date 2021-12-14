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
    'Magento_Checkout/js/model/quote',
    'mage/storage',
    'Magento_Checkout/js/model/url-builder',
    'Magento_Checkout/js/model/full-screen-loader',
    'Magento_Checkout/js/model/error-processor',
    'Amazon_Pay/js/model/storage'
], function (quote, storage, urlBuilder, fullScreenLoader, errorProcessor, amazonStorage) {
    'use strict';

    return function (callback) {
        var serviceUrl = urlBuilder.createUrl('/amazon-checkout-vault/charge',{});

        fullScreenLoader.startLoader();

        return storage.post(serviceUrl).done(function (response) {
            fullScreenLoader.stopLoader(true);
            if (response) {
                //alert('OK !');
                console.log(response);
                callback(response);
                fullScreenLoader.stopLoader(true);
            } else {
                console.log('Invalid Amazon RedirectUrl:');
                console.log(response);
                errorProcessor.process(response);
            }
        }).fail(function (response) {
            errorProcessor.process(response);
            fullScreenLoader.stopLoader(true);
        });
    };
});
