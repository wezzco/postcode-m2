<?php

namespace Wezz\Postcode\Model;

use Magento\Checkout\Model\ConfigProviderInterface;

/**
 * Class ConfigProvider
 * @package Experius\Postcode\Model
 */
class ConfigProvider implements ConfigProviderInterface
{
    /**
     * @var HelperData
     */
    protected $helperConfig;

    /**
     * ConfigProvider constructor.
     * @param \Wezz\Postcode\Helper\Config $helperConfig
     */
    public function __construct(
        \Wezz\Postcode\Helper\Config $helperConfig
    ) {
        $this->helperConfig = $helperConfig;
    }

    /**
     * Method to get config
     *
     * @return array
     */
    public function getConfig()
    {
        return [
            'wezz_postcode' =>
                [
                    'settings' => $this->helperConfig->getSettingsSet()
                ]
        ];
    }
}
