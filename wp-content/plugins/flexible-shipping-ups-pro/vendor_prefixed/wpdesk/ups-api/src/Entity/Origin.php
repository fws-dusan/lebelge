<?php

namespace UpsProVendor\Ups\Entity;

class Origin
{
    public $PackageReferenceNumber;
    public $ShipmentReferenceNumber;
    public $ShipperNumber;
    public $TrackingNumber;
    public $Date;
    public $Time;
    public $ActivityLocation;
    public $BillToAccount;
    public $ScheduledDeliveryDate;
    public $ScheduledDeliveryTime;
    public function __construct($response = null)
    {
        $this->PackageReferenceNumber = new \UpsProVendor\Ups\Entity\PackageReferenceNumber();
        $this->ShipmentReferenceNumber = new \UpsProVendor\Ups\Entity\ShipmentReferenceNumber();
        $this->ActivityLocation = new \UpsProVendor\Ups\Entity\ActivityLocation();
        $this->BillToAccount = new \UpsProVendor\Ups\Entity\BillToAccount();
        if (null !== $response) {
            if (isset($response->PackageReferenceNumber)) {
                if (\is_array($response->PackageReferenceNumber)) {
                    foreach ($response->PackageReferenceNumber as $PackageReferenceNumber) {
                        $this->PackageReferenceNumber[] = new \UpsProVendor\Ups\Entity\PackageReferenceNumber($PackageReferenceNumber);
                    }
                } else {
                    $this->PackageReferenceNumber[] = new \UpsProVendor\Ups\Entity\PackageReferenceNumber($response->PackageReferenceNumber);
                }
            }
            if (isset($response->ShipmentReferenceNumber)) {
                if (\is_array($response->ShipmentReferenceNumber)) {
                    foreach ($response->ShipmentReferenceNumber as $ShipmentReferenceNumber) {
                        $this->ShipmentReferenceNumber[] = new \UpsProVendor\Ups\Entity\ShipmentReferenceNumber($ShipmentReferenceNumber);
                    }
                } else {
                    $this->ShipmentReferenceNumber[] = new \UpsProVendor\Ups\Entity\ShipmentReferenceNumber($response->ShipmentReferenceNumber);
                }
            }
            if (isset($response->ShipperNumber)) {
                $this->ShipperNumber = $response->ShipperNumber;
            }
            if (isset($response->TrackingNumber)) {
                $this->TrackingNumber = $response->TrackingNumber;
            }
            if (isset($response->Date)) {
                $this->Date = $response->Date;
            }
            if (isset($response->Time)) {
                $this->Time = $response->Time;
            }
            if (isset($response->ActivityLocation)) {
                $this->ActivityLocation = new \UpsProVendor\Ups\Entity\ActivityLocation($response->ActivityLocation);
            }
            if (isset($response->BillToAccount)) {
                $this->BillToAccount = new \UpsProVendor\Ups\Entity\BillToAccount($response->BillToAccount);
            }
            if (isset($response->ScheduledDeliveryDate)) {
                $this->ScheduledDeliveryDate = $response->ScheduledDeliveryDate;
            }
            if (isset($response->ScheduledDeliveryTime)) {
                $this->ScheduledDeliveryTime = $response->ScheduledDeliveryTime;
            }
        }
    }
}
