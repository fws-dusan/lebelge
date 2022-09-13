<?php

namespace UpsProVendor\Ups\Entity;

class NetSummaryCharges
{
    /**
     * @var Charges
     */
    public $GrandTotal;
    /**
     * @var Charges|null
     */
    public $TotalChargesWithTaxes;
    /**
     * @param \stdClass|null $response
     */
    public function __construct(\stdClass $response = null)
    {
        $this->GrandTotal = new \UpsProVendor\Ups\Entity\Charges();
        if (null !== $response) {
            if (isset($response->GrandTotal)) {
                $this->GrandTotal = new \UpsProVendor\Ups\Entity\Charges($response->GrandTotal);
            }
            if (isset($response->TotalChargesWithTaxes)) {
                $this->TotalChargesWithTaxes = new \UpsProVendor\Ups\Entity\Charges($response->TotalChargesWithTaxes);
            }
        }
    }
}
