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
    'Amazon_PayV2/js/model/storage',
    'Amazon_PayV2/js/amazon-checkout'
], function (amazonStorage, amazonCheckout) {
    'use strict';

    return function (selector, changeAction) {
        amazonCheckout.withAmazonCheckout(function (amazon) {
            amazon.Pay.bindChangeAction(selector, {
                amazonCheckoutSessionId: amazonStorage.getCheckoutSessionId(),
                changeAction: changeAction
            });
        });
    };
});
