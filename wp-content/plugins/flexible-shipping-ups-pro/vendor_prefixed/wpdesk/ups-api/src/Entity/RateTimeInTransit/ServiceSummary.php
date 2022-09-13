<?php

namespace UpsProVendor\Ups\Entity\RateTimeInTransit;

use UpsProVendor\Ups\Entity\ServiceSummaryTrait;
class ServiceSummary
{
    use ServiceSummaryTrait;
    /**
     * @var
     */
    protected $estimatedArrival;
    /**
     * @param \stdClass|null $response
     */
    public function __construct(\stdClass $response = null)
    {
        $this->build($response);
        $this->setEstimatedArrival(new \UpsProVendor\Ups\Entity\RateTimeInTransit\EstimatedArrival());
        if (null !== $response) {
            if (isset($response->EstimatedArrival)) {
                $this->setEstimatedArrival(new \UpsProVendor\Ups\Entity\RateTimeInTransit\EstimatedArrival($response->EstimatedArrival));
            }
        }
    }
    /**
     * @return EstimatedArrival|null
     */
    public function getEstimatedArrival()
    {
        return $this->estimatedArrival;
    }
    /**
     * @param EstimatedArrival $estimatedArrival
     */
    public function setEstimatedArrival(\UpsProVendor\Ups\Entity\RateTimeInTransit\EstimatedArrival $estimatedArrival)
    {
        $this->estimatedArrival = $estimatedArrival;
    }
}
