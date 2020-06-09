define([
    'ko'
], function (ko) {
    'use strict';

    return {
        isLoaded: ko.observable(false),
        isValid: ko.observable(false)
    };
});
