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
    'Amazon_Pay/js/model/amazon-pay-config',
    'Amazon_Pay/js/model/storage'
], function (amazonConfig, amazonStorage) {
    'use strict';

    var mixin = {
        /**
         * Clear potential checkout session info after checking out with an AP stored token.
         */
        afterPlaceOrder: function () {
            if (this.getCode() === amazonConfig.getVaultCode()) {
                amazonStorage.clearAmazonCheckout();
            }
        }
    };

    return function (target) {
        return target.extend(mixin);
    };
});
