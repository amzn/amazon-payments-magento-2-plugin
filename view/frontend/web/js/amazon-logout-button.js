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
    'Amazon_Pay/js/amazon-checkout',
    'mage/cookies'
], function ($, amazonCheckout) {
    'use strict';

    $('.amazon-logout-button').click(function() {
        amazonCheckout.withAmazonCheckout(function (amazon, args) {
            amazon.Pay.signout();
            var redirectUrl = '/customer/account/login';
            var myParams = new URLSearchParams(window.location.search);
            if (myParams.has('amazonCheckoutSessionId')) {
                redirectUrl = '/checkout/';
            }
            window.location.assign(redirectUrl);
        });
    });
});
