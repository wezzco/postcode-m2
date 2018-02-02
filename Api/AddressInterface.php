<?php

namespace Wezz\Postcode\Api;

/**
 * Interface AddressInterface
 * @package Wezz\Postcode\Api
 */
interface AddressInterface
{
    /**
     * Method to get postcode address by API
     *
     * @api
     * @param string $postcode
     * @param string $houseNumber
     * @param string $houseNumberAddition
     * @return mixed
     */
    public function getAddress($postcode, $houseNumber, $houseNumberAddition = '');
}
