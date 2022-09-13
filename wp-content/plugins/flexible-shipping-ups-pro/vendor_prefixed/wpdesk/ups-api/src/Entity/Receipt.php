<?php

namespace UpsProVendor\Ups\Entity;

class Receipt
{
    public $HTMLImage;
    public $Image;
    /**
     * @param \stdClass|null $response
     */
    public function __construct(\stdClass $response = null)
    {
        $this->Image = new \UpsProVendor\Ups\Entity\Image();
        if (null !== $response) {
            if (isset($response->HTMLImage)) {
                $this->HTMLImage = $response->HTMLImage;
            }
            if (isset($response->Image)) {
                $this->Image = new \UpsProVendor\Ups\Entity\Image($response->Image);
            }
        }
    }
}
