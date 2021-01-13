/*global define*/

define([
    'Amazon_PayV2/js/model/storage'
], function (amazonStorage) {
    'use strict';

    return function (GrandTotal) {
        return GrandTotal.extend({
            /**
             * @return {Boolean}
             */
            isBaseGrandTotalDisplayNeeded: function () {
                if (!amazonStorage.isAmazonCheckout()) {
                    return this._super();
                }

                return false;
            }
        });
    }
});
