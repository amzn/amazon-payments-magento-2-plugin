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

define([
    'jquery',
    'uiComponent',
    'Amazon_PayV2/js/action/bind-amazon-change-action',
    'Amazon_PayV2/js/model/storage',
    'Amazon_PayV2/js/model/checkout-messages',
], function ($, Component, bindAmazonChangeAction, amazonStorage, messageContainer) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Amazon_PayV2/checkout-messages'
        },

        /** @inheritdoc */
        initialize: function () {
            this._super()
            this.messageContainer = messageContainer;

            return this;
        },

        /**
         * @return {Boolean}
         */
        isVisible: function () {
            return amazonStorage.isAmazonCheckout() && this.messageContainer.hasMessages();
        },

        bindMessageObervers: function () {
            var editSelector = '.messages .edit-address-link';
            if ($(editSelector).length) {
                bindAmazonChangeAction(editSelector, 'changeAddress');
            }
        }
    });
});
