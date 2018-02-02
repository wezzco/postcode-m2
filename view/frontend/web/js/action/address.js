define(
    [
        'jquery',
        'mage/storage',
        'Magento_Checkout/js/model/url-builder'
    ],
    function ($, storage, urlBuilder) {
        'use strict';

        return function (deferred, postcode, housenumber, housenumberAddition) {
            var serviceUrl, payload;

            serviceUrl = urlBuilder.createUrl('/postcode/address', {});

            payload = {
                postcode: postcode,
                houseNumber: housenumber,
                houseNumberAddition: housenumberAddition
            };

            return storage.post(
                serviceUrl,
                JSON.stringify(payload)
            ).done(
                function (postcodeInformation) {
                    if (postcodeInformation) {
                        deferred.resolve(postcodeInformation);
                    } else {
                        deferred.reject();
                    }
                }
            ).fail(
                function () {
                    deferred.reject();
                }
            );
        };
    }
);
