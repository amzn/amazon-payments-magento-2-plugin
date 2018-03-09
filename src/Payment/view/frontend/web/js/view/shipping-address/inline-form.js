define([
    'uiComponent',
    'ko',
    'Amazon_Payment/js/model/storage'
], function (Component, ko, amazonStorage) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Amazon_Payment/shipping-address/inline-form',
            formSelector: 'co-shipping-form'
        },

        /**
         * Init inline form
         */
        initObservable: function () {
            this._super();
            amazonStorage.isAmazonAccountLoggedIn.subscribe(function (value) {
                var elem = document.getElementById(this.formSelector);

                if (elem && value === false) {
                    document.getElementById(this.formSelector).style.display = 'block';
                }
            }, this);

            return this;
        },

        /**
         * Show/hide inline form
         */
        manipulateInlineForm: function () {
            var elem;

            if (amazonStorage.isAmazonAccountLoggedIn()) {
                elem = document.getElementById(this.formSelector);

                if (elem) {
                    document.getElementById(this.formSelector).style.display = 'none';
                }
            }
        }
    });
});
