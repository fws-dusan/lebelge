<?php

namespace UpsProVendor\Ups\Entity;

class BillingWeight
{
    /**
     * @var UnitOfMeasurement
     */
    public $UnitOfMeasurement;
    public $Weight;
    /**
     * @param \stdClass|null $response
     */
    public function __construct(\stdClass $response = null)
    {
        $this->UnitOfMeasurement = new \UpsProVendor\Ups\Entity\UnitOfMeasurement();
        if (null !== $response) {
            if (isset($response->UnitOfMeasurement)) {
                $this->UnitOfMeasurement = new \UpsProVendor\Ups\Entity\UnitOfMeasurement($response->UnitOfMeasurement);
            }
            if (isset($response->Weight)) {
                $this->Weight = $response->Weight;
            }
        }
    }
}
