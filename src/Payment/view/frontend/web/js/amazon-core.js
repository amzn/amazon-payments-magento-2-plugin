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
    'ko',
    'mage/url',
    'amazonPaymentConfig',
    'amazonWidgetsLoader',
    'bluebird',
    'jquery/jquery-storageapi'
], function ($, ko, url, amazonPaymentConfig) {
    'use strict';

    var clientId = amazonPaymentConfig.getValue('clientId'),
        amazonDefined = ko.observable(false),
        amazonLoginError = ko.observable(false),
        accessToken = ko.observable(null),
        // Match region config to amazon.Login.Region
        regions = {'us': 'NA', 'de': 'EU', 'uk': 'EU', 'jp': 'APAC'},
        sandboxMode,
        region;

    if (typeof amazon === 'undefined') {
        /**
         * Amazon login ready callback
         */
        window.onAmazonLoginReady = function () {
            setClientId(clientId);  //eslint-disable-line no-use-before-define
            doLogoutOnFlagCookie(); //eslint-disable-line no-use-before-define

            sandboxMode = amazonPaymentConfig.getValue('isSandboxEnabled', false);
            amazon.Login.setSandboxMode(sandboxMode); //eslint-disable-line no-undef

            region = regions[amazonPaymentConfig.getValue('region')];
            amazon.Login.setRegion(region); //eslint-disable-line no-undef
        };
    } else {
        setClientId(clientId);  //eslint-disable-line no-use-before-define
        doLogoutOnFlagCookie(); //eslint-disable-line no-use-before-define
    }

    // Widgets.js ready callback
    window.onAmazonPaymentsReady = function() {
        $(window).trigger('OffAmazonPayments');
    };

    /**
     * Set Client ID
     * @param {String} cid
     */
    function setClientId(cid) {
        amazon.Login.setClientId(cid); //eslint-disable-line no-undef
        amazonDefined(true);
    }

    /**
     * Log user out of amazon
     */
    function amazonLogout() {
        $.ajax({
            url: url.build('amazon/logout'),
            context: this
        }).always(function () {
            if (amazonDefined()) {
                amazon.Login.logout(); //eslint-disable-line no-undef
            } else {
                var logout = amazonDefined.subscribe(function (defined) { //eslint-disable-line vars-on-top
                    if (defined) {
                        amazon.Login.logout(); // eslint-disable-line no-undef
                        logout.dispose(); //remove subscribe
                    }
                });
            }
        });
    }

    /**
     * Check if login error / logout cookies are present
     */
    function doLogoutOnFlagCookie() {
        var errorFlagCookie = 'amz_auth_err',
            amazonLogoutCookie = 'amz_auth_logout';

        //eslint-disable-next-line no-use-before-define
        $.cookieStorage.isSet(errorFlagCookie) ? amazonLogoutThrowError(errorFlagCookie) : false;
        //eslint-disable-next-line no-use-before-define
        $.cookieStorage.isSet(amazonLogoutCookie) ? amazonLogoutThrowError(amazonLogoutCookie) : false;
    }

    /**
     * Handle deletion of cookie and log user out if present
     */
    function amazonLogoutThrowError(cookieToRemove) {
        amazonLogout();
        document.cookie = cookieToRemove + '=; Path=/; expires=Thu, 01 Jan 1970 00:00:01 GMT;';
        amazonLoginError(true);
    }

    return {
        /**
         * Verify a user is logged into amazon
         */
        verifyAmazonLoggedIn: function () {
            var defer  = $.Deferred(),
                loginOptions = {
                    scope: amazonPaymentConfig.getValue('loginScope'),
                    popup: true,
                    interactive: 'never'
                };

            // jscs:disable requireCamelCaseOrUpperCaseIdentifiers
            amazon.Login.authorize(loginOptions, function (response) { //eslint-disable-line no-undef
                if (response.error) {
                    defer.reject(response.error);
                } else {
                    accessToken(response.access_token);
                    defer.resolve(!response.error);
                }
            });
            // jscs:enable requireCamelCaseOrUpperCaseIdentifiers

            return defer.promise();
        },

        /**
         * Log user out of Amazon
         */
        AmazonLogout: amazonLogout,
        amazonDefined: amazonDefined,
        accessToken: accessToken,
        amazonLoginError: amazonLoginError
    };

});
