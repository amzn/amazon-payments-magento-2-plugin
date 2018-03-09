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

define([
    'jquery',
    'Magento_Catalog/js/catalog-add-to-cart',
    'jquery/ui'
], function ($) {
    'use strict';

    $.widget('amazon.catalogAddToCart', $.mage.catalogAddToCart, {

        /**
         * Set submit
         * @private
         */
        _create: function () {
            //this is overridden here and ignores the redirect option until fixed by Magento (as of 2.1)
            this._bindSubmit();
        },

        /**
         * Bind submit
         * @private
         */
        _bindSubmit: function () {
            var self = this;

            this.element.mage('validation');
            this.element.on('submit', function (e) {
                e.preventDefault();

                if (self.element.valid()) {
                    self.submitForm($(this));
                }
            });
        }
    });

    return $.amazon.catalogAddToCart;
});
