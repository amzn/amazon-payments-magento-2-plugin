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
    'Magento_Checkout/js/model/quote',
    'Amazon_PayV2/js/model/storage'
], function ($, quote, amazonStorage) {
    'use strict';

    var formSelector = '#co-shipping-form';

    return {
        /**
         * Toggle shipping form address field visibility
         */
        toggleFields: function() {

            if (!amazonStorage.isAmazonCheckout()) {
                return;
            }

            var $form = $(formSelector),
                address = quote.shippingAddress();

            // Hide all shipping fields
            $form.find('.field').hide();

            // Show error/failed validation fields
            $form.find('.field._error').show();

            // Show phone number if required
            $form.find('.field[name="shippingAddress.telephone"]._required').show();

            // Show state/providence drop-down
            if (!address.regionId || address.countryId != 'US') {
                $form.find('.field[name="shippingAddress.region_id"]').show();
            }
        }
    };
});
