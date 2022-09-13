<?php

namespace UpsProVendor\Ups\Entity;

class SubscriptionFile
{
    public $FileName;
    public $StatusType;
    public $Manifest;
    public $Origin;
    public $Exception;
    public $Delivery;
    public $Generic;
    /**
     * @param \stdClass|null $response
     */
    public function __construct(\stdClass $response = null)
    {
        $this->StatusType = new \UpsProVendor\Ups\Entity\StatusType();
        $this->Manifest = new \UpsProVendor\Ups\Entity\Manifest();
        $this->Origin = new \UpsProVendor\Ups\Entity\Origin();
        $this->Exception = new \UpsProVendor\Ups\Entity\Exception();
        $this->Delivery = new \UpsProVendor\Ups\Entity\Delivery();
        $this->Generic = new \UpsProVendor\Ups\Entity\Generic();
        if (null !== $response) {
            if (isset($response->FileName)) {
                $this->FileName = $response->FileName;
            }
            if (isset($response->StatusType)) {
                $this->StatusType = new \UpsProVendor\Ups\Entity\StatusType($response->StatusType);
            }
            if (isset($response->Manifest)) {
                $this->Manifest = new \UpsProVendor\Ups\Entity\Manifest($response->Manifest);
            }
            if (isset($response->Origin)) {
                $this->Origin = new \UpsProVendor\Ups\Entity\Origin($response->Origin);
            }
            if (isset($response->Exception)) {
                $this->Exception = new \UpsProVendor\Ups\Entity\Exception($response->Exception);
            }
            if (isset($response->Delivery)) {
                $this->Delivery = new \UpsProVendor\Ups\Entity\Delivery($response->Delivery);
            }
            if (isset($response->Generic)) {
                $this->Generic = new \UpsProVendor\Ups\Entity\Generic($response->Generic);
            }
        }
    }
}
