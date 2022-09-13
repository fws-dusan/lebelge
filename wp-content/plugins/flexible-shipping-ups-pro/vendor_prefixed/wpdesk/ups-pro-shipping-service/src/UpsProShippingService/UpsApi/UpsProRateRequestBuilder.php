<?php

/**
 * UPS API: Build request.
 *
 * @package WPDesk\UpsProShippingService\UpsApi
 */
namespace UpsProVendor\WPDesk\UpsProShippingService\UpsApi;

use UpsProVendor\WPDesk\AbstractShipping\Exception\UnitConversionException;
use UpsProVendor\WPDesk\AbstractShipping\Settings\SettingsValues;
use UpsProVendor\WPDesk\AbstractShipping\Shipment\Shipment;
use UpsProVendor\WPDesk\AbstractShipping\Settings\SettingsDecorators\BlackoutLeadDaysSettingsDefinitionDecoratorFactory;
use UpsProVendor\WPDesk\AbstractShipping\Settings\BlackoutLeadDays;
use UpsProVendor\WPDesk\UpsProShippingService\CutoffTime\CutoffTimeRequestModifier;
use UpsProVendor\WPDesk\UpsProShippingService\CutoffTime\CutoffTimeSettingsDefinitionDecorator;
use UpsProVendor\WPDesk\UpsProShippingService\DeliveryConfirmation\DeliveryConfirmationRequestModifier;
use UpsProVendor\WPDesk\UpsProShippingService\DeliveryConfirmation\DeliveryConfirmationSettingsDefinitionDecorator;
use UpsProVendor\WPDesk\UpsProShippingService\DestinationAddressType\DestinationAddressTypeRequestModifier;
use UpsProVendor\WPDesk\UpsProShippingService\DestinationAddressType\DestinationAddressTypeSettingsDefinitionDecorator;
use UpsProVendor\WPDesk\UpsProShippingService\DestinationAddressType\EstimatedDeliverySettingsDefinitionDecorator;
use UpsProVendor\WPDesk\UpsProShippingService\EstimatedDelivery\EstimatedDeliveryRequestModifier;
use UpsProVendor\WPDesk\UpsProShippingService\LeadTime\LeadTimeRequestModifier;
use UpsProVendor\WPDesk\UpsProShippingService\LeadTime\LeadTimeSettingsDefinitionDecorator;
use UpsProVendor\WPDesk\UpsShippingService\UpsApi\UpsRateRequestBuilder;
use UpsProVendor\WPDesk\WooCommerceShipping\ShopSettings;
/**
 * Build request for UPS rate
 */
