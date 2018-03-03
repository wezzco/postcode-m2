<?php

namespace Wezz\Postcode\Block\Checkout;

use Magento\Framework\View\Element\AbstractBlock;
use Magento\Checkout\Block\Checkout\LayoutProcessorInterface;

/**
 * Class LayoutProcessor
 * @package Wezz\Postcode\Block\Checkout
 */
class LayoutProcessor extends AbstractBlock implements LayoutProcessorInterface
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
            ['steps']
            ['children']
            ['shipping-step']
            ['children']
            ['shippingAddress']
            ['children']
            ['shipping-address-fieldset']
        )
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

        $shippingFields = $result['components']['checkout']['children']['steps']['children']
            ['shipping-step']['children']['shippingAddress']['children']
            ['shipping-address-fieldset']['children'];

        $shippingFields['postcode_fieldset'] = $this->getFieldArray('shippingAddress', 'shipping');

        $shippingFields = $this->changeFieldPosition($shippingFields);

        $result['components']['checkout']['children']['steps']['children']
            ['shipping-step']['children']['shippingAddress']['children']
            ['shipping-address-fieldset']['children'] = $shippingFields;



        $result = $this->getBillingFormFields($result);

        return $result;
    }

    /**
     * Method to change address field positions
     *
     * @param $addressFields
     * @return mixed
     */
    public function changeFieldPosition($addressFields)
    {

        if (isset($addressFields['street'])) {
            $addressFields['street']['sortOrder'] = '910';
        }

        if (isset($addressFields['postcode'])) {
            $addressFields['postcode']['sortOrder'] = '930';
        }

        if (isset($addressFields['city'])) {
            $addressFields['city']['sortOrder'] = '920';
        }

        if (isset($addressFields['region'])) {
            $addressFields['region']['sortOrder'] = '940';
        }

        if (isset($addressFields['region_id'])) {
            $addressFields['region_id']['sortOrder'] = '945';
        }

        return $addressFields;
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
        return [
            'component' => 'Wezz_Postcode/js/view/postcode',
            'type' => 'group',
            'config' => [
                "customScope" => $customScope,
                "template" => 'Wezz_Postcode/form/fieldset',
                "additionalClasses" => "postcode_fieldset",
                "loaderImageHref" => $this->getViewFileUrl('images/loader-1.gif')
            ],
            'sortOrder' => '850',
            'children' => $this->getFields($customScope, $addressType),
            'provider' => 'checkoutProvider',
            'addressType' => $addressType
        ];
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
            $result['components']['checkout']['children']['steps']['children']
            ['billing-step']['children']['payment']['children']['payments-list']
        )) {
            $paymentForms = $result['components']['checkout']['children']['steps']['children']
            ['billing-step']['children']['payment']['children']
            ['payments-list']['children'];

            foreach ($paymentForms as $paymentMethodForm => $paymentMethodValue) {
                $paymentMethodCode = str_replace('-form', '', $paymentMethodForm);

                if (!isset($result['components']['checkout']['children']['steps']['children']['billing-step']
                    ['children']['payment']['children']['payments-list']['children'][$paymentMethodCode . '-form'])) {
                    continue;
                }

                $billingFields = $result['components']['checkout']['children']['steps']['children']
                ['billing-step']['children']['payment']['children']
                ['payments-list']['children'][$paymentMethodCode . '-form']['children']['form-fields']['children'];

                $billingFields['postcode_fieldset'] = $this->getFieldArray('billingAddress' . $paymentMethodCode, 'billing');

                $billingFields = $this->changeFieldPosition($billingFields);


                $result['components']['checkout']['children']['steps']['children']['billing-step']
                ['children']['payment']['children']['payments-list']['children'][$paymentMethodCode . '-form']
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
            [
                'postcode_postcode' => [
                    'component' => 'Magento_Ui/js/form/element/abstract',
                    'config' => [
                        "customScope" => $customScope,
                        "template" => 'ui/form/field',
                        "elementTmpl" => 'ui/form/element/input',
                    ],
                    'provider' => 'checkoutProvider',
                    'dataScope' => $customScope . '.postcode_postcode',
                    'label' => __('Postcode'),
                    'sortOrder' => '800',
                    'validation' => [
                        'required-entry' => true,
                        'min_text_length' => 6,
                    ]
                ],
                'postcode_housenumber' => [
                    'component' => 'Magento_Ui/js/form/element/abstract',
                    'config' => [
                        "customScope" => $customScope,
                        "template" => 'ui/form/field',
                        "elementTmpl" => 'ui/form/element/input'
                    ],
                    'provider' => 'checkoutProvider',
                    'dataScope' => $customScope . '.postcode_housenumber',
                    'label' => __('Housenumber'),
                    'sortOrder' => '801',
                    'validation' => [
                        'required-entry' => true,
                    ],
                ],
                'postcode_housenumber_addition' => [
                    'component' => 'Magento_Ui/js/form/element/select',
                    'config' => [
                        "customScope" => $customScope,
                        "template" => 'ui/form/field',
                        "elementTmpl" => 'ui/form/element/select'
                    ],
                    'provider' => 'checkoutProvider',
                    'dataScope' => $customScope . '.postcode_housenumber_addition',
                    'label' => __('Housenumber addition'),
                    'sortOrder' => '802',
                    'validation' => [
                        'required-entry' => false,
                    ],
                    'options' => [],
                    'visible' => false,
                ],
                'postcode_housenumber_addition_manual' => [
                    'component' => 'Magento_Ui/js/form/element/abstract',
                    'config' => [
                        "customScope" => $customScope,
                        "template" => 'ui/form/field',
                        "elementTmpl" => 'ui/form/element/input'
                    ],
                    'provider' => 'checkoutProvider',
                    'dataScope' => $customScope . '.postcode_housenumber_addition_manual',
                    'label' => __('Housenumber addition'),
                    'sortOrder' => '803',
                    'validation' => [
                        'required-entry' => false,
                    ],
                    'options' => [],
                    'visible' => false,
                ],
                'postcode_disable' => [
                    'component' => 'Magento_Ui/js/form/element/abstract',
                    'config' => [
                        "customScope" => $customScope,
                        "template" => 'ui/form/field',
                        "elementTmpl" => 'ui/form/element/checkbox'
                    ],
                    'provider' => 'checkoutProvider',
                    'dataScope' => $customScope . '.postcode_disable',
                    'description' => __('Fill out address information manually'),
                    'sortOrder' => '804',
                    'validation' => [
                        'required-entry' => false,
                    ],
                    'addressType' => $addressType
                ]
            ];

        return $postcodeFields;
    }
}
