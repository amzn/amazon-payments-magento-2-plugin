/*global define*/

define(
    [
        'jquery',
        "uiComponent",
        'ko',
        'Amazon_Payment/js/model/storage',
	'uiRegistry'
    ],
    function (
        $,
        Component,
        ko,
        amazonStorage,
	registry
    ) {
        'use strict';

        var self;

        return Component.extend({
            defaults: {
                template: 'Amazon_Payment/checkout-sandbox-simulator'
            },
            isAmazonAccountLoggedIn: amazonStorage.isAmazonAccountLoggedIn,
	    isSandboxEnabled: ko.observable(registry.get('amazonPayment').isSandboxEnabled),
            sandboxSimulationReference: amazonStorage.sandboxSimulationReference,
            sandboxSimulationOptions: ko.observableArray(registry.get('amazonPayment').sandboxSimulationOptions),
            initialize: function () {
                self = this;
                this._super();
            }
        });
    }
);
