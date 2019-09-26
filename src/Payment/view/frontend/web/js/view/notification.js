/*global define*/
define(
    [
        'jquery',
        'underscore',
        'ko',
        'uiComponent',
        'Amazon_Payment/js/model/storage',
        'uiRegistry'
    ],
    function (
        $,
        _,
        ko,
        Component,
        amazonStorage,
        registry
    ) {
        'use strict';

        return Component.extend(
            {
                defaults: {
                    template: 'Amazon_Payment/notification'
                },
                isAmazonAccountLoggedIn: amazonStorage.isAmazonAccountLoggedIn,
                chargeOnOrder: ko.observable(registry.get('amazonPayment').chargeOnOrder),
                isEuPaymentRegion: ko.observable(registry.get('amazonPayment').isEuPaymentRegion),

                /**
                 * Init
                 */
                initialize: function () {
                    this._super();
                }
            }
        );
    }
);
