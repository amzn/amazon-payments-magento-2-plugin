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
    function ($, Class, $t) {
        'use strict';
        var pollTimer, windowOpen = false;

        return Class.extend({

                defaults: {
                    $amazonFields: null,
                    $amazonCredentialJson: null,
                    $amazonSpBack: null,
                    $amazonMerchantId: null,
                    selector: 'amazon_payment',
                    $container: null,
                    pollInterval: 1500,
                    $form: null,
                },

                /**
                 * Set list of observable attributes
                 * @returns {exports.initObservable}
                 */
                initObservable: function () {
                    var self = this;
                    self.$amazonSimplepath = $('#amazon_simplepath');
                    self.$amazonFields = $('#payment_' + self.getCountry() + '_' + self.selector + ' .form-list');
                    self.$amazonCredentialsHeader = $('#payment_' + self.getCountry() + '_' + self.selector
                        + '_credentials-head');
                    self.$amazonCredentialJson = $('#payment_' + self.getCountry() + '_' + self.selector
                        + '_credentials_credentials_json');
                    self.$amazonMerchantId = $('#payment_' + self.getCountry() + '_' + self.selector
                        + '_credentials_merchant_id').val();
                    self.$amazonSpBack = $('#amazon_simplepath_back');
                    self.$container = $('#amazon_simplepath');

                    if (self.$amazonMerchantId) {
                        self.hideAmazonConfig();
                    }
                    else {
                        self.showAmazonConfig();
                    }

                    if (!self.$form) {
                        self.generateSimplePathForm();
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
                    self.$amazonSpBack.click(function () {
                        self.showAmazonConfig();
                        return false;
                    });

                    $('#simplepath-skip').click(function () {
                        self.hideAmazonConfig();
                        return false;
                    });

                    $('#simplepath_form').on('submit', function () {
                        self.setupWindowLaunch();
                    });

                    self.$amazonCredentialJson.on('input', function (e) {
                        self.updateCredentials(self);
                    });
                },

                /**
                 * Detects when a properly formatted JSON block is pasted into the Credentials JSON field and auto populates
                 * specified fields.
                 *
                 * @param self
                 */
                updateCredentials: function (self) {
                    var elJson = self.$amazonCredentialJson.val();
                    var obj = null;
                    try {
                        obj = $.parseJSON($.trim(elJson));
                    }
                    catch (err) {
                        obj = null;
                        self.$amazonCredentialJson.val('').attr(
                            'placeholder',
                            $t('Invalid JSON credentials entered, please try again.')
                        ).focus();
                    }

                    if (obj && typeof obj === 'object') {
                        var success = true;
                        for (var prop in obj) {
                            var item = $('#payment_' + self.getCountry() + '_amazon_payment_credentials_' + $.trim(prop));
                            if (item && item.length) {
                                $('#payment_' + self.getCountry() + '_amazon_payment_credentials_'
                                    + $.trim(prop)).val($.trim(obj[prop]));
                            }
                            else {
                                success = false;
                            }
                        }

                        if (success) {
                            self.$amazonCredentialJson.val('').attr(
                                'placeholder',
                                $t('Credential fields successfully updated and being saved.')
                            ).focus();
                            $('#save').click();
                        }
                        else {
                            self.$amazonCredentialJson.val('').attr(
                                'placeholder',
                                $t('One or more of your credential fields did not parse correctly. Please review your entry and try again.')
                            ).focus();
                        }
                    }
                },

                /**
                 * Sets up Amazon merchant key popup and polls for data update upon user completion.
                 */
                setupWindowLaunch: function () {
                    var self = this;
                    var heights = [660, 720, 810, 900];
                    var popupWidth = this.getCountry() !== 'us' ? 768 : 1050;
                    var popupHeight = heights[0];
                    for (var i in heights) {
                        popupHeight = (window.innerHeight >= heights[i]) ? heights[i] : popupHeight;
                    }

                    self.launchPopup(self.amazonUrl, popupWidth, popupHeight);

                    // flags that popup is open and poll timer can proceed
                    windowOpen = true;

                    // begin polling for feedback
                    pollTimer = setTimeout(self.pollForKeys(self), self.pollInterval);

                    // Save JSON
                    $('#save-json').click(function (e) {
                        e.stop();
                        var json = $('#json-import').value;
                        if (!json || !json.isJSON()) {
                            return;
                        }
                        elJson.value = json;
                        $('#save').click();
                    });

                    // Autoset payment region (for EU/UK)
                    var region = self.region;
                    if (self.region.indexOf('eu') != -1) {
                        region = 'de';
                    }
                    var elCheckDefault = $('#payment_' + self.getCountry()
                        + '_amazon_payment_credentials_payment_region_inherit:checked');
                    if (elCheckDefault && elCheckDefault.length) {
                        elCheckDefault[0].click();
                    }
                    var elRegion = $('payment_' + self.getCountry() + '_amazon_payment_credentials_payment_region');
                    if (elRegion) {
                        elRegion.value = region;
                    }
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
                generateSimplePathForm: function () {
                    this.$form = new Element('form', {
                        method: 'post',
                        action: this.amazonUrl,
                        id: 'simplepath_form',
                        target: 'simplepath',
                        novalidate: 'novalidate',
                    });

                    this.$container.wrap(this.$form);

                    // Convert formParams JSON to hidden inputs
                    for (var key in this.formParams) {
                        if (typeof this.formParams[key] === 'object' || typeof this.formParams[key] === 'array') {
                            for (var i in this.formParams[key]) {
                                if (typeof this.formParams[key][i] !== 'function') {
                                    var element = new Element('input', {
                                        type: 'hidden',
                                        name: key,
                                        value: this.formParams[key][i],
                                        novalidate: 'novalidate'
                                    });
                                    $(element).appendTo($("#simplepath_form"));
                                }
                            }
                        } else {
                            var element = new Element('input', {
                                type: 'hidden',
                                name: key,
                                novalidate: 'novalidate',
                                value: this.formParams[key]
                            });
                            $(element).appendTo($("#simplepath_form"));
                        }
                    }

                    // unable to use this.form, had to resort to direct call
                    $('#simplepath_form').validate({});
                },

                /**
                 * display amazon simple path config section
                 */
                showAmazonConfig: function () {
                    this.$amazonSimplepath.show();
                    this.$amazonSpBack.hide();
                    if (this.$amazonCredentialsHeader.hasClass('open')) {
                        this.$amazonCredentialsHeader.click();
                    }
                },

                /**
                 * hide amazon simple path config.
                 */
                hideAmazonConfig: function () {
                    this.$amazonSimplepath.hide();
                    this.$amazonSpBack.show();
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
                    var self = this;
                    var leftOffset = this.getLeftOffset(requestedWidth),
                        topOffset = this.getTopOffset(requestedHeight),
                        newWindow = window.open(url, 'simplepath', 'scrollbars=yes, width=' + requestedWidth + ', height=' +
                            requestedHeight + ', top=' + topOffset + ', left=' + leftOffset);

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
                    return ((this.windowWidth() / 2) - (requestedWidth / 2)) + dualScreenLeft;
                },

                /**
                 * Determine top offset for popup window
                 * @param requestedHeight
                 * @returns {number}
                 */
                getTopOffset: function (requestedHeight) {
                    var dualScreenTop = window.screenTop !== undefined ? window.screenTop : screen.top;
                    return ((this.windowHeight() / 2) - (requestedHeight / 2)) + dualScreenTop;
                },

                /**
                 * Determine width of popup window
                 * @returns {number}
                 */
                windowWidth: function () {
                    if (window.innerWidth) {
                        return window.innerWidth;
                    } else if (document.documentElement.clientWidth) {
                        return document.documentElement.clientWidth;
                    } else {
                        return screen.width;
                    }
                },

                /**
                 * Determine window height of popup
                 * @returns {number}
                 */
                windowHeight: function () {
                    if (window.innerHeight) {
                        return window.innerHeight;
                    } else if (document.documentElement.clientHeight) {
                        return document.documentElement.clientHeight;
                    } else {
                        return screen.height;
                    }
                }
            }
        );
    }
);
