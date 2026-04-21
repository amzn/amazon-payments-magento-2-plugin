define(['jquery'], function ($) {
    'use strict';

    if (!$.fn.uniqueId) {
        var uuid = 0;

        $.fn.extend({
            uniqueId: function () {
                return this.each(function () {
                    if (!this.id) {
                        this.id = 'ui-id-' + (++uuid);
                    }
                });
            },

            removeUniqueId: function () {
                return this.each(function () {
                    if (/^ui-id-\d+$/.test(this.id)) {
                        $(this).removeAttr('id');
                    }
                });
            }
        });
    }
});
