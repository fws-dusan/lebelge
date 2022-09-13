<?php

/**
 * Capability: CanRateToCollectionPoint class
 *
 * @package WPDesk\AbstractShipping\ShippingServiceCapability
 */
namespace UpsProVendor\WPDesk\AbstractShipping\ShippingServiceCapability;

use UpsProVendor\WPDesk\AbstractShipping\CollectionPoints\CollectionPoint;
use UpsProVendor\WPDesk\AbstractShipping\Rate\ShipmentRating;
use UpsProVendor\WPDesk\AbstractShipping\Settings\SettingsValues;
use UpsProVendor\WPDesk\AbstractShipping\Shipment\Shipment;
/**
 * Interface for rate shipment to collection point
 */
interface CanRateToCollectionPoint
{
    /**
     * Rate shipment to collection point.
     *
     * @param SettingsValues  $settings Settings.
     * @param Shipment        $shipment Shipment.
     * @param CollectionPoint $collection_point Collection point.
     *
     * @return ShipmentRating
     */
    public function rate_shipment_to_collection_point(\UpsProVendor\WPDesk\AbstractShipping\Settings\SettingsValues $settings, \UpsProVendor\WPDesk\AbstractShipping\Shipment\Shipment $shipment, \UpsProVendor\WPDesk\AbstractShipping\CollectionPoints\CollectionPoint $collection_point);
    /**
     * Is rate to collection point enabled?
     *
     * @param SettingsValues $settings
     *
     * @return mixed
     */
    public function is_rate_to_collection_point_enabled(\UpsProVendor\WPDesk\AbstractShipping\Settings\SettingsValues $settings);
}
