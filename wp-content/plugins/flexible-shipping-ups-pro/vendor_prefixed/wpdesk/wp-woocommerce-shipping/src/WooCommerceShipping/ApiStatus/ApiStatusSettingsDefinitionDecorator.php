<?php

/**
 * Decorator for api status settings field.
 *
 * @package WPDesk\WooCommerceShipping\ApiStatus
 */
namespace UpsProVendor\WPDesk\WooCommerceShipping\ApiStatus;

use UpsProVendor\WPDesk\AbstractShipping\Settings\DefinitionModifier\SettingsDefinitionModifierAfter;
use UpsProVendor\WPDesk\AbstractShipping\Settings\SettingsDefinition;
use UpsProVendor\WPDesk\WooCommerceShipping\CustomFields\ApiStatus\FieldApiStatus;
use UpsProVendor\WPDesk\WooCommerceShipping\CustomFields\ApiStatus\FieldApiStatusAjax;
/**
 * Can decorate settings for estimated delivery field.
 */
class ApiStatusSettingsDefinitionDecorator extends \UpsProVendor\WPDesk\AbstractShipping\Settings\DefinitionModifier\SettingsDefinitionModifierAfter
{
    const API_STATUS = 'api_status';
    /**
     * ApiStatusSettingsDefinitionDecorator constructor.
     *
     * @param SettingsDefinition $ups_settings_definition .
     * @param string $after_field API Status field will be added after this field.
     * @param FieldApiStatusAjax $api_status_ajax_handler .
     * @param string $service_id .
     */
    public function __construct(\UpsProVendor\WPDesk\AbstractShipping\Settings\SettingsDefinition $ups_settings_definition, $after_field, \UpsProVendor\WPDesk\WooCommerceShipping\CustomFields\ApiStatus\FieldApiStatusAjax $api_status_ajax_handler, $service_id)
    {
        parent::__construct($ups_settings_definition, $after_field, self::API_STATUS, ['title' => \__('API Status', 'flexible-shipping-ups-pro'), 'type' => 'api_status', 'class' => 'flexible_shipping_api_status', 'default' => \__('Checking...', 'flexible-shipping-ups-pro'), 'description' => \__('If there are connection problems, you should see the error message.', 'flexible-shipping-ups-pro'), 'desc_tip' => \true, \UpsProVendor\WPDesk\WooCommerceShipping\CustomFields\ApiStatus\FieldApiStatus::SECURITY_NONCE => \wp_create_nonce($api_status_ajax_handler->get_nonce_name()), \UpsProVendor\WPDesk\WooCommerceShipping\CustomFields\ApiStatus\FieldApiStatus::SHIPPING_SERVICE_ID => $service_id]);
    }
}
