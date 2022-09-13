<?php

namespace UpsProVendor\Ups;

use DOMDocument;
use DOMElement;
use Exception;
use SimpleXMLElement;
use UpsProVendor\Ups\Entity\RateRequest;
use UpsProVendor\Ups\Entity\RateResponse;
use UpsProVendor\Ups\Entity\Shipment;
/**
 * RateTimeInTransit API Wrapper.
 */
class RateTimeInTransit extends \UpsProVendor\Ups\Rate
{
    /**
     * @param $rateRequest
     *
     * @throws Exception
     *
     * @return RateResponse
     */
    public function getRateTimeInTransit($rateRequest)
    {
        if ($rateRequest instanceof \UpsProVendor\Ups\Entity\Shipment) {
            $shipment = $rateRequest;
            $rateRequest = new \UpsProVendor\Ups\Entity\RateRequest();
            $rateRequest->setShipment($shipment);
        }
        $this->requestOption = 'Ratetimeintransit';
        return $this->sendRequest($rateRequest);
    }
    /**
     * @param $rateRequest
     *
     * @throws Exception
     *
     * @return RateResponse
     */
    public function shopRatesTimeInTransit($rateRequest)
    {
        if ($rateRequest instanceof \UpsProVendor\Ups\Entity\Shipment) {
            $shipment = $rateRequest;
            $rateRequest = new \UpsProVendor\Ups\Entity\RateRequest();
            $rateRequest->setShipment($shipment);
        }
        $this->requestOption = 'Shoptimeintransit';
        return $this->sendRequest($rateRequest);
    }
}
