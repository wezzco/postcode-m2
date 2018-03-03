define([
    'jquery',
    'Magento_Ui/js/form/components/group',
    'Wezz_Postcode/js/action/address',
    'Wezz_Postcode/js/model/settings',
    'Wezz_Postcode/js/model/default',
    'Magento_Checkout/js/checkout-data',
    'uiRegistry'
], function (
    $,
    Abstract,
    getAddressAction,
    settings,
    defaultInit,
    checkoutData,
    registry
) {
    'use strict';
    return Abstract.extend({
        defaults: defaultInit,
        getAddressData: function () {
            if (this.addressType == 'shipping' && typeof checkoutData.getShippingAddressFromData() !== 'undefined' && checkoutData.getShippingAddressFromData()) {
                return checkoutData.getShippingAddressFromData();
            } else if (this.addressType == 'billing' && typeof checkoutData.getBillingAddressFromData() !== 'undefined' && checkoutData.getBillingAddressFromData()) {
                return checkoutData.getBillingAddressFromData();
            } else if (this.source) {
                return this.source.get(this.customScope);
            } else {
                return;
            }
        },
        /**
         * Init method
         */
        initialize: function () {
            this._super()
                ._setClasses();
            var self = this;

            registry.async(this.provider)(function () {
                self.initModules();
                self.postcodeRefresh();
            });

            self.debug('Postcode: initialize');

            if (settings.neverHideCountry == "0") {
                self.debug('Postcode: set country_id NL by default according to never_hide_country configuration');
                self.updateFields(['country_id'], 'visible', false);
                self.updateFields(['country_id'], 'value', 'NL');
            }

            return this;
        },
        /**
         * Init observable
         */
        initObservable: function () {
            var rules = this.validation = this.validation || {};
            this._super().observe(['isLoading']);
            this.observe('isLoading checked error disabled focused info preview visible value warn isDifferedFromDefault notice')
                .observe('isUseDefault')
                .observe({
                    'required': !!rules['required-entry']
                });
            return this;
        },
        /**
         * Refresh fields
         */
        postcodeRefresh: function () {
            var self = this;
            if (self.timeout != undefined) {
                clearTimeout(settings.timeout);
            }
            self.timeout = setTimeout(function () {
                self.postcodeDispatch();
            }, self.checkDelay);
        },
        /**
         * Dispatch refresh
         */
        postcodeDispatch: function () {
            var self = this;

            if (!this.source) {
                return;
            }

            var formData = this.source.get(this.customScope);
            if (!formData) {
                return;
            }

            if (!formData.postcode_disable && formData.country_id == settings.countryCode) {
                self.updateFields(self.postcodeFields, 'visible', true);
                self.updateFields(self.standardFields, 'visible', false);
                self.updateFields(self.standardPostcodeFields, 'visible', false);
                self.updateFields(['region_id_input'], 'required', false);
                self.getAddressByIp();
                self.info(settings.translations.fillOut);

                var streetElement = registry.get(self.parentName + '.street');
                var additionalClasses = streetElement.get('additionalClasses');
                additionalClasses["street-field-set"] = true;
                streetElement.set('additionalClasses', additionalClasses);
                $("fieldset.street").addClass("street-field-set");
            } else if (formData.country_id == settings.countryCode && formData.postcode_disable) {
                self.updateFields(self.postcodeFields, 'visible', true);
                self.updateFields(self.standardFields, 'visible', true);

                var streetElement = registry.get(self.parentName + '.street');
                var additionalClasses = streetElement.get('additionalClasses');
                additionalClasses["street-field-set"] = false;
                streetElement.set('additionalClasses', additionalClasses);
                $("fieldset.street").removeClass("street-field-set");

                self.updateFields(self.standardPostcodeFields, 'visible', true);
                self.updateFields(['region_id_input'], 'required', false);
                self.error(null);
                self.notice(null);
                self.info(settings.translations.fillOut);

                var streetFull = registry.get(self.parentName + '.street.0').value();

                var streetArray = streetFull.split(" ");

                var clearStreet = '';

                if (streetArray.length > 0) {
                    clearStreet = streetArray[0];
                }

                if (settings.useStreet2AsHouseNumber != 0) {
                    self.updateFields(['street.0'], 'value', clearStreet);
                    var street1 = formData.postcode_housenumber.toString();
                    if (formData.postcode_housenumber_addition_manual) {
                        street1 += ' ' + formData.postcode_housenumber_addition_manual;
                    }
                    self.updateFields(['street.1'], 'value', street1);
                } else if (settings.useStreet3AsHouseNumber != 0) {
                    self.updateFields(['street.0'], 'value', clearStreet);
                    self.updateFields(['street.1'], 'value', formData.postcode_housenumber.toString());
                    if (formData.postcode_housenumber_addition_manual) {
                        self.updateFields(['street.2'], 'value', formData.postcode_housenumber_addition_manual);
                    }
                } else {
                    var street0 = clearStreet + ' ' + formData.postcode_housenumber;
                    if (formData.postcode_housenumber_addition_manual) {
                        street0 += ' ' + formData.postcode_housenumber_addition_manual;
                    }
                    self.updateFields(['street.0'], 'value', street0);
                }
            } else {
                self.updateFields(self.standardFields, 'visible', true);
                self.updateFields(self.standardPostcodeFields, 'visible', true);
                self.updateFields(self.postcodeFields, 'visible', false);
                self.updateFields(['postcode_fieldset.postcode_housenumber'], 'visible', false);
                self.updateFields(['postcode_fieldset.postcode_housenumber_addition'], 'visible', false);

                var streetElement = registry.get(self.parentName + '.street');
                var additionalClasses = streetElement.get('additionalClasses');
                additionalClasses["street-field-set"] = false;
                streetElement.set('additionalClasses', additionalClasses);
                $("fieldset.street").removeClass("street-field-set");

                self.error(null);
                self.notice(null);
                self.info(null);
            }
        },
        /**
         * Update form fields properties
         * @param fields
         * @param property
         * @param value
         */
        updateFields: function (fields, property, value) {
            var self = this;
            var property = property;
            var value = value;

            $.each(fields, function (key, field) {
                registry.async(self.parentName + '.' + field)(function () {
                    console.log(field + ' - ' + property + ' - ' + value);
                    registry.get(self.parentName + '.' + field).set(property, value);
                });
            });
        },
        /**
         * Update fields after country is changed
         * @param address
         */
        updateFieldsByCountry: function (address) {
            var self = this;
            self.debug('Postcode: updated fields by selected country');

            if (address && address.country_id == settings.countryCode && !address.postcode_disable) {
                self.info(settings.translations.fillOut);
                self.postcodeRefresh();
            } else if (address && address.country_id == settings.countryCode && address.postcode_disable) {
                self.info(settings.translations.fillOut);
                self.postcodeRefresh();
            } else {
                self.error(null);
                self.notice(null);
                self.info(null);
                self.postcodeRefresh();
            }
        },
        /**
         * Observe country
         * @param value
         */
        observeCountry: function (value) {
            if (value) {
               this.updateFieldsByCountry(this.getAddressData());
            }
        },
        /**
         * Observe postcode fields
         * @param value
         */
        observePostcodeField: function (value) {
            if (value) {
                this.postcodeRefresh();
            }
        },
        /**
         * Observe disable checkbox
         * @param value
         */
        observeDisableCheckbox: function (value) {
            var self = this;
            self.editManually(this.getAddressData());
        },
        /**
         * Observe housenumber field
         * @param value
         */
        observeHousenumberField: function (value) {
            if (value) {
                this.postcodeRefresh();
            }
        },
        /**
         * Observe housenumber field
         * @param value
         */
        observeHousenumberAdditionField: function (value) {
            this.postcodeRefresh();
        },
        /**
         * Observe housenumber addition manual field
         * @param value
         */
        observeHousenumberAdditionManualField: function (value) {
            this.postcodeRefresh();
        },
        /**
         * Edit manually method
         * @param address
         */
        editManually: function (address) {
            var self = this;

            if (address && address.country_id == settings.countryCode && address.postcode_disable) {
                self.updateFields(self.standardFields, 'visible', true);
                self.updateFields(['postcode_fieldset.postcode_housenumber_addition'], 'visible', false);
                var houseNumberAddition = registry.get(self.parentName + '.postcode_fieldset.postcode_housenumber_addition').value();
                self.updateFields(['postcode_fieldset.postcode_housenumber_addition_manual'], 'value', houseNumberAddition);
                self.updateFields(['postcode_fieldset.postcode_housenumber_addition_manual'], 'visible', true);
                self.updateFields(['postcode_fieldset.postcode_fieldset.postcode_disable'], 'visible', true);
                self.error(null);
                self.notice(null);
                self.debug('Postcode: edit manually state');
            } else if (address && address.country_id == settings.countryCode && !address.postcode_disable) {
                self.updateFields(self.postcodeFields, 'visible', true);
                self.updateFields(['postcode_fieldset.postcode_housenumber_addition_manual'], 'visible', false);
                self.updateFields(['postcode_fieldset.postcode_housenumber_addition'], 'visible', true);
                self.updateFields(self.standardFields, 'visible', false);
                self.updateFields(self.standardPostcodeFields, 'visible', false);
                self.getAddressByIp();
                self.debug('Postcode: edit with Postcode API state');

                var streetElement = registry.get(self.parentName + '.street');
                var additionalClasses = streetElement.get('additionalClasses');
                additionalClasses["street-field-set"] = true;
                streetElement.set('additionalClasses', additionalClasses);
                $("fieldset.street").addClass("street-field-set");
            }
        },
        /**
         * Validate api request
         */
        validateRequest: function () {
            if (this.checkRequest != null && $.inArray(this.checkRequest.readyState, [1, 2, 3])) {
                this.checkRequest.abort();
                this.checkRequest = null;
            }
        },
        /**
         * Method to render address content on notice block
         */
        renderAddressContent: function () {
            var addressContent = '<i>';
            addressContent = '<h3>' + settings.translations.yourAddress + '</h3>';

            if (!this.source) {
                return;
            }

            var self = this;

            var address = this.source.get(this.customScope);

            $.each(address.street, function (index, street) {
                addressContent += street + ' ';
            });

            addressContent += "<br/>";
            addressContent += address.postcode;
            addressContent += "<br/>";
            addressContent += address.city;
            addressContent += "<br/>";
            addressContent += address.region;
            addressContent += "</i>";

            self.debug('Postcode: render address information');

            this.notice(addressContent);
        },
        /**
         * Method to get address information from Postcode API
         */
        getAddressByIp: function () {

            var self = this;
            var response = false;

            if (!this.source) {
                return;
            }

            var formData = this.source.get(this.customScope);

            if (formData.postcode_postcode && formData.postcode_housenumber) {
                this.validateRequest();
                this.isLoading(true);

                this.isPostcodeCheckComplete = $.Deferred();

                self.debug('Postcode: get address by Postcode API; ' +
                    'postcode: ' + formData.postcode_postcode + '; ' +
                    'postcode_housenumber: ' + formData.postcode_housenumber + '; ' +
                    'postcode_housenumber_addition: ' + formData.postcode_housenumber_addition);

                this.checkRequest = getAddressAction(this.isPostcodeCheckComplete, formData.postcode_postcode, formData.postcode_housenumber, formData.postcode_housenumber_addition);

                $.when(this.isPostcodeCheckComplete).done(function (data) {
                    var response = JSON.parse(data);

                    if (settings.apiShowCase) {
                        console.log('Postcode API SHOWCASE:', response);
                    }

                    if (response) {
                        if (response.street) {
                            self.error(null);
                            self.notice(null);

                            self.debug('Postcode: API request result:');
                            self.debug(response);

                            if (settings.useStreet2AsHouseNumber != 0) {
                                self.updateFields(['street.0', 'street.1'], 'error', false);
                                self.updateFields(['street.0'], 'value', response.street);

                                var street1 = response.houseNumber.toString();

                                if (response.houseNumberAddition) {
                                    street1 += ' ' + response.houseNumberAddition;
                                }

                                self.updateFields(['street.1'], 'value', street1);
                            } else if (settings.useStreet3AsHouseNumber != 0) {
                                self.updateFields(['street.0'], 'error', false);
                                self.updateFields(['street.0'], 'value', response.street);
                                self.updateFields(['street.1'], 'value', response.houseNumber);
                                if (response.houseNumberAddition) {
                                    self.updateFields(['street.2'], 'value', response.houseNumberAddition);
                                }
                            } else {
                                var street0 = response.street + ' ' + response.houseNumber;
                                if (response.houseNumberAddition) {
                                    street0 += ' ' + response.houseNumberAddition;
                                }
                                self.updateFields(['street.0'], 'value', street0);
                            }

                            self.updateFields(['country_id', 'region_id', 'city', 'region_id_input'], 'error', false);
                            self.updateFields(['country_id'], 'value', settings.countryCode);
                            self.updateFields(['postcode'], 'value', response.postcode);
                            self.updateFields(['region_id_input'], 'value', response.province);
                            self.updateFields(['city'], 'value', response.city);

                            self.renderAddressContent();

                            self.setHouseNumberAdditions(response.houseNumberAdditions);
                        } else {
                            self.error(response.message);
                            self.notice(null);
                        }
                    } else {
                        self.debug('Postcode: API request empty:');
                        self.debug(response);
                    }
                    self.isLoading(false);

                }).fail(function () {
                    self.debug('Postcode: API request failed;');
                    self.isLoading(false);
                }).always(function () {

                });
            }
        },
        /**
         * Method to render house number additions selectbox
         * @param additions
         */
        setHouseNumberAdditions: function (additions) {

            var self = this;

            if (additions.length > 1) {
                var element = registry.get(this.parentName + '.postcode_fieldset.postcode_housenumber_addition');

                var currentValue = element.value();

                var currentOptions = element.options();

                var tempCurrentArray = [];

                $.each(currentOptions, function (key, value) {
                     if (value.value) {
                         tempCurrentArray.push(value.value);
                     }
                });

                var additionItems = [];
                var tempNewArray = [];

                $.each(additions, function (key, addition) {
                    if (!addition) {
                        additionItems[key] = {'label': '' + settings.translations.select, 'labeltitle': '' + settings.translations.select, 'value': ''};
                    } else {
                        var additionTrim = addition.replace(" ", "");
                        tempNewArray.push(additionTrim);
                        additionItems[key] = {
                            'label': additionTrim,
                            'labeltitle': additionTrim ,
                            'value': additionTrim
                        };
                    }
                });

                if (JSON.stringify(tempCurrentArray) != JSON.stringify(tempNewArray)) {
                    self.debug('Postcode: update house number additions select list', tempNewArray);

                    self.updateFields(['postcode_fieldset.postcode_housenumber_addition'], 'visible', true);
                    self.updateFields(['postcode_fieldset.postcode_housenumber_addition'], 'options', additionItems);
                    self.updateFields(['postcode_fieldset.postcode_housenumber_addition'], 'value', currentValue);
                }
            } else {
                self.updateFields(['postcode_fieldset.postcode_housenumber_addition'], 'visible', false);
                self.updateFields(['postcode_fieldset.postcode_housenumber_addition'], 'value', '');
            }
        },
        debug: function (message) {
            if (settings.apiDebug) {
                console.log(message);
            }
        },
    });
});