class UpsProRateRequestBuilder extends \UpsProVendor\WPDesk\UpsShippingService\UpsApi\UpsRateRequestBuilder
{
    /**
     * @var UpsRateRequestModifier[]
     */
    private $rate_request_modifiers = array();
    /**
     * Settings values.
     *
     * @var SettingsValues
     */
    private $settings;
    /**
     * WooCommerce shipment.
     *
     * @var Shipment
     */
    private $shipment;
    /**
     * UpsRateRequestBuilder constructor.
     *
     * @param SettingsValues $settings Settings.
     * @param Shipment       $shipment Shipment.
     * @param ShopSettings   $shop_settings Helper.
     */
    public function __construct(\UpsProVendor\WPDesk\AbstractShipping\Settings\SettingsValues $settings, \UpsProVendor\WPDesk\AbstractShipping\Shipment\Shipment $shipment, \UpsProVendor\WPDesk\WooCommerceShipping\ShopSettings $shop_settings)
    {
        parent::__construct($settings, $shipment, $shop_settings);
        $this->settings = $settings;
        $this->shipment = $shipment;
        $this->init_modifiers();
    }
    /**
     * Init modifiers.
     */
    private function init_modifiers()
    {
        $destination_address_type = $this->settings->get_value(\UpsProVendor\WPDesk\UpsProShippingService\DestinationAddressType\DestinationAddressTypeSettingsDefinitionDecorator::OPTION_DESTINATION_ADDRESS_TYPE, \UpsProVendor\WPDesk\UpsProShippingService\DestinationAddressType\DestinationAddressTypeSettingsDefinitionDecorator::DESTINATION_ADDRESS_TYPE_COMMERCIAL);
        $delivery_dates = $this->settings->get_value(\UpsProVendor\WPDesk\UpsProShippingService\DestinationAddressType\EstimatedDeliverySettingsDefinitionDecorator::OPTION_DELIVERY_DATES, \UpsProVendor\WPDesk\UpsProShippingService\DestinationAddressType\EstimatedDeliverySettingsDefinitionDecorator::OPTION_NONE);
        $lead_time = (int) $this->settings->get_value(\UpsProVendor\WPDesk\UpsProShippingService\LeadTime\LeadTimeSettingsDefinitionDecorator::OPTION_LEAD_TIME, '0');
        $cutoff_time = $this->settings->get_value(\UpsProVendor\WPDesk\UpsProShippingService\CutoffTime\CutoffTimeSettingsDefinitionDecorator::OPTION_CUTOFF_TIME, '');
        $delivery_confirmation = $this->settings->get_value(\UpsProVendor\WPDesk\UpsProShippingService\DeliveryConfirmation\DeliveryConfirmationSettingsDefinitionDecorator::OPTION_DELIVERY_CONFIRMATION, \UpsProVendor\WPDesk\UpsProShippingService\DeliveryConfirmation\DeliveryConfirmationSettingsDefinitionDecorator::DELIVERY_CONFIRMATION_NONE);
        $this->rate_request_modifiers[] = new \UpsProVendor\WPDesk\UpsProShippingService\DestinationAddressType\DestinationAddressTypeRequestModifier($destination_address_type);
        if ($delivery_confirmation !== \UpsProVendor\WPDesk\UpsProShippingService\DeliveryConfirmation\DeliveryConfirmationSettingsDefinitionDecorator::DELIVERY_CONFIRMATION_NONE) {
            $this->rate_request_modifiers[] = new \UpsProVendor\WPDesk\UpsProShippingService\DeliveryConfirmation\DeliveryConfirmationRequestModifier($delivery_confirmation);
        }
        $this->rate_request_modifiers[] = new \UpsProVendor\WPDesk\UpsProShippingService\EstimatedDelivery\EstimatedDeliveryRequestModifier($delivery_dates, $this->shipment);
        if (\UpsProVendor\WPDesk\UpsProShippingService\DestinationAddressType\EstimatedDeliverySettingsDefinitionDecorator::OPTION_NONE !== $delivery_dates) {
            $blackout_lead_days_settings = $this->settings->get_value(\UpsProVendor\WPDesk\AbstractShipping\Settings\SettingsDecorators\BlackoutLeadDaysSettingsDefinitionDecoratorFactory::OPTION_ID, array());
            $blackout_lead_days = new \UpsProVendor\WPDesk\AbstractShipping\Settings\BlackoutLeadDays(\is_array($blackout_lead_days_settings) ? $blackout_lead_days_settings : array(), $lead_time);
            $this->rate_request_modifiers[] = new \UpsProVendor\WPDesk\UpsProShippingService\LeadTime\LeadTimeRequestModifier($blackout_lead_days);
            $this->rate_request_modifiers[] = new \UpsProVendor\WPDesk\UpsProShippingService\CutoffTime\CutoffTimeRequestModifier($lead_time, $cutoff_time, $blackout_lead_days);
        }
    }
    /**
     * Build request.
     *
     * @throws UnitConversionException Weight exception.
     */
    public function build_request()
    {
        parent::build_request();
        $request = $this->get_build_request();
        foreach ($this->rate_request_modifiers as $modifier) {
            $modifier->modify_rate_request($request);
        }
    }
}
