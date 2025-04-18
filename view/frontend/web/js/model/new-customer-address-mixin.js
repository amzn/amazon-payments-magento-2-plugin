define([
    'mage/utils/wrapper'
], function (wrapper) {
    'use strict';

    /**
     * @param {Object} addressData
     * Returns new address object
     */
    return function (newCustomerAddress) {
        newCustomerAddress = wrapper.wrap(newCustomerAddress, function (originalFunction, addressData) {
            var address = originalFunction(addressData);
            if (address.regionId === undefined) {
                address.regionId = null;
            }

            return address;
        });

        return newCustomerAddress;
    };
});
