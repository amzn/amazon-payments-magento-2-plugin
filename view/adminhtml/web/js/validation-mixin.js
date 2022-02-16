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
define(['jquery'], function ($) {
    'use strict';

    return function () {
        $.validator.addMethod(
            'validate-secure-url',
            function (v) {
                if ($.mage.isEmptyNoTrim(v)) {
                    return true;
                }
                v = (v || '').replace(/^\s+/, '').replace(/\s+$/, '');

                return (/^https:\/\/(([A-Z0-9]([A-Z0-9_-]*[A-Z0-9]|))(\.[A-Z0-9]([A-Z0-9_-]*[A-Z0-9]|))*)(:(\d+))?(\/[A-Z0-9~](([A-Z0-9_~-]|\.)*[A-Z0-9~]|))*\/?(.*)?$/i).test(v); //eslint-disable-line max-len

            },
            $.mage.__('Please enter a valid URL. Secure protocol is required (https://).')
        ),
        $.validator.addMethod(
            'validate-private-key',
            function (v) {
                if (v == '******' || v === '------' || v == '') {
                    return true;
                }
                return (/^-----BEGIN (RSA )?PRIVATE KEY-----.*-----END (RSA )?PRIVATE KEY-----$/s).test(v);
            },
            $.mage.__('Private Key field is invalid. It must include header ' +
                'and footer of the private key. Please check and try again')
        ),

        $.validator.addMethod(
            'validate-amzn-merchant-id',
            function (v) {
                return (/^[0-9A-Z]{13}[0-9A-Z]?$/).test(v);
            },
            $.mage.__('Merchant Id field is invalid. It must contain 13 or 14 characters. Please check and try again')
        ),
        $.validator.addMethod(
            'validate-amzn-public-key-id',
            function (v) {
                return (/^((SANDBOX-)?|(LIVE-)?)[0-9A-Z]{24}$/).test(v);
            },
            $.mage.__('Public Key ID field is invalid. It must contain 24 characters, possibly prefixed with ' +
                '\'SANDBOX-\' or \'LIVE-\'. Please check and try again')
        ),
        $.validator.addMethod(
            'validate-amzn-store-id',
            function (v) {
                return (/^amzn1\.application-oa2-client\.[0-9a-z]{32}$/).test(v);
            },
            $.mage.__('Store Id field is invalid. It must start with “amzn1.application-oa2-client.” ' +
                'and contain 61 characters. Please check and try again')
        ),
        $.validator.addMethod(
            'validate-amzn-display-language',
            function (v) {
                // allow empty field, as it will default to locale
                if (v === '') {
                    return true;
                }
                // match specific codes
                return (/^(en_GB|de_DE|fr_FR|it_IT|es_ES)$/).test(v);
            },
            $.mage.__('Entered language is not a supported value for “Button Display Language” in Amazon Pay configuration')
        )
    }
});
