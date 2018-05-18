/*global define*/

define(
    [
        'jquery',
        'uiComponent',
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
        if (registry.get('amazonPayment') !== undefined) {
            return Component.extend({
                defaults: {
                    template: 'Amazon_Payment/checkout-sandbox-simulator'
                },
                isAmazonAccountLoggedIn: amazonStorage.isAmazonAccountLoggedIn,
                isSandboxEnabled: ko.observable(registry.get('amazonPayment').isSandboxEnabled),
                sandboxSimulationReference: amazonStorage.sandboxSimulationReference,
                sandboxSimulationOptions: ko.observableArray(registry.get('amazonPayment').sandboxSimulationOptions),

                /**
                 * Init
                 */
                initialize: function () {
                    this._super();
                }
            });
        }
        else {
            return Component.extend({});
        }
    }
);
