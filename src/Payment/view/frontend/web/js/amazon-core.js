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
    'Magento_Ui/js/model/messageList',
    'amazonWidgetsLoader',
    'jquery/jquery-storageapi',
    'mage/cookies'
], function ($, ko, url, amazonPaymentConfig, messageList) {
    'use strict';

    var amazonDefined = ko.observable(false),
        amazonLoginError = ko.observable(false),
        accessToken = ko.observable(null),
        // Match region config to amazon.Login.Region
        regions = {'us': 'NA', 'de': 'EU', 'uk': 'EU', 'jp': 'APAC'},
        sandboxMode,
        region;

    accessToken($.mage.cookies.get('amazon_Login_accessToken'));

    var initAmazonLogin = function () {
        amazon.Login.setClientId(amazonPaymentConfig.getValue('clientId')); //eslint-disable-line no-undef
        amazon.Login.setSandboxMode(amazonPaymentConfig.getValue('isSandboxEnabled', false)); //eslint-disable-line no-undef
        amazon.Login.setRegion(regions[amazonPaymentConfig.getValue('region')]); //eslint-disable-line no-undef
        amazon.Login.setUseCookie(true); //eslint-disable-line no-undef

        doLogoutOnFlagCookie(); //eslint-disable-line no-use-before-define
        amazonDefined(true);
    };

    if (typeof amazon === 'undefined') {
        window.onAmazonLoginReady = initAmazonLogin;
    } else {
        initAmazonLogin();
    }

    // Widgets.js ready callback
    window.onAmazonPaymentsReady = function() {
        $(window).trigger('OffAmazonPayments');
    };

    /**
     * Log user out of amazon
     */
    function amazonLogout() {
        $.mage.cookies.clear('amazon_Login_accessToken');
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

    function handleWidgetError(error) {
        console.log('OffAmazonPayments.Widgets', error.getErrorCode(), error.getErrorMessage());
        switch (error.getErrorCode()) {
            case 'BuyerSessionExpired':
                messageList.addErrorMessage({message: $.mage.__('Your Amazon session has expired.  Please sign in again by clicking the Amazon Pay Button.')});
                var storage = require('Amazon_Payment/js/model/storage'); //TODO: clean up this circular dependency
                storage.amazonlogOut();
                break;
            case 'ITP':
                // ITP errors are how handled within the widget code
                break;
            default:
                messageList.addErrorMessage({message: $.mage.__(error.getErrorMessage())});
        }
    }

    return {
        /**
         * Log user out of Amazon
         */
        AmazonLogout: amazonLogout,
        amazonDefined: amazonDefined,
        accessToken: accessToken,
        amazonLoginError: amazonLoginError,
        handleWidgetError: handleWidgetError
    };

});
