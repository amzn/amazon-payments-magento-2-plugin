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
        initObservable: function () {
            this._super();
            amazonStorage.isAmazonAccountLoggedIn.subscribe(function (value) {
                var elem = document.getElementById('co-shipping-form');
                if (elem && value === false) {
                    document.getElementById(this.formSelector).style.display = 'block';
                }
            }, this);
            return this;
        },
        manipulateInlineForm: function () {
            if (amazonStorage.isAmazonAccountLoggedIn()) {
                var elem = document.getElementById('co-shipping-form');
                if (elem) {
                    document.getElementById(this.formSelector).style.display = 'none';
                }
            }
        }
    });
});
