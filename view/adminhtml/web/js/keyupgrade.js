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

        return Component.extend({

                defaults: {
                    apKeyUpgradeSelector: '#amazon_keyupgrade_start'
                },

                /**
                 * Set list of observable attributes
                 * @returns {exports.initObservable}
                 */
                initObservable: function () {
                    var self = this;

                    self._super();
                    self.initEventHandlers();

                    return self;
                },

                /**
                 * Init event handlers
                 */
                initEventHandlers: function () {
                    var self = this;

                    $(self.apKeyUpgradeSelector).click(function () {
                        $('#keyupgrade_message ').show();
                        $.ajax({
                            url: self.keyUpgradeUrl,
                            data: {
                                scope: self.scope,
                                scopeCode: self.scopeCode,
                                accessKey: self.accessKey
                            },
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
            }
        );
    }
);
