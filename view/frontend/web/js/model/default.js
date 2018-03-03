/**
 * Copyright 2016 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

define(
    ['Wezz_Postcode/js/model/observe'],
    function (observe) {
        'use strict';
        return {
            checkDelay: 500,
            standardFields: ['street', 'street.0', 'street.1', 'street.2', 'city', 'region_id_input'],
            standardPostcodeFields: ['postcode'],
            postcodeDisableFields: [''],
            postcodeFields: [
                'postcode_fieldset.postcode_postcode',
                'postcode_fieldset.postcode_disable',
                'postcode_fieldset.postcode_housenumber',
            ],
            CheckTimeout: 0,
            isLoading: false,
            checkRequest: null,
            isPostcodeCheckComplete: null,
            postcodeCheckValid: true,
            addressType: 'shipping',
            imports: observe,
            listens: {
                '${ $.provider }:${ $.customScope ? $.customScope + "." : ""}data.validate': 'validate',
            },
            visible: true
        }
    }
);
