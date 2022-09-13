<?php

namespace UpsProVendor\Ups\Entity;

class LabelSpecification
{
    public $HTTPUserAgent;
    public $LabelImageFormat;
    /** @var LabelStockSize|null */
    public $LabelStockSize;
    /**
     * @param \stdClass|null $response
     */
    public function __construct(\stdClass $response = null)
    {
        $this->LabelImageFormat = new \UpsProVendor\Ups\Entity\LabelImageFormat();
        if (null !== $response) {
            if (isset($response->HTTPUserAgent)) {
                $this->HTTPUserAgent = $response->HTTPUserAgent;
            }
            if (isset($response->LabelImageFormat)) {
                $this->LabelImageFormat = new \UpsProVendor\Ups\Entity\LabelImageFormat($response->LabelImageFormat);
            }
        }
    }
}
