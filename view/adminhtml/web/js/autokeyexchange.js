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
/*browser:true*/
/*global define*/
define(
    [
        'jquery',
        'uiComponent',
        'mage/translate',
        'jquery/ui',
        'jquery/validate'
    ],
    function ($, Component, $t) {
        'use strict';
        var pollTimer, windowOpen = false;

        return Component.extend({

                defaults: {
                    $amazonFields: null,
                    $amazonCredentialJson: null,
                    $amazonAutoKeyExchangeBack: null,
                    $amazonMerchantId: null,
                    selector: 'amazon_pay',
                    $container: null,
                    pollInterval: 1500,
                    $form: null,
                    apAutoKeyExchangeSelector: '#amazon_autokeyexchange',
                    apAutoKeyExchangeBackSelector: '#amazon_autokeyexchange_back',
                },

                /**
                 * Set list of observable attributes
                 * @returns {exports.initObservable}
                 */
                initObservable: function () {
                    var self = this;

                    self.$amazonAutoKeyExchange = $(self.apAutoKeyExchangeSelector);
                    self.$amazonFields = $('#payment_' + self.getCountry() + '_' + self.selector + ' .form-list');
                    self.$amazonCredentialsHeader = $('#payment_' + self.getCountry() + '_' + self.selector
                        + '_credentials-head');
                    self.$amazonMerchantId = $('#payment_' + self.getCountry() + '_' + self.selector
                        + '_credentials_merchant_id_v2');
                    self.$amazonAutoKeyExchangeBack = $(self.apAutoKeyExchangeBackSelector);
                    self.$container = $(self.apAutoKeyExchangeSelector);

                    if (this.isMultiCurrencyRegion) {
                        $('#row_payment_' + self.getCountry() + '_amazon_pay_options_multicurrency').show();
                        $('#row_payment_other_amazon_pay_options_multicurrency').show();
                    }
                    else {
                        $('#row_payment_' + self.getCountry() + '_amazon_pay_options_multicurrency').hide();
                        $('#row_payment_other_amazon_pay_options_multicurrency').hide();
                    }

                    if (self.$amazonMerchantId.val()) {
                        this.$amazonAutoKeyExchange.hide();
                        this.$amazonAutoKeyExchangeBack.show();
                    }
                    else {
                        self.showAmazonConfig();
                    }

                    if (!self.$form) {
                        self.generateAutoKeyExchangeForm();
                    }

                    self._super();

                    self.initEventHandlers();

                    return self;
                },

                /**
                 * Init event handlers
                 */
                initEventHandlers: function () {
                    var self = this;

                    self.$amazonAutoKeyExchangeBack.click(function () {
                        self.showAmazonConfig();
                        return false;
                    });

                    $('#autokeyexchange-skip').click(function () {
                        self.hideAmazonConfig();
                        return false;
                    });

                    $('#autokeyexchange_form').on('submit', function () {
                        // Remove the numeric indices added by Magento's form validation logic
                        $('#autokeyexchange_form :input').each(function() {
                            if($(this).attr('orig-name')) {
                                $(this).attr('name', $(this).attr('orig-name'));
                                $(this).removeAttr('orig-name');
                            }
                        });
                        self.setupWindowLaunch();
                    });

                    $('#reset-auto-key-exchange-button').click(function() {
                        $('#reset-auto-key-exchange-message').show();
                        $.ajax({
                            url: self.resetAKEUrl,
                            data: {},
                            type: 'GET',
                            cache: true,
                            dataType: 'json',
                            context: this,

                            /**
                             * Response handler
                             * @param {Object} response
                             */
                            success: function (response) {
                                document.location.replace(document.location + '#payment_amazon_payments-head');
                                location.reload();
                            }
                        });
                    });
                },

                /**
                 * Sets up Amazon merchant key popup and polls for data update upon user completion.
                 */
                setupWindowLaunch: function () {
                    var self = this,
                        heights = [660, 720, 810, 900],
                        popupWidth = this.getCountry() !== 'us' ? 768 : 1050, popupHeight = heights[0];

                    for (var i in heights) {
                        if (heights.hasOwnProperty(i)) {
                            popupHeight = window.innerHeight >= heights[i] ? heights[i] : popupHeight;
                        }
                    }

                    self.launchPopup(self.amazonUrl, popupWidth, popupHeight);

                    // flags that popup is open and poll timer can proceed
                    windowOpen = true;

                    // begin polling for feedback
                    pollTimer = setTimeout(self.pollForKeys(self), self.pollInterval);
                },

                /**
                 * Perform Ajax request looking for new keys.
                 */
                pollForKeys: function (self) {
                    clearTimeout(pollTimer);
                    if (windowOpen) {
                        $.ajax({
                            url: self.pollUrl,
                            data: {},
                            type: 'GET',
                            cache: true,
                            dataType: 'json',
                            context: this,

                            /**
                             * Response handler
                             * @param {Object} response
                             */
                            success: function (response) {
                                // poll controller returns a 0 if invalid and a 1 if valid
                                if (response) {
                                    $('#amazon_reload').show();
                                    document.location.replace(document.location + '#payment_amazon_payments-head');
                                    location.reload();
                                }
                                else {
                                    pollTimer = setTimeout(self.pollForKeys(self), self.pollInterval);
                                }
                            }
                        });
                    }
                },

                /**
                 * Sets up dynamic form for capturing popup/form input for simple path setup.
                 */
                generateAutoKeyExchangeForm: function () {

                    this.$form = new Element('form', {
                        method: 'post',
                        action: this.amazonUrl,
                        id: 'autokeyexchange_form',
                        target: 'autokeyexchange',
                        novalidate: 'novalidate',
                    });

                    this.$container.wrap(this.$form);

                    // Convert formParams JSON to hidden inputs
                    for (var key in this.formParams) {
                        if ( $.isPlainObject(this.formParams[key]) || $.isArray(this.formParams[key])) {
                            for (var i in this.formParams[key]) {
                                if (typeof this.formParams[key][i] !== 'function') {
                                    $(new Element('input', {
                                        type: 'hidden',
                                        name: key,
                                        value: this.formParams[key][i],
                                        novalidate: 'novalidate'
                                    })).appendTo($("#autokeyexchange_form"));
                                }
                            }
                        } else {
                            $(new Element('input', {
                                type: 'hidden',
                                name: key,
                                novalidate: 'novalidate',
                                value: this.formParams[key]
                            })).appendTo($("#autokeyexchange_form"));
                        }
                    }

                    // unable to use this.form, had to resort to direct call
                    $('#autokeyexchange_form').validate({});
                },

                /**
                 * display amazon simple path config section
                 */
                showAmazonConfig: function () {
                    this.$amazonAutoKeyExchange.show();
                    this.$amazonAutoKeyExchangeBack.hide();
                    if (this.$amazonCredentialsHeader.hasClass('open')) {
                        this.$amazonCredentialsHeader.click();
                    }
                },

                /**
                 * hide amazon simple path config.
                 */
                hideAmazonConfig: function () {
                    this.$amazonAutoKeyExchange.hide();
                    this.$amazonAutoKeyExchangeBack.show();
                    if (!this.$amazonCredentialsHeader.hasClass('open')) {
                        this.$amazonCredentialsHeader.click();
                    }
                },

                /**
                 * Get payment code
                 * @returns {String}
                 */
                getCountry: function () {
                    return this.co.toLowerCase();
                },

                /**
                 * Generate popup window for simple path process
                 * @param url
                 * @param requestedWidth
                 * @param requestedHeight
                 */
                launchPopup: function (url, requestedWidth, requestedHeight) {
                    var leftOffset = this.getLeftOffset(requestedWidth),
                        topOffset = this.getTopOffset(requestedHeight),
                        newWindow = window.open(url, 'autokeyexchange', 'scrollbars=yes, width=' + requestedWidth
                            + ', height=' + requestedHeight + ', top=' + topOffset + ', left=' + leftOffset);

                    if (window.focus) {
                        newWindow.focus();
                    }

                    // Set interval to check when this popup window is closed so timeout can be suspended.
                    var winTimer = window.setInterval(function () {
                        if (newWindow.closed !== false) {
                            window.clearInterval(winTimer);
                            windowOpen = false;
                        }
                    });
                },

                /**
                 * Determine left offset for popup window
                 * @param requestedWidth
                 * @returns {number}
                 */
                getLeftOffset: function (requestedWidth) {
                    var dualScreenLeft = window.screenLeft !== undefined ? window.screenLeft : screen.left;

                    return (this.windowWidth() / 2) - (requestedWidth / 2) + dualScreenLeft;
                },

                /**
                 * Determine top offset for popup window
                 * @param requestedHeight
                 * @returns {number}
                 */
                getTopOffset: function (requestedHeight) {
                    var dualScreenTop = window.screenTop !== undefined ? window.screenTop : screen.top;

                    return (this.windowHeight() / 2) - (requestedHeight / 2) + dualScreenTop;
                },

                /**
                 * Determine width of popup window
                 * @returns {number}
                 */
                windowWidth: function () {
                    return window.innerWidth
                        || document.documentElement.clientWidth
                        || screen.width;
                },

                /**
                 * Determine window height of popup
                 * @returns {number}
                 */
                windowHeight: function () {
                    return window.innerHeight
                        || document.documentElement.clientHeight
                        || screen.height;
                }
            }
        );
    }
);
