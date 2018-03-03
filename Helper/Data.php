<?php

namespace Wezz\Postcode\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

/**
 * Class Data
 * @package Wezz\Postcode\Helper
 */
class Data extends AbstractHelper
{
    /**
     * @var ProductMetadataInterface
     */
    protected $productMetadataInterface;

    /**
     * @var ModuleListInterface
     */
    protected $moduleList;

    /**
     * @var null
     */
    protected $modules = null;

    /**
     * Data constructor.
     * @param \Magento\Framework\App\ProductMetadataInterface $productMetadataInterface
     * @param \Magento\Framework\Module\ModuleListInterface $moduleList
     */
    public function __construct(
        \Magento\Framework\App\ProductMetadataInterface $productMetadataInterface,
        \Magento\Framework\Module\ModuleListInterface $moduleList
    ) {
        $this->productMetadataInterface = $productMetadataInterface;
        $this->moduleList = $moduleList;
    }

    /**
     * Get user agent method
     *
     * @return string
     */
    public function getUserAgent()
    {
        return 'PostcodeNl_Api_MagentoPlugin/' . $this->getExtensionVersion() . ' ' .
            $this->getMagentoVersion() . ' PHP/' . phpversion() . ' EnrichType/' . $this->getEnrichType();
    }

    /**
     * Get extension version method
     *
     * @return string
     */
    public function getExtensionVersion()
    {
        $extensionInfo = $this->getModuleInfo('PostcodeNl_Api');

        $extensionVersion = ($extensionInfo && isset($extensionInfo['version'])) ? (string) $extensionInfo['version'] : 'unknown';

        return $extensionVersion;
    }

    /**
     * Get magento version
     *
     * @return string
     */
    public function getMagentoVersion()
    {
        if ($this->getModuleInfo('Enterprise_CatalogPermissions') != null) {
            return 'MagentoEnterprise/' . $this->productMetadataInterface->getVersion();
        } elseif ($this->getModuleInfo('Enterprise_Enterprise') != null) {
            return 'MagentoProfessional/' . $this->productMetadataInterface->getVersion();
        }

        return 'Magento/' . $this->productMetadataInterface->getVersion();
    }

    /**
     * Get module info method
     *
     * @param $moduleName
     * @return null
     */
    protected function getModuleInfo($moduleName)
    {
        $modules = $this->getMagentoModules();

        if (!isset($modules[$moduleName])) {
            return null;
        }

        return $modules[$moduleName];
    }

    /**
     * Get Magento Modules method
     *
     * @return array
     */
    public function getMagentoModules()
    {
        if ($this->modules !== null) {
            return $this->modules;
        }

        $this->modules = [];

        foreach ($this->moduleList->getAll() as $name => $module) {
            $this->modules[$name] = [];
            foreach ($module as $key => $value) {
                if (in_array((string)$key, ['setup_version', 'name'])) {
                    $this->modules[$name][$key] = (string)$value;
                }
            }
        }

        return $this->modules;
    }

    /**
     * Get enrich type method
     *
     * @return int
     */
    protected function getEnrichType()
    {
        return 0;
    }

    /**
     * Method to check for SSL support in CURL
     * @return int
     */
    protected function curlHasSsl()
    {
        $curlVersion = curl_version();

        return $curlVersion['features'] & CURL_VERSION_SSL;
    }

    /**
     * Method to check capabilities
     *
     * @return array
     */
    public function checkCapabilities()
    {
        $result = [];

        if (!$this->curlHasSsl()) {
            $result = [
                'message' => $this->__('Cannot connect to Postcode.nl API: Server is missing SSL (https) support for CURL.')];
        }

        return $result;
    }
}
