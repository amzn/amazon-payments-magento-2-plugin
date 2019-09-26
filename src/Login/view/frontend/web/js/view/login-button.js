/**
 * Copyright 2016 Amazon.com, Inc. or its affiliates. All Rights Reserved.
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

define(
    [
        'jquery',
        'uiComponent',
        'ko',
        'Magento_Customer/js/model/customer',
        'Amazon_Payment/js/model/storage',
        'amazonPaymentConfig'
    ],
    function (
        $,
        Component,
        ko,
        customer,
        amazonStorage,
        amazonPaymentConfig
    ) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Amazon_Login/login-button'
            },
            isCustomerLoggedIn: customer.isLoggedIn,
            isAmazonAccountLoggedIn: amazonStorage.isAmazonAccountLoggedIn,
            isLwaVisible: ko.observable(amazonPaymentConfig.getValue('isLwaEnabled')),

            /**
             * Initialize login button
             */
            initialize: function () {
                this._super();
            }
        });
    }
);
