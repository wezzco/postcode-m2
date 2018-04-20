<?php

namespace Wezz\Postcode\Model;

/**
 * Class Address
 * @package Wezz\Postcode\Model
 */
class Address
{
    /**
     * @var \Wezz\Postcode\Api\ClientApi
     */
    private $clientApi;

    /**
     * Address constructor.
     * @param Api\ClientApi $clientApi
     */
    public function __construct(
        \Wezz\Postcode\Model\Api\ClientApi $clientApi
    ) {
        $this->clientApi = $clientApi;
    }

    /**
     * Method to get address by postcode
     *
     * @api
     * @param string $postcode
     * @param string $houseNumber
     * @param string $houseNumberAddition
     * @return string
     */
    public function getAddress($postcode, $houseNumber, $houseNumberAddition)
    {
        $result = $this->clientApi->lookupAddress($postcode, $houseNumber, $houseNumberAddition);
        return json_encode($result);
    }
}
