<?php

namespace UpsProVendor\Ups\Entity;

class Delivery
{
    public $PackageReferenceNumber;
    public $ShipmentReferenceNumber;
    public $TrackingNumber;
    public $ShipperNumber;
    public $Date;
    public $Time;
    public $DriverRelease;
    public $ActivityLocation;
    public $DeliveryLocation;
    public $COD;
    public $BillToAccount;
    public function __construct($response = null)
    {
        $this->ShipmentReferenceNumber = new \UpsProVendor\Ups\Entity\ShipmentReferenceNumber();
        $this->PackageReferenceNumber = new \UpsProVendor\Ups\Entity\PackageReferenceNumber();
        $this->ActivityLocation = new \UpsProVendor\Ups\Entity\ActivityLocation();
        $this->DeliveryLocation = new \UpsProVendor\Ups\Entity\DeliveryLocation();
        $this->COD = new \UpsProVendor\Ups\Entity\COD();
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
            if (isset($response->TrackingNumber)) {
                $this->TrackingNumber = $response->TrackingNumber;
            }
            if (isset($response->ShipperNumber)) {
                $this->ShipperNumber = $response->ShipperNumber;
            }
            if (isset($response->Date)) {
                $this->Date = $response->Date;
            }
            if (isset($response->Time)) {
                $this->Time = $response->Time;
            }
            if (isset($response->DriverRelease)) {
                $this->DriverRelease = $response->DriverRelease;
            }
            if (isset($response->ActivityLocation)) {
                $this->ActivityLocation = new \UpsProVendor\Ups\Entity\ActivityLocation($response->ActivityLocation);
            }
            if (isset($response->DeliveryLocation)) {
                $this->DeliveryLocation = new \UpsProVendor\Ups\Entity\DeliveryLocation($response->DeliveryLocation);
            }
            if (isset($response->COD)) {
                $this->COD = new \UpsProVendor\Ups\Entity\COD($response->COD);
            }
            if (isset($response->BillToAccount)) {
                $this->BillToAccount = new \UpsProVendor\Ups\Entity\BillToAccount($response->BillToAccount);
            }
        }
    }
}
