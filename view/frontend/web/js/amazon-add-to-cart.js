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
    'Magento_Customer/js/customer-data'
], function ($, customerData) {
    'use strict';

    return {
        deferredAddToCart: null,
        addedViaAmazon: false,
        amznWidget: null,

        register: function(amznWidget) {
            this.amznWidget = amznWidget;
            var self = this;

            customerData.get('cart').subscribe(function () {
                if(self.addedViaAmazon && self.deferredAddToCart.state() === 'pending') {
                    self.deferredAddToCart.resolve();
                    self.initDeferred();
                }
            });

            amznWidget.amazonPayButton.onClick(function () {
                var form = $('#product_addtocart_form');
                form.submit();
                self.addedViaAmazon = true;
            });

            self.initDeferred();
        },

        initDeferred: function() {
            var self = this;
            this.deferredAddToCart = $.Deferred(function () {
                this.then(() => { self.amznWidget._initCheckout(); });
            });
        }
    };
});
