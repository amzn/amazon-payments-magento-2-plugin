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
    'sjcl',
    'jquery',
    'mage/cookies'
], function (sjcl, $) {
    'use strict';

    return {
        options: {
            wordsLength: 8,
            cookieName: 'amazon-csrf-state'
        },

        /**
         * Create random string for Amazon CSRF cookie
         */
        generateNewValue: function () {
            var randomString = sjcl.codec.base64.fromBits(sjcl.random.randomWords(this.options.wordsLength));

            $.mage.cookies.set(this.options.cookieName, randomString);

            return randomString;
        },

        /**
         * Check if Amazon CSRF cookie is valid and clear cookie
         * @param {String} stateString
         * @returns {Boolean}
         */
        isValid: function (stateString) {
            var isValid = $.mage.cookies.get(this.options.cookieName) === stateString;

            this.clear(); // always clear nonce when validating

            return isValid;
        },

        /**
         * Clear Amazon CSRF cookie
         */
        clear: function () {
            $.mage.cookies.clear(this.options.cookieName);
        }
    };
});
