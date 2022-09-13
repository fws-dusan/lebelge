<?php

/**
 * Settings definition.
 *
 * @package WPDesk\UpsProShippingService
 */
namespace UpsProVendor\WPDesk\UpsProShippingService;

use UpsProVendor\WPDesk\AbstractShipping\Exception\SettingsFieldNotExistsException;
use UpsProVendor\WPDesk\AbstractShipping\Settings\SettingsDecorators\BlackoutLeadDaysSettingsDefinitionDecoratorFactory;
use UpsProVendor\WPDesk\AbstractShipping\Settings\SettingsDefinition;
use UpsProVendor\WPDesk\AbstractShipping\Settings\SettingsValues;
use UpsProVendor\WPDesk\UpsProShippingService\CollectionPointFlatRate\CollectionPointFlatRateSettingsDefinitionDecorator;
use UpsProVendor\WPDesk\UpsProShippingService\CutoffTime\CutoffTimeSettingsDefinitionDecorator;
use UpsProVendor\WPDesk\UpsProShippingService\DatesAndTimes\DatesAndTimesSettingsDefinitionDecorator;
use UpsProVendor\WPDesk\UpsProShippingService\DeliveryConfirmation\DeliveryConfirmationSettingsDefinitionDecorator;
use UpsProVendor\WPDesk\UpsProShippingService\DestinationAddressType\DestinationAddressTypeSettingsDefinitionDecorator;
use UpsProVendor\WPDesk\UpsProShippingService\DestinationAddressType\EstimatedDeliverySettingsDefinitionDecorator;
use UpsProVendor\WPDesk\UpsProShippingService\LeadTime\LeadTimeSettingsDefinitionDecorator;
use UpsProVendor\WPDesk\UpsProShippingService\MaximumTransitTime\MaximumTransitTimeSettingsDefinitionDecorator;
use UpsProVendor\WPDesk\UpsProShippingService\RateAdjustmentsTitle\RateAdjustmentsTitleSettingsDefinitionDecorator;
use UpsProVendor\WPDesk\UpsProShippingService\PickupType\HandlingFeesSettingsDefinitionDecorator;
use UpsProVendor\WPDesk\UpsShippingService\UpsSettingsDefinition;
/**
 * Settings definitions.
 */
class UpsProSettingsDefinition extends \UpsProVendor\WPDesk\AbstractShipping\Settings\SettingsDefinition
{
    /**
     * UPS settings definition.
     *
     * @var UpsSettingsDefinition
     */
    private $ups_settings_definition;
    /**
     * UpsProSettingsDefinition constructor.
     *
     * @param UpsSettingsDefinition $ups_settings_definition UPS settings definition.
     */
    public function __construct(\UpsProVendor\WPDesk\UpsShippingService\UpsSettingsDefinition $ups_settings_definition)
    {
        $ups_settings_definition = new \UpsProVendor\WPDesk\UpsProShippingService\RateAdjustmentsTitle\RateAdjustmentsTitleSettingsDefinitionDecorator($ups_settings_definition);
        $ups_settings_definition = new \UpsProVendor\WPDesk\UpsProShippingService\DestinationAddressType\DestinationAddressTypeSettingsDefinitionDecorator($ups_settings_definition);
        $ups_settings_definition = new \UpsProVendor\WPDesk\UpsProShippingService\PickupType\HandlingFeesSettingsDefinitionDecorator($ups_settings_definition);
        $ups_settings_definition = new \UpsProVendor\WPDesk\UpsProShippingService\DatesAndTimes\DatesAndTimesSettingsDefinitionDecorator($ups_settings_definition);
        $ups_settings_definition = new \UpsProVendor\WPDesk\UpsProShippingService\DestinationAddressType\EstimatedDeliverySettingsDefinitionDecorator($ups_settings_definition);
        $ups_settings_definition = new \UpsProVendor\WPDesk\UpsProShippingService\MaximumTransitTime\MaximumTransitTimeSettingsDefinitionDecorator($ups_settings_definition);
        $ups_settings_definition = new \UpsProVendor\WPDesk\UpsProShippingService\LeadTime\LeadTimeSettingsDefinitionDecorator($ups_settings_definition);
        $ups_settings_definition = new \UpsProVendor\WPDesk\UpsProShippingService\CutoffTime\CutoffTimeSettingsDefinitionDecorator($ups_settings_definition);
        $ups_settings_definition = (new \UpsProVendor\WPDesk\AbstractShipping\Settings\SettingsDecorators\BlackoutLeadDaysSettingsDefinitionDecoratorFactory())->create_decorator($ups_settings_definition, \UpsProVendor\WPDesk\UpsProShippingService\CutoffTime\CutoffTimeSettingsDefinitionDecorator::OPTION_CUTOFF_TIME, \false);
        $ups_settings_definition = new \UpsProVendor\WPDesk\UpsProShippingService\CollectionPointFlatRate\CollectionPointFlatRateSettingsDefinitionDecorator($ups_settings_definition);
        $ups_settings_definition = new \UpsProVendor\WPDesk\UpsProShippingService\DeliveryConfirmation\DeliveryConfirmationSettingsDefinitionDecorator($ups_settings_definition);
        $this->ups_settings_definition = $ups_settings_definition;
    }
    /**
     * Get form fields.
     *
     * @return array
     *
     * @throws SettingsFieldNotExistsException .
     */
    public function get_form_fields()
    {
        return $this->ups_settings_definition->get_form_fields();
    }
    /**
     * Validate settings.
     *
     * @param SettingsValues $settings Settings.
     *
     * @return bool
     */
    public function validate_settings(\UpsProVendor\WPDesk\AbstractShipping\Settings\SettingsValues $settings)
    {
        return $this->ups_settings_definition->validate_settings($settings);
    }
}
