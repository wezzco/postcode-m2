/**
 * Copyright 2016 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

define(
    function () {
        'use strict';
        return {
            observeCountry: '${ $.parentName }.country_id:value',
            observeDisableCheckbox: '${ $.parentName }.postcode_fieldset.postcode_disable:value',
            observePostcodeField: '${ $.parentName }.postcode_fieldset.postcode_postcode:value',
            observePostcodeStandardField: '${ $.parentName }.postcode:value',
            observeHousenumberField: '${ $.parentName }.postcode_fieldset.postcode_housenumber:value',
            observeHousenumberAdditionField: '${ $.parentName }.postcode_fieldset.postcode_housenumber_addition:value',
            observeHousenumberAdditionManualField: '${ $.parentName }.postcode_fieldset.postcode_housenumber_addition_manual:value'
        }
    }
);