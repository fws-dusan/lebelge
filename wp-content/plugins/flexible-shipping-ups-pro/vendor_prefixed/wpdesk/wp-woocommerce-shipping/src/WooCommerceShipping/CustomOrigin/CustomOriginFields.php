<?php

namespace UpsProVendor\WPDesk\WooCommerceShipping\CustomOrigin;

use UpsProVendor\WPDesk\WooCommerceShipping\ShopSettings;
/**
 * Can replace fake custom_origin field with custom origin fields to shipping method settings fields.
 *
 * @package WPDesk\WooCommerceShipping\CustomOrigin
 */
class CustomOriginFields
{
    const FIELD_TYPE_CUSTOM_ORIGIN = 'custom_origin';
    const CUSTOM_ORIGIN = 'custom_origin';
    const ORIGIN_ADDRESS = 'origin_address';
    const ORIGIN_CITY = 'origin_city';
    const ORIGIN_POSTCODE = 'origin_postcode';
    const ORIGIN_COUNTRY = 'origin_country';
    /**
     * Prepare country state options.
     *
     * @return array
     */
    private function prepare_country_state_options()
    {
        $country_state_options = array();
        $countries = \WC()->countries->get_countries();
        if (isset($countries)) {
            $country_state_options = $countries;
            foreach ($country_state_options as $country_code => $country) {
                $states = \WC()->countries->get_states($country_code);
                if ($states) {
                    unset($country_state_options[$country_code]);
                    foreach ($states as $state_code => $state_name) {
                        $country_state_options[$country_code . ':' . $state_code] = $country . ' &mdash; ' . $state_name;
                    }
                }
            }
        }
        return $country_state_options;
    }
    /**
     * Replace custom_origin fake field with checkbox and input fields in settings.
     *
     * @param array $settings
     *
     * @return array
     */
    public function replace_fallback_field_if_exists(array $settings)
    {
        $country_state_options = $this->prepare_country_state_options();
        $new_settings = [];
        foreach ($settings as $key => $field) {
            if ($field['type'] === self::FIELD_TYPE_CUSTOM_ORIGIN) {
                $new_settings[self::CUSTOM_ORIGIN] = ['title' => \__('Custom Origin', 'flexible-shipping-ups-pro'), 'label' => \__('Enable custom origin', 'flexible-shipping-ups-pro'), 'type' => 'checkbox', 'description' => \__('By default store address data from the WooCommerce settings are used as the origin.', 'flexible-shipping-ups-pro'), 'desc_tip' => \true, 'default' => 'no', 'class' => 'custom_origin'];
                $new_settings[self::ORIGIN_ADDRESS] = ['title' => \__('Origin Address', 'flexible-shipping-ups-pro'), 'type' => 'text', 'custom_attributes' => array('required' => 'required'), 'default' => '', 'class' => 'custom_origin_field'];
                $new_settings[self::ORIGIN_CITY] = ['title' => \__('Origin City', 'flexible-shipping-ups-pro'), 'type' => 'text', 'custom_attributes' => array('required' => 'required'), 'default' => '', 'class' => 'custom_origin_field'];
                $new_settings[self::ORIGIN_POSTCODE] = ['title' => \__('Origin Postcode', 'flexible-shipping-ups-pro'), 'type' => 'text', 'custom_attributes' => array('required' => 'required'), 'default' => '', 'class' => 'custom_origin_field'];
                $new_settings[self::ORIGIN_COUNTRY] = ['title' => \__('Origin Country/State', 'flexible-shipping-ups-pro'), 'type' => 'select', 'options' => $country_state_options, 'custom_attributes' => array('required' => 'required'), 'default' => '', 'class' => 'custom_origin_field custom_origin_country'];
            } else {
                $new_settings[$key] = $field;
            }
        }
        return $new_settings;
    }
}
