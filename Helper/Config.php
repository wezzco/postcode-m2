<?php

namespace Wezz\Postcode\Helper;

/**
 * Class Data
 * @package Wezz\Postcode\Helper
 */
class Config
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var string
     */
    private $scopeStore = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;

    /**
     * @var \Magento\Developer\Helper\Data
     */
    private $developerHelper;

    /**
     * Config constructor.
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Developer\Helper\Data $developerHelper
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Developer\Helper\Data $developerHelper
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->developerHelper = $developerHelper;
    }

    /**
     * Get enabled configuration
     *
     * @return mixed
     */
    public function getEnabled()
    {
        return $this->scopeConfig->getValue(
            'postcodenl_api/general/enabled',
            $this->scopeStore
        );
    }

    /**
     * Get api password
     *
     * @return string
     */
    public function getApiPassword()
    {
        return $this->getApiKey() . ':' . $this->getApiSecret();
    }

    /**
     * Get api_url configuration
     *
     * @return mixed
     */
    public function getApiUrl()
    {
        return $this->scopeConfig->getValue(
            'postcodenl_api/development_config/api_url',
            $this->scopeStore
        );
    }

    /**
     * Get use_street2_as_housenumber configuration
     *
     * @return mixed
     */
    public function getUseStreet2AsHouseNumber()
    {
        return $this->scopeConfig->getValue(
            'postcodenl_api/advanced_config/use_street2_as_housenumber',
            $this->scopeStore
        );
    }

    /**
     * Get use_street3_as_housenumber configuration
     *
     * @return mixed
     */
    public function getUseStreet3AsHouseNumber()
    {
        return $this->scopeConfig->getValue(
            'postcodenl_api/advanced_config/use_street3_as_housenumber_addition',
            $this->scopeStore
        );
    }

    /**
     * Get never_hide_country configuration
     *
     * @return mixed
     */
    public function getNeverHideCountry()
    {
        return $this->scopeConfig->getValue(
            'postcodenl_api/advanced_config/never_hide_country',
            $this->scopeStore
        );
    }

    /**
     * Get postcodenl_api/advanced_config/admin_validation_enabled configuration
     *
     * @return mixed
     */
    public function getAdminValidationEnabled()
    {
        return $this->scopeConfig->getValue(
            'postcodenl_api/advanced_config/admin_validation_enabled',
            $this->scopeStore
        );
    }

    /**
     * Get api_showcase configuration
     *
     * Not used now;
     *
     * @return mixed
     */
    public function getApiShowcase()
    {
        return false;
    }

    /**
     * Get api_key configuration
     *
     * @return mixed
     */
    public function getApiKey()
    {
        return $this->scopeConfig->getValue(
            'postcodenl_api/general/api_key',
            $this->scopeStore
        );
    }

    /**
     * Get api_secret configuration
     *
     * @return mixed
     */
    public function getApiSecret()
    {
        return $this->scopeConfig->getValue(
            'postcodenl_api/general/api_secret',
            $this->scopeStore
        );
    }

    /**
     * Get block post addresses configuration
     *
     * @return mixed
     */
    public function getBlockPostAddresses()
    {
        return $this->scopeConfig->getValue(
            'postcodenl_api/advanced_config/block_postofficeboxaddresses',
            $this->scopeStore
        );
    }

    /**
     * Get settings set method
     *
     * @return array
     */
    public function getSettingsSet()
    {
        return [
            'timeout' => 0,
            'enabled' => $this->getEnabled(),
            'useStreet2AsHouseNumber' => $this->getUseStreet2AsHouseNumber(),
            'useStreet3AsHouseNumber' => $this->getUseStreet3AsHouseNumber(),
            'neverHideCountry' => $this->getNeverHideCountry(),
            'apiDebug' => $this->getIsDebug(),
            'apiShowcase' => $this->getApiShowcase(),
            'countryCode' => 'NL',
            'translations' => [
                'defaultError' => __('Unknown postcode + housenumber combination.'),
                'fillOut' => __(
                    '<h3>Address validation</h3>
                    Fill out your postcode and housenumber to auto-complete your address.'
                ),
                'select' => __('Select...'),
                'validatedAddress' => __('Validated address'),
                'yourAddress' => __('Your address:')
            ]
        ];
    }

    /**
     * Get API debug
     *
     * @return mixed
     */
    public function getApiDebug()
    {
        return $this->scopeConfig->getValue(
            'postcodenl_api/development_config/api_debug',
            $this->scopeStore
        );
    }

    /**
     * Check if we're currently in debug mode, and if the current user may see dev info.
     *
     * @return bool
     */
    public function getIsDebug()
    {
        if ($this->getApiDebug() && $this->developerHelper->isDevAllowed()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Method to check basic api settings
     *
     * @return array
     */
    public function checkBasicApiSettings()
    {
        $result = [];

        if (!$this->getApiUrl() || !$this->getApiKey() || !$this->getApiSecret()) {
            $result = [
                'message' => __('Postcode.nl API not configured.'),
                'info' => [__('Configure your `API key` and `API secret`.')]
            ];
        }

        return $result;
    }
}
