<?php

/**
 * Connection checker.
 *
 * @package WPDesk\UpsShippingService\UpsApi
 */
namespace UpsProVendor\WPDesk\UpsShippingService\UpsApi;

use Psr\Log\LoggerInterface;
use UpsProVendor\Ups\Entity\Address;
use UpsProVendor\Ups\SimpleAddressValidation;
use UpsProVendor\WPDesk\AbstractShipping\Settings\SettingsValues;
use UpsProVendor\WPDesk\AbstractShipping\Settings\SettingsValuesAsArray;
use UpsProVendor\WPDesk\UpsShippingService\UpsSettingsDefinition;
use UpsProVendor\WPDesk\UpsShippingService\UpsShippingService;
/**
 * Can check connection.
 */
class ConnectionChecker
{
    /**
     * Shipping service.
     *
     * @var UpsShippingService
     */
    private $shipping_service;
    /**
     * Settings.
     *
     * @var SettingsValuesAsArray
     */
    private $settings;
    /**
     * Logger.
     *
     * @var LoggerInterface
     */
    private $logger;
    /**
     * ConnectionChecker constructor.
     *
     * @param UpsShippingService $shipping_service .
     * @param SettingsValues     $settings .
     * @param LoggerInterface    $logger .
     */
    public function __construct(\UpsProVendor\WPDesk\UpsShippingService\UpsShippingService $shipping_service, \UpsProVendor\WPDesk\AbstractShipping\Settings\SettingsValues $settings, $logger)
    {
        $this->shipping_service = $shipping_service;
        $this->settings = $settings;
        $this->logger = $logger;
    }
    /**
     * Pings API.
     *
     * @throws \Exception .
     */
    public function check_connection()
    {
        $address = new \UpsProVendor\Ups\Entity\Address();
        $address->setStateProvinceCode('NY');
        $address->setCity('New York');
        $address->setCountryCode('US');
        $address->setPostalCode('10000');
        $av = new \UpsProVendor\Ups\SimpleAddressValidation($this->settings->get_value(\UpsProVendor\WPDesk\UpsShippingService\UpsSettingsDefinition::ACCESS_KEY), $this->settings->get_value(\UpsProVendor\WPDesk\UpsShippingService\UpsSettingsDefinition::USER_ID), $this->settings->get_value(\UpsProVendor\WPDesk\UpsShippingService\UpsSettingsDefinition::PASSWORD), $this->shipping_service->is_testing($this->settings), null, $this->logger);
        $response = $av->validate($address);
    }
}
