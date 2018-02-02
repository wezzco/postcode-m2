<?php

namespace Wezz\Postcode\Block\Checkout;

use Magento\Framework\View\Element\AbstractBlock;
use Magento\Checkout\Block\Checkout\LayoutProcessorInterface;

/**
 * Class LayoutOSCProcessor - Layout Processor for AheadWorks OneStepCheckout checkout form
 * @package Wezz\Postcode\Block\Checkout
 */
class LayoutOSCProcessor extends AbstractBlock implements LayoutProcessorInterface
{
    /**
     * @var \Wezz\Postcode\Helper\Config
     */
    protected $helperConfig;

    /**
     * LayoutProcessor constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Wezz\Postcode\Helper\Config $helperConfig,
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    ) {
        $this->helperConfig = $helperConfig;
        parent::__construct($context, $data);
    }

    /**
     * Check post code method
     *
     * @param $result
     * @return bool
     */
    private function checkPostcode($result)
    {
        if ($this->helperConfig->getEnabled() && isset(
                $result['components']
                ['checkout']
                ['children']
                ['shippingAddress']
                ['children']
                ['shipping-address-fieldset']
                ['children'])
        ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param array $result
     * @return array
     */
    public function process($result)
    {

        if (!$this->checkPostcode($result)) {
            return $result;
        }

        $shippingFields = $result['components']['checkout']['children']['shippingAddress']
            ['children']['shipping-address-fieldset']['children'];

        $shippingFields['postcode_fieldset'] = $this->getFieldArray('shippingAddress', 'shipping');
        
        $result['components']['checkout']['children']['shippingAddress']
        ['children']['shipping-address-fieldset']['children'] = $shippingFields;

        $result = $this->getBillingFormFields($result);

        return $result;
    }

    /**
     * Get field array method
     *
     * @param $customScope
     * @param $addressType
     * @return array
     */
    private function getFieldArray($customScope, $addressType)
    {
        return array(
            'component' => 'Wezz_Postcode/js/view/postcode',
            'type' => 'group',
            'config' => array(
                "customScope" => $customScope,
                "template" => 'Wezz_Postcode/form/fieldset',
                "additionalClasses" => "postcode_fieldset",
                "loaderImageHref" => $this->getViewFileUrl('images/loader-1.gif')
            ),
            'sortOrder' => '1000',
            'children' => $this->getFields($customScope, $addressType),
            'provider' => 'checkoutProvider',
            'addressType' => $addressType
        );
    }

    /**
     * Get billing form fields method
     *
     * @param $result
     * @return mixed
     */
    public function getBillingFormFields($result)
    {
        if (isset(
            $result['components']['checkout']['children']['paymentMethod']['children']
            ['billingAddress']['children']['billing-address-fieldset']['children']
        )) {
            $paymentForms = $result['components']['checkout']['children']['paymentMethod']['children']
            ['billingAddress']['children']['billing-address-fieldset']['children'];

            foreach ($paymentForms as $paymentMethodForm => $paymentMethodValue) {
                $paymentMethodCode = str_replace('-form', '', $paymentMethodForm);

                if (!isset($result['components']['checkout']['children']['paymentMethod']['children']
                    ['billingAddress']['children']['billing-address-fieldset']['children'][$paymentMethodCode . '-form'])) {
                    continue;
                }

                $billingFields = $result['components']['checkout']['children']['paymentMethod']['children']
                ['billingAddress']['children']['billing-address-fieldset']['children'][$paymentMethodCode . '-form']['children']['form-fields']['children'];

                $billingFields['postcode_fieldset'] = $this->getFieldArray('billingAddress' . $paymentMethodCode, 'billing');

                $result['components']['checkout']['children']['paymentMethod']['children']
                ['billingAddress']['children']['billing-address-fieldset']['children'][$paymentMethodCode . '-form']
                ['children']['form-fields']['children'] = $billingFields;
            }
        }

        return $result;
    }

    /**
     * Get fields method
     *
     * @param $customScope
     * @param $addressType
     * @return array
     */
    public function getFields($customScope, $addressType)
    {
        $postcodeFields =
            array(
                'postcode_postcode' => array(
                    'component' => 'Magento_Ui/js/form/element/abstract',
                    'config' => array(
                        "customScope" => $customScope,
                        "template" => 'ui/form/field',
                        "elementTmpl" => 'ui/form/element/input',
                    ),
                    'provider' => 'checkoutProvider',
                    'dataScope' => $customScope . '.postcode_postcode',
                    'label' => __('Postcode'),
                    'sortOrder' => '1001',
                    'validation' => array(
                        'required-entry' => true,
                        'min_text_length' => 6,
                    )
                ),
                'postcode_housenumber' => array(
                    'component' => 'Magento_Ui/js/form/element/abstract',
                    'config' => array(
                        "customScope" => $customScope,
                        "template" => 'ui/form/field',
                        "elementTmpl" => 'ui/form/element/input'
                    ),
                    'provider' => 'checkoutProvider',
                    'dataScope' => $customScope . '.postcode_housenumber',
                    'label' => __('Housenumber'),
                    'sortOrder' => '1002',
                    'validation' => array(
                        'required-entry' => true,
                    ),
                ),
                'postcode_housenumber_addition' => array(
                    'component' => 'Magento_Ui/js/form/element/select',
                    'config' => array(
                        "customScope" => $customScope,
                        "template" => 'ui/form/field',
                        "elementTmpl" => 'ui/form/element/select'
                    ),
                    'provider' => 'checkoutProvider',
                    'dataScope' => $customScope . '.postcode_housenumber_addition',
                    'label' => __('Housenumber addition'),
                    'sortOrder' => '1003',
                    'validation' => array(
                        'required-entry' => false,
                    ),
                    'options' => array(),
                    'visible' => false,
                ),
                'postcode_housenumber_addition_manual' => array(
                    'component' => 'Magento_Ui/js/form/element/abstract',
                    'config' => array(
                        "customScope" => $customScope,
                        "template" => 'ui/form/field',
                        "elementTmpl" => 'ui/form/element/input'
                    ),
                    'provider' => 'checkoutProvider',
                    'dataScope' => $customScope . '.postcode_housenumber_addition_manual',
                    'label' => __('Housenumber addition'),
                    'sortOrder' => '1004',
                    'validation' => array(
                        'required-entry' => false,
                    ),
                    'options' => array(),
                    'visible' => false,
                ),
                'postcode_disable' => array(
                    'component' => 'Magento_Ui/js/form/element/abstract',
                    'config' => array(
                        "customScope" => $customScope,
                        "template" => 'ui/form/field',
                        "elementTmpl" => 'ui/form/element/checkbox'
                    ),
                    'provider' => 'checkoutProvider',
                    'dataScope' => $customScope . '.postcode_disable',
                    'description' => __('Fill out address information manually'),
                    'sortOrder' => '1005',
                    'validation' => array(
                        'required-entry' => false,
                    ),
                    'addressType' => $addressType
                )
            );

        return $postcodeFields;
    }
}
