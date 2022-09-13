<?php

/**
 * Settings definitions.
 *
 * @package WPDesk\WooCommerceShipping\Ups
 */
namespace UpsProVendor\WPDesk\WooCommerceShipping\Ups;

use UpsProVendor\WPDesk\AbstractShipping\Settings\SettingsValues;
use UpsProVendor\WPDesk\UpsShippingService\UpsSettingsDefinition;
use UpsProVendor\WPDesk\WooCommerceShipping\CustomFields\FieldApiStatusAjax;
use UpsProVendor\WPDesk\WooCommerceShipping\ShippingMethod\RateMethod\Fallback\FallbackRateMethod;
/**
 * Can handle global and instance settings for WooCommerce shipping method.
 */
class UpsSettingsDefinitionWooCommerce extends \UpsProVendor\WPDesk\UpsShippingService\UpsSettingsDefinition
{
    private $global_method_fields = [\UpsProVendor\WPDesk\UpsShippingService\UpsSettingsDefinition::SHIPPING_METHOD_TITLE, \UpsProVendor\WPDesk\UpsShippingService\UpsSettingsDefinition::API_SETTINGS_TITLE, \UpsProVendor\WPDesk\UpsShippingService\UpsSettingsDefinition::USER_ID, \UpsProVendor\WPDesk\UpsShippingService\UpsSettingsDefinition::PASSWORD, \UpsProVendor\WPDesk\UpsShippingService\UpsSettingsDefinition::ACCESS_KEY, \UpsProVendor\WPDesk\UpsShippingService\UpsSettingsDefinition::ACCOUNT_NUMBER, \UpsProVendor\WPDesk\UpsShippingService\UpsSettingsDefinition::TESTING, \UpsProVendor\WPDesk\UpsShippingService\UpsSettingsDefinition::ORIGIN_SETTINGS_TITLE, \UpsProVendor\WPDesk\UpsShippingService\UpsSettingsDefinition::CUSTOM_ORIGIN, \UpsProVendor\WPDesk\UpsShippingService\UpsSettingsDefinition::ORIGIN_ADDRESS, \UpsProVendor\WPDesk\UpsShippingService\UpsSettingsDefinition::ORIGIN_CITY, \UpsProVendor\WPDesk\UpsShippingService\UpsSettingsDefinition::ORIGIN_POSTCODE, \UpsProVendor\WPDesk\UpsShippingService\UpsSettingsDefinition::ORIGIN_COUNTRY, \UpsProVendor\WPDesk\UpsShippingService\UpsSettingsDefinition::ADVANCED_OPTIONS_TITLE, \UpsProVendor\WPDesk\UpsShippingService\UpsSettingsDefinition::UNITS, \UpsProVendor\WPDesk\UpsShippingService\UpsSettingsDefinition::DEBUG_MODE, \UpsProVendor\WPDesk\UpsShippingService\UpsSettingsDefinition::API_STATUS];
    /**
     * Form fields.
     *
     * @var array
     */
    private $form_fields;
    /**
     * UpsSettingsDefinitionWooCommerce constructor.
     *
     * @param array $form_fields Form fields.
     */
    public function __construct(array $form_fields)
    {
        $this->form_fields = $form_fields;
    }
    /**
     * Get form fields.
     *
     * @return array
     */
    public function get_form_fields()
    {
        return $this->filter_instance_fields($this->form_fields, \false);
    }
    /**
     * Get instance form fields.
     *
     * @return array
     */
    public function get_instance_form_fields()
    {
        return $this->filter_instance_fields($this->form_fields, \true);
    }
    /**
     * Get global method fields.
     *
     * @return array
     */
    protected function get_global_method_fields()
    {
        return $this->global_method_fields;
    }
    /**
     * Filter instance form fields.
     *
     * @param array $all_fields .
     * @param bool  $instance_fields .
     *
     * @return array
     */
    private function filter_instance_fields(array $all_fields, $instance_fields)
    {
        $fields = array();
        foreach ($all_fields as $key => $field) {
            $is_instance_field = !\in_array($key, $this->get_global_method_fields(), \true);
            if ($instance_fields && $is_instance_field || !$instance_fields && !$is_instance_field) {
                $fields[$key] = $field;
            }
        }
        return $fields;
    }
}
