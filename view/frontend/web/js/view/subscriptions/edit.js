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
/*jshint jquery:true*/
define([
    'jquery',
    'Magento_Ui/js/modal/confirm'
], function($, confirmation) {
    "use strict";

    return function (widget) {
        $.widget('mage.subscriptionsEdit', widget, {
            _create: function() {
                var self = this;
                this.options.isAmazonPaySubscription = this._isAmazonPaySelected();
                this.options.paymentMethodLabel = this._getSelectedPaymentLabel();

                this.element.find('.action.save.primary')
                    .on('click', function (event) {
                        self._handleSubscriptionSave.call(self, event);
                    });

                return this._super();
            },

            _isAmazonPaySelected: function () {
                return this.element.find(this.options.paymentSelector)
                    .find(':selected')
                    .data('method') === 'amazon_payment_v2';
            },

            _getSelectedPaymentLabel: function () {
                return this.element.find(this.options.paymentSelector)
                    .find(':selected')[0]
                    .label;
            },

            _handleSubscriptionSave: function (event) {
                var self = this;
                if (this.options.isAmazonPaySubscription && !(this._getSelectedPaymentLabel() === this.options.paymentMethodLabel)) {
                    event.preventDefault();

                    confirmation({
                        title: $.mage.__('Switching from Stored Amazon Pay Method'),
                        content: $.mage.__(`If this is your only subscription using "${this.options.paymentMethodLabel}", it will be deleted from your stored payment methods. Is this OK?`),
                        actions: {
                            confirm: function () {
                                self.element.find('.action.save.primary').unbind('click').click();
                            }
                        },
                        buttons: [{
                            text: $.mage.__('No'),
                            class: 'action-secondary action-dismiss',
                            click: function (event) {
                                this.closeModal(event);
                            }
                        }, {
                            text: $.mage.__('Yes'),
                            class: 'action-primary action-accept',
                            click: function (event) {
                                this.closeModal(event, true);
                            }
                        }]
                    });
                }
            }
        });

        return $.mage.subscriptionsEdit;
    }
});
