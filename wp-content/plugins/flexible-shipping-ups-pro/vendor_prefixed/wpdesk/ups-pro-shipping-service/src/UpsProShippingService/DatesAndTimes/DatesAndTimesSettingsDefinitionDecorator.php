<?php

/**
 * Decorator for dates and times section.
 *
 * @package WPDesk\UpsProShippingService\DatesAndTimes
 */
namespace UpsProVendor\WPDesk\UpsProShippingService\DatesAndTimes;

use UpsProVendor\WPDesk\AbstractShipping\Settings\DefinitionModifier\SettingsDefinitionModifierAfter;
use UpsProVendor\WPDesk\AbstractShipping\Settings\SettingsDefinition;
use UpsProVendor\WPDesk\UpsProShippingService\PickupType\HandlingFeesSettingsDefinitionDecorator;
/**
 * Can decorate settings by adding handling fees field.
 */
class DatesAndTimesSettingsDefinitionDecorator extends \UpsProVendor\WPDesk\AbstractShipping\Settings\DefinitionModifier\SettingsDefinitionModifierAfter
{
    const DATES_AND_TIMES_TITLE = 'dates_and_times_title';
    public function __construct(\UpsProVendor\WPDesk\AbstractShipping\Settings\SettingsDefinition $ups_settings_definition)
    {
        parent::__construct($ups_settings_definition, \UpsProVendor\WPDesk\UpsProShippingService\PickupType\HandlingFeesSettingsDefinitionDecorator::HANDLING_FEES, self::DATES_AND_TIMES_TITLE, array('title' => \__('Dates & Time', 'flexible-shipping-ups-pro'), 'description' => \__('Manage services\' dates information.', 'flexible-shipping-ups-pro'), 'type' => 'title'));
    }
}
