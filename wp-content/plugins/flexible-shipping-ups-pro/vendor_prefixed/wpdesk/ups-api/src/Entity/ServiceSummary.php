<?php

namespace UpsProVendor\Ups\Entity;

class ServiceSummary
{
    use ServiceSummaryTrait;
    /** @deprecated */
    public $EstimatedArrival;
    /**
     * @var EstimatedArrival
     */
    protected $estimatedArrival;
    /**
     * @param \stdClass|null $response
     */
    public function __construct(\stdClass $response = null)
    {
        $this->build($response);
        $this->setEstimatedArrival(new \UpsProVendor\Ups\Entity\EstimatedArrival());
        if (null !== $response) {
            if (isset($response->EstimatedArrival)) {
                $this->setEstimatedArrival(new \UpsProVendor\Ups\Entity\EstimatedArrival($response->EstimatedArrival));
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
     * @param EstimatedArrival
     */
    public function setEstimatedArrival(\UpsProVendor\Ups\Entity\EstimatedArrival $estimatedArrival)
    {
        $this->EstimatedArrival = $estimatedArrival;
        $this->estimatedArrival = $estimatedArrival;
    }
}
