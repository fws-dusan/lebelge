<?php

namespace UpsProVendor\Ups\Entity;

class LabelRecoveryResponse
{
    public $ShipmentIdentificationNumber;
    public $LabelResults;
    public $TrackingCandidate;
    /**
     * @param \stdClass|null $response
     */
    public function __construct(\stdClass $response = null)
    {
        $this->LabelResults = new \UpsProVendor\Ups\Entity\LabelResults();
        if (null !== $response) {
            if (isset($response->ShipmentIdentificationNumber)) {
                $this->ShipmentIdentificationNumber = $response->ShipmentIdentificationNumber;
            }
            if (isset($response->LabelResults)) {
                $this->LabelResults = new \UpsProVendor\Ups\Entity\LabelResults($response->LabelResults);
            }
            if (isset($response->TrackingCandidate)) {
                $this->TrackingCandidate = new \UpsProVendor\Ups\Entity\TrackingCandidate($response->TrackingCandidate);
            }
        }
    }
}
