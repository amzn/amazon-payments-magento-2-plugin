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
            amazonStorage.isAmazonAccountLoggedIn.subscribe(this.hideInlineForm, this);
            return this;
        },

        /**
         * Show/hide inline form depending on Amazon login status
         */
        manipulateInlineForm: function () {
            this.hideInlineForm(amazonStorage.isAmazonAccountLoggedIn());
        },

        /**
         * Show/hide inline form
         */
        hideInlineForm: function(hide) {
            var elem = document.getElementById(this.formSelector);

            if (elem) {
                document.getElementById(this.formSelector).style.display = hide ? 'none' : 'block';
            }
        }
    });
});
