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
    'amazonPaymentConfig',
    'amazonCsrf',
    'mage/loader',
    'jquery/ui',
    'mage/cookies'
], function ($, amazonCore, amazonPaymentConfig, amazonCsrf) {
    'use strict';

    var self;

    $.widget('amazon.AmazonRedirect', {

        /**
         * @private
         */
        _create: function () {

            self = this;
            // start the loading animation. WIll en on redirect, no explicit stop here
            $('body').trigger('processStart');

            //verify nonce first
            this.redirectOnInvalidState();

            // we don't have the customer's consent or invalid request
            this.redirectOnRequestWithError();
            this.setAuthStateCookies();
            amazonCore.amazonDefined.subscribe(function () {
                //only set this on the redirect page
                amazon.Login.setUseCookie(true); //eslint-disable-line no-undef
                amazonCore.verifyAmazonLoggedIn().then(function (loggedIn) {
                    if (loggedIn) {
                        self.redirect();
                    }
                }, 0);
            }, this);
        },

        /**
         * getURLParamater from URL for access OAuth Token
         * @param {String} name
         * @param {String} source
         * @returns {String|Null}
         */
        getURLParameter: function (name, source) {
            return decodeURIComponent((new RegExp('[?|&|#]' + name + '=' +
                    '([^&]+?)(&|#|;|$)').exec(source) || [,''])[1].replace(
                        /\+/g,
                        '%20'
                    )) || null;
        },

        /**
         * Set State Cache Auth Cookies if they aren't already set
         * @returns {Boolean}
         */
        setAuthStateCookies: function () {
            var token = this.getURLParameter('access_token', location.hash);

            if (typeof token === 'string' && token.match(/^Atza/)) {
                $.mage.cookies.set('amazon_Login_accessToken', token);
            }

            return true;
        },

        /**
         * Redirect user to correct controller which logs them into M2 via Amazon hash
         */
        redirect: function () {
            window.location = amazonPaymentConfig.getValue('redirectUrl') + '?access_token=' +
                this.getURLParameter('access_token', location.hash);
        },

        /**
         * Redirect user on invalid state
         */
        redirectOnInvalidState: function () {
            var state = this.getURLParameter('state', location.hash);

            if (!state || !amazonCsrf.isValid(state)) {
                window.location = amazonPaymentConfig.getValue('customerLoginPageUrl');
            }
        },

        /**
         * Redirect user on request error
         */
        redirectOnRequestWithError: function () {
            if (this.getURLParameter('error', window.location)) {
                window.location = amazonPaymentConfig.getValue('customerLoginPageUrl');
            }
        }
    });

    return $.amazon.AmazonRedirect;
});
