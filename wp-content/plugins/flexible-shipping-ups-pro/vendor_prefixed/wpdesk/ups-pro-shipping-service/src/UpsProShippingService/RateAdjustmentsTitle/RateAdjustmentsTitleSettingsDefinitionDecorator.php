<?php

/**
 * Decorator for rate adjustments title field.
 *
 * @package WPDesk\UpsProShippingService\PickupType
 */
namespace UpsProVendor\WPDesk\UpsProShippingService\RateAdjustmentsTitle;

use UpsProVendor\WPDesk\AbstractShipping\Settings\DefinitionModifier\SettingsDefinitionModifierAfter;
use UpsProVendor\WPDesk\AbstractShipping\Settings\SettingsDefinition;
use UpsProVendor\WPDesk\UpsShippingService\UpsSettingsDefinition;
/**
 * Can decorate settings by adding pickup type field.
 */
class RateAdjustmentsTitleSettingsDefinitionDecorator extends \UpsProVendor\WPDesk\AbstractShipping\Settings\DefinitionModifier\SettingsDefinitionModifierAfter
{
    const RATE_ADJUSTMENTS_TITLE = 'rate_adjustments_title';
    public function __construct(\UpsProVendor\WPDesk\AbstractShipping\Settings\SettingsDefinition $ups_settings_definition)
    {
        parent::__construct($ups_settings_definition, \UpsProVendor\WPDesk\UpsShippingService\UpsSettingsDefinition::SERVICES, self::RATE_ADJUSTMENTS_TITLE, $this->get_field_settings());
    }
    /**
     * Get field settings.
     *
     * @return array .
     */
    private function get_field_settings()
    {
        return array('title' => \__('Rates Adjustments', 'flexible-shipping-ups-pro'), 'description' => \sprintf(\__('Adjust these settings to get more accurate rates. Read %swhat affects the UPS rates in UPS WooCommerce plugin →%s', 'flexible-shipping-ups-pro'), \sprintf('<a href="%s" target="_blank">', \__('https://wpde.sk/ups-pro-rates-eng/', 'flexible-shipping-ups-pro')), '</a>'), 'type' => 'title');
    }
    /**
     * Replaces settings field from free version.
     *
     * @return array .
     * @throws \WPDesk\AbstractShipping\Exception\SettingsFieldNotExistsException
     */
    public function get_form_fields()
    {
        $form_fields = parent::get_form_fields();
        $form_fields[self::RATE_ADJUSTMENTS_TITLE] = $this->get_field_settings();
        return $form_fields;
    }
}
