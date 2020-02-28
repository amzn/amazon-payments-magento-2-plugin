/**
 * Copyright 2019 Amazon.com, Inc. or its affiliates. All Rights Reserved.
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

define(
    [
        'jquery',
        'Amazon_Payment/js/model/storage',
        'Magento_Theme/js/view/messages',
        'Magento_Ui/js/model/messageList'
    ], function(
        $,
        amazonStorage,
        messagesFactory,
        messageList
    ) {
        'use strict';

        return {
            defaults: {},
            isAmazonAccountLoggedIn: amazonStorage.isAmazonAccountLoggedIn,

            /*
             * Magento's core Checkout module removes the "messages" block from the layout,
             *  so we display them on checkout using the messageList API.
             */
            displayMessages: function () {
                if(this.isAmazonAccountLoggedIn()) {
                    var messagesComponent = messagesFactory();
                    messagesComponent.cookieMessages.forEach(function(message) {
                        if(message.type == 'error') {
                            messageList.addErrorMessage({message: $.mage.__($("<textarea/>").html(message.text).text())});
                        }
                    });
                }
            }
        }
    }
);
