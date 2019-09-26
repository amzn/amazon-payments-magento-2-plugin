/*global define*/

define(
    [
        'uiComponent',
        'Amazon_PayV2/js/model/storage'
    ],
    function (
        Component,
        amazonStorage
    ) {
        'use strict';

        return Component.extend(
            {
                defaults: {
                    template: 'Amazon_PayV2/checkout-button'
                },
                isVisible: !amazonStorage.isAmazonCheckout(),

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

