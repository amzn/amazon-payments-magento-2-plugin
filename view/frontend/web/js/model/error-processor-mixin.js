/*global define*/

define([
    'Amazon_Pay/js/model/storage',
    'mage/url',
    'Magento_Ui/js/model/messageList',
    'mage/translate'
], function (amazonStorage, url, globalMessageList, $t) {
    'use strict';

    return function (errorProcessor) {
        /**
         * @param {Object} response
         * @param {Object} messageContainer
         */
        errorProcessor.process = function (response, messageContainer) {
            var error;

            messageContainer = messageContainer || globalMessageList;

            if (response.status == 401) { //eslint-disable-line eqeqeq
                this.redirectTo(url.build('customer/account/login/'));
            } else {
                try {
                    if (amazonStorage.isAmazonCheckout() && response.hasOwnProperty('message')) {
                        error = {
                            message: $t(response.message)
                        };
                    } else {
                        error = JSON.parse(response.responseText);
                    }
                } catch (exception) {
                    error = {
                        message: $t('Something went wrong with your request. Please try again later.')
                    };
                }
                messageContainer.addErrorMessage(error);
            }
        }

        return errorProcessor;
    }
});
