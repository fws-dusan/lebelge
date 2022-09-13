<?php

namespace UpsProVendor\Ups\Entity;

class LabelRecoveryRequest
{
    public $LabelSpecification;
    public $Translate;
    public $LabelDelivery;
    public $TrackingNumber;
    public $ReferenceNumber;
    public $ShipperNumber;
    public function __construct()
    {
        $this->LabelSpecification = new \UpsProVendor\Ups\Entity\LabelSpecification();
        $this->Translate = new \UpsProVendor\Ups\Entity\Translate();
        $this->LabelDelivery = new \UpsProVendor\Ups\Entity\LabelDelivery();
        $this->ReferenceNumber = new \UpsProVendor\Ups\Entity\ReferenceNumber();
    }
}
