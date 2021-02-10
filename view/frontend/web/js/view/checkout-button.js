/*global define*/

define(
    [
        'uiComponent',
        'Amazon_Pay/js/model/storage'
    ],
    function (
        Component,
        amazonStorage
    ) {
        'use strict';

        return Component.extend(
            {
                defaults: {
                    template: 'Amazon_Pay/checkout-button'
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

