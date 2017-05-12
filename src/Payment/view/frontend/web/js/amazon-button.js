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
    'Magento_Customer/js/customer-data',
    'Magento_Customer/js/section-config',
    'Amazon_Payment/js/model/amazonPaymentConfig',
    'amazonCsrf',
    'modernizr/modernizr',
    'amazonCore',
    'jquery/ui'
], function ($, customerData, sectionConfig, amazonPaymentConfig, amazonCsrf) {
    "use strict";

    var _this,
        $button;

    $.widget('amazon.AmazonButton', {
        options: {
            merchantId: null,
            buttonType: 'LwA',
            buttonColor: 'Gold',
            buttonSize: 'medium',
            redirectUrl: null,
            loginPostUrl: null
        },

        _create: function () {
            _this = this;
            $button = this.element;
            this._verifyAmazonConfig();
            _this._renderAmazonButton();
        },
        /**
         * Verify if checkout config is available
         * @private
         */
        _verifyAmazonConfig: function () {
            if (amazonPaymentConfig.isDefined()) {
                _this.options.merchantId = amazonPaymentConfig.getValue('merchantId');
                _this.options.buttonType = (_this.options.buttonType == 'LwA') ? amazonPaymentConfig.getValue('buttonTypeLwa') : amazonPaymentConfig.getValue('buttonTypePwa');
                _this.options.buttonColor = amazonPaymentConfig.getValue('buttonColor');
                _this.options.buttonSize = amazonPaymentConfig.getValue('buttonSize');
                _this.options.redirectUrl = amazonPaymentConfig.getValue('redirectUrl');
                _this.options.loginPostUrl = amazonPaymentConfig.getValue('loginPostUrl');
                _this.options.loginScope = amazonPaymentConfig.getValue('loginScope');
                _this.options.buttonLanguage = amazonPaymentConfig.getValue('displayLanguage');
            }
        },
        secureHttpsCallback: function (event) {
            if (!event.state || !amazonCsrf.isValid(event.state)) {
                return window.location = amazonPaymentConfig.getValue('customerLoginPageUrl');
            }

            if (!event.access_token || !!event.error) {
                return window.location = amazonPaymentConfig.getValue('customerLoginPageUrl');
            }

            var sections = sectionConfig.getAffectedSections(_this.options.loginPostUrl);
            if (sections) {
                customerData.invalidate(sections);
            }
            window.location = _this.options.redirectUrl + '?access_token=' + event.access_token;
        },
        _popupCallback: function () {
            return _this.usePopUp() ? _this.secureHttpsCallback : amazonPaymentConfig.getValue('oAuthHashRedirectUrl');
        },
        /**
         * Are touch events available
         * (Supports both v2 and v3 Modernizr)
         * @returns {Boolean}
         * @private
         */
        _touchSupported: function () {
            return Modernizr.touch !== undefined ? Modernizr.touch : Modernizr.touchevents;
        },
        usePopUp: function () {
            //always use redirect journey on product page and touch devices
            return ((window.location.protocol === 'https:' && !$('body').hasClass('catalog-product-view')) && !_this._touchSupported());
        },
        /**
         * onAmazonPaymentsReady
         * @private
         */
        _renderAmazonButton: function () {
            var authRequest;

            OffAmazonPayments.Button($button.attr('id'), _this.options.merchantId, {
                type: _this.options.buttonType,
                color: _this.options.buttonColor,
                size: _this.options.buttonSize,
                language: _this.options.buttonLanguage,

                authorization: function () {
                    authRequest = amazon.Login.authorize(_this._getLoginOptions(), _this._popupCallback());
                }
            });
        },
        /**
         * Build login options
         * @returns {{scope: *, popup: *, state: *}}
         * @private
         */
        _getLoginOptions: function () {
            return {
                scope: _this.options.loginScope,
                popup: _this.usePopUp(),
                state: amazonCsrf.generateNewValue()
            };
        }
    });

    return $.amazon.AmazonButton;
});
