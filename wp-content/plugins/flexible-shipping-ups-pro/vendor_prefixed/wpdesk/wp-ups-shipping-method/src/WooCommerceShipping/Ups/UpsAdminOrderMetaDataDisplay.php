<?php

/**
 * Meta data interpreter.
 *
 * @package WPDesk\WooCommerceShipping\Ups
 */
namespace UpsProVendor\WPDesk\WooCommerceShipping\Ups;

use UpsProVendor\WPDesk\UpsShippingService\UpsShippingService;
use UpsProVendor\WPDesk\WooCommerceShipping\OrderMetaData\AdminOrderMetaDataDisplay;
use UpsProVendor\WPDesk\WooCommerceShipping\OrderMetaData\SingleAdminOrderMetaDataInterpreterImplementation;
use UpsProVendor\WPDesk\WooCommerceShipping\ShippingBuilder\WooCommerceShippingMetaDataBuilder;
/**
 * Can interpret UPS meta data on order.
 */
class UpsAdminOrderMetaDataDisplay extends \UpsProVendor\WPDesk\WooCommerceShipping\OrderMetaData\AdminOrderMetaDataDisplay
{
    const META_FALLBACK_REASON = 'fallback_reason';
    /**
     * UpsOrderMetaDataInterpreter constructor.
     */
    public function __construct()
    {
        parent::__construct(\UpsProVendor\WPDesk\UpsShippingService\UpsShippingService::UNIQUE_ID);
        $this->add_hidden_order_item_meta_key(\UpsProVendor\WPDesk\WooCommerceShipping\Ups\UpsMetaDataBuilder::COLLECTION_POINT_ID);
        $this->add_hidden_order_item_meta_key(\UpsProVendor\WPDesk\WooCommerceShipping\Ups\UpsMetaDataBuilder::COLLECTION_POINT_ADDRESS);
        $this->add_hidden_order_item_meta_key(\UpsProVendor\WPDesk\WooCommerceShipping\ShippingBuilder\WooCommerceShippingMetaDataBuilder::COLLECTION_POINT);
        $this->add_hidden_order_item_meta_key(\UpsProVendor\WPDesk\WooCommerceShipping\ShippingBuilder\WooCommerceShippingMetaDataBuilder::SERVICE_TYPE);
    }
    /**
     * Init interpreters.
     */
    public function init_interpreters()
    {
        $this->add_interpreter(new \UpsProVendor\WPDesk\WooCommerceShipping\OrderMetaData\SingleAdminOrderMetaDataInterpreterImplementation(\UpsProVendor\WPDesk\WooCommerceShipping\Ups\UpsMetaDataBuilder::META_UPS_ACCESS_POINT, \__('UPS Access Point', 'flexible-shipping-ups-pro')));
        $this->add_interpreter(new \UpsProVendor\WPDesk\WooCommerceShipping\OrderMetaData\SingleAdminOrderMetaDataInterpreterImplementation(\UpsProVendor\WPDesk\WooCommerceShipping\Ups\UpsMetaDataBuilder::META_UPS_SERVICE_CODE, \__('UPS Service Code', 'flexible-shipping-ups-pro')));
        $this->add_interpreter(new \UpsProVendor\WPDesk\WooCommerceShipping\OrderMetaData\SingleAdminOrderMetaDataInterpreterImplementation(self::META_FALLBACK_REASON, \__('Fallback reason', 'flexible-shipping-ups-pro')));
        $this->add_interpreter(new \UpsProVendor\WPDesk\WooCommerceShipping\OrderMetaData\SingleAdminOrderMetaDataInterpreterImplementation(\UpsProVendor\WPDesk\WooCommerceShipping\Ups\UpsMetaDataBuilder::META_UPS_ACCESS_POINT_ADDRESS, \__('UPS Access Point Address', 'flexible-shipping-ups-pro')));
    }
}
