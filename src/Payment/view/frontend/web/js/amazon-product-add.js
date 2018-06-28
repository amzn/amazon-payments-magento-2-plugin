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
    'Magento_Customer/js/customer-data',
    'amazonCore',
    'jquery/ui'
], function ($, customerData) {
    'use strict';

    var _this,
        addedViaAmazon = false;

    $.widget('amazon.AmazonProductAdd', {
        options: {
            addToCartForm: '#product_addtocart_form'
        },

        /**
         * Create triggers
         */
        _create: function () {
            _this = this;
            this.setupTriggers();
        },

        /**
         * Setup triggers when item added to cart if amazon pay button pressed
         */
        setupTriggers: function () {
            this.cart = customerData.get('cart');

            //subscribe to add to cart event
            this.cart.subscribe(function () {
                //only trigger the amazon button click if the user has chosen to add to cart via this method
                if (addedViaAmazon) {
                    addedViaAmazon = false;
                    $('.login-with-amazon img').trigger('click');
                }
            }, this);

            //setup binds for click
            $('.amazon-addtoCart').on('click', function () {
                if ($(_this.options.addToCartForm).valid()) {
                    addedViaAmazon = true;
                    $(_this.options.addToCartForm).submit();
                }
            });
        }

    });

    return $.amazon.AmazonProductAdd;
});
