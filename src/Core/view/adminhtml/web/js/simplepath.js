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
/*browser:true*/
/*global define*/
define([
    'jquery',
    'uiComponent',
    'jquery/ui',
    'jquery/validate',
    'mage/translate'
], function ($, Class) {
    'use strict';

    return Class.extend({

        defaults: {
            $amazonFields: null,
            $amazonCredentialJson: null,
            selector: 'amazon_payment',
            $container: null,
            $submitButton: null,
            pollInterval: 1500,
            pollTimer: null,
            $form: null,
        },

        /**
         * Set list of observable attributes
         * @returns {exports.initObservable}
         */
        initObservable: function () {
            var self = this;
            self.$amazonFields = $('#payment_' + self.getCountry() + '_' + self.selector + ' .form-list');
            self.$amazonCredentialJson = $('#row_payment_' + self.getCountry() + '_' + self.selector + '_credentials_credentials_json');
            self.$container = $('#' + self.container);


            if (!self.$form) {
                self.$form = new Element('form', {
                    method: 'post',
                    action: self.amazonUrl,
                    id: 'simplepath_form',
                    target: 'simplepath',
                    novalidate: 'novalidate'
                });

                self.$container.wrap(self.$form);
                $('#simplepath_form').validate({});

            }

            self._super();

            self.initEventHandlers();

            return self;
        },

        /**
         * Get payment code
         * @returns {String}
         */
        getCountry: function () {
            return this.co;
        },

        /**
         * Init event handlers
         */
        initEventHandlers: function () {

        },


    });
});
