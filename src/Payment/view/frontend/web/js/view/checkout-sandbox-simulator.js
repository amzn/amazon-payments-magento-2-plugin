/*global define*/

define(
    [
        'jquery',
        "uiComponent",
        'ko',
        'Amazon_Payment/js/model/storage'
    ],
    function (
        $,
        Component,
        ko,
        amazonStorage
    ) {
        'use strict';

        var self;

        return Component.extend({
            defaults: {
                template: 'Amazon_Payment/checkout-sandbox-simulator'
            },
            isAmazonAccountLoggedIn: amazonStorage.isAmazonAccountLoggedIn,
            isSandboxEnabled: ko.observable(window.amazonPayment.isSandboxEnabled),
            sandboxSimulationReference: amazonStorage.sandboxSimulationReference,
            sandboxSimulationOptions: ko.observableArray(window.amazonPayment.sandboxSimulationOptions),
            initialize: function () {
                self = this;
                this._super();
            }
        });
    }
);