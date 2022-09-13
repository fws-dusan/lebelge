<?php

/**
 * Request modifier for delivery confirmation.
 *
 * @package WPDesk\UpsProShippingService\DeliveryConfirmation
 */
namespace UpsProVendor\WPDesk\UpsProShippingService\DeliveryConfirmation;

use UpsProVendor\Ups\Entity\DeliveryConfirmation;
use UpsProVendor\Ups\Entity\RateRequest;
use UpsProVendor\WPDesk\UpsProShippingService\UpsApi\UpsRateRequestModifier;
/**
 * Can modify request for delivery confirmation.
 */
class DeliveryConfirmationRequestModifier implements \UpsProVendor\WPDesk\UpsProShippingService\UpsApi\UpsRateRequestModifier
{
    /**
     * Delivery confirmation.
     *
     * @var string
     */
    private $delivery_confirmation;
    public function __construct($delivery_confirmation)
    {
        $this->delivery_confirmation = $delivery_confirmation;
    }
    /**
     * Modify rate request.
     *
     * @param RateRequest $request
     */
    public function modify_rate_request(\UpsProVendor\Ups\Entity\RateRequest $request)
    {
        $delivery_confirmation = new \UpsProVendor\Ups\Entity\DeliveryConfirmation();
        $delivery_confirmation->setDcisType((int) $this->delivery_confirmation);
        $request->getShipment()->getShipmentServiceOptions()->setDeliveryConfirmation($delivery_confirmation);
    }
}
