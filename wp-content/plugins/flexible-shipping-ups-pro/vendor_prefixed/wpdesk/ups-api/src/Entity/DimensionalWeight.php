<?php

namespace UpsProVendor\Ups\Entity;

class DimensionalWeight
{
    /**
     * @var UnitOfMeasurement
     */
    public $UnitOfMeasurement;
    public $Weight;
    public function __construct()
    {
        $this->UnitOfMeasurement = new \UpsProVendor\Ups\Entity\UnitOfMeasurement();
    }
}
