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
    'jquery',
    'uiComponent'
], function ($, GrandClass) {
    'use strict';
    return function (Class) {
        return Class.extend({
            initObservable: function () {
                var country = this.getCountry();
                var $apiSelector = $('#payment_' + country + '_amazon_payment_api_version');
                if (!$apiSelector.length) {
                    country = 'other';
                    $apiSelector = $('#payment_' + country + '_amazon_payment_api_version');
                }
                var apiVersion = $apiSelector.val();
                if (apiVersion > 1) {
                    $('#row_payment_' + country + '_amazon_payment_advanced_sales_options_multicurrency').hide();

                    GrandClass.prototype.initObservable.apply(this, arguments);
                } else {
                    this._super();
                }
                return this;
            }
        });
    }
});
