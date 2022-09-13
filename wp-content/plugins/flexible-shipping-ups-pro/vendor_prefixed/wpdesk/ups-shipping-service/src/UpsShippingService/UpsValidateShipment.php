<?php

/**
 * UPS implementation: Validate shipment class.
 *
 * @package WPDesk\UpsShippingService;
 */
namespace UpsProVendor\WPDesk\UpsShippingService;

use Psr\Log\LoggerInterface;
use UpsProVendor\WPDesk\AbstractShipping\Shipment\Shipment;
/**
 * Validate shipment for some cases.
 */
class UpsValidateShipment
{
    /**
     * Shipment.
     *
     * @var Shipment
     */
    private $shipment;
    /**
     * Logger.
     *
     * @var LoggerInterface
     */
    private $logger;
    /**
     * UpsValidateShipment constructor.
     *
     * @param Shipment        $shipment Shipment.
     * @param LoggerInterface $logger Logger.
     */
    public function __construct(\UpsProVendor\WPDesk\AbstractShipping\Shipment\Shipment $shipment, \Psr\Log\LoggerInterface $logger)
    {
        $this->shipment = $shipment;
        $this->logger = $logger;
    }
}
