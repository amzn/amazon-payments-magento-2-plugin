/*global define*/

define([
    'Amazon_Payment/js/model/storage'
], function (amazonStorage) {
    'use strict';

    return function (GrandTotal) {
        return GrandTotal.extend({
            /**
             * @return {Boolean}
             */
            isBaseGrandTotalDisplayNeeded: function () {
                if (!amazonStorage.isAmazonAccountLoggedIn()) {
                    return this._super();
                }

                return false;
            }
        });
    }
});
