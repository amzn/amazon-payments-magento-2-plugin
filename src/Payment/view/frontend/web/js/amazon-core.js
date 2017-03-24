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
    'amazonPaymentConfig',
    'amazonWidgetsLoader',
    'bluebird',
    'jquery/jquery-storageapi'
], function ($, ko, amazonPaymentConfig) {
    "use strict";

    var clientId = amazonPaymentConfig.getValue('clientId'),
        amazonDefined = ko.observable(false),
        amazonLoginError = ko.observable(false),
        accessToken = ko.observable(null);


    if (typeof amazon === 'undefined') {
        window.onAmazonLoginReady = function () {
            setClientId(clientId);
            doLogoutOnFlagCookie();
        }
    } else {
        setClientId(clientId);
        doLogoutOnFlagCookie();
    }

    /**
     * Set Client ID
     * @param cid
     */
    function setClientId(cid)
    {
        amazon.Login.setClientId(cid);
        amazonDefined(true);
    }

    /**
     * Log user out of amazon
     */
    function amazonLogout()
    {
        if (amazonDefined()) {
            amazon.Login.logout();
        } else {
            var logout = amazonDefined.subscribe(function (defined) {
                if (defined) {
                    amazon.Login.logout();
                    logout.dispose(); //remove subscribe
                }
            });
        }
    }

    //Check if login error / logout cookies are present
    function doLogoutOnFlagCookie()
    {
        var errorFlagCookie = 'amz_auth_err',
            amazonLogoutCookie = 'amz_auth_logout';

        $.cookieStorage.isSet(errorFlagCookie) ? amazonLogoutThrowError(errorFlagCookie) : false;
        $.cookieStorage.isSet(amazonLogoutCookie) ? amazonLogoutThrowError(amazonLogoutCookie) : false;
    }

    //handle deletion of cookie and log user out if present
    function amazonLogoutThrowError(cookieToRemove)
    {
        amazonLogout();
        document.cookie = cookieToRemove + '=; Path=/; expires=Thu, 01 Jan 1970 00:00:01 GMT;';
        amazonLoginError(true);
    }

    return {
        /**
         * Verify a user is logged into amazon
         */
        verifyAmazonLoggedIn: function () {
            var defer  = $.Deferred();
            
            var loginOptions = {
                scope: amazonPaymentConfig.getValue('loginScope'),
                popup: true,
                interactive: 'never'
            };
            
            amazon.Login.authorize(loginOptions, function (response) {
                if (response.error) {
                    defer.reject(response.error);
                } else {
                    accessToken(response.access_token);
                    defer.resolve(!response.error);
                }
            });
            
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
