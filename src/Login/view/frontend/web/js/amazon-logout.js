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

define([
    'jquery',
    'amazonCore',
    'jquery/ui',
    'mage/cookies'
], function ($, core) {
    'use strict';

    $.widget('amazon.AmazonLogout', {
        options: {
            onInit: false
        },

        /**
         * Create Amazon Logout Widget
         * @private
         */
        _create: function () {
            if (this.options.onInit) {
                core.AmazonLogout(); //logout amazon user on init
                $.mage.cookies.clear('amazon_Login_accessToken');
            }
        },

        /**
         * Logs out a user if called directly
         * @private
         */
        _logoutAmazonUser: function () {
            core.AmazonLogout();
            $.mage.cookies.clear('amazon_Login_accessToken');
        }
    });

    return $.amazon.AmazonLogout;
});
