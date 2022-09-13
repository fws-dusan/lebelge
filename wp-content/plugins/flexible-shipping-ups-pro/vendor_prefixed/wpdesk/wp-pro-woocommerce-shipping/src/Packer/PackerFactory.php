<?php

namespace UpsProVendor\WPDesk\WooCommerceShippingPro\Packer;

use UpsProVendor\WPDesk\Packer\Box;
use UpsProVendor\WPDesk\Packer\Packer;
use UpsProVendor\WPDesk\Packer\PackerSeparately;
/**
 * Can create a ready to use  packer.
 *
 * @package WPDesk\WooCommerceShippingPro\Packer
 */
class PackerFactory
{
    /** @var string */
    private $packaging_method;
    /**
     * PackerFactory constructor.
     *
     * @param string $packaging_method One of packaging method names
     */
    public function __construct($packaging_method)
    {
        $this->packaging_method = $packaging_method;
    }
    /**
     * Create packer that can pack to given boxes.
     *
     * @param Box[] $boxes Boxes to pack.
     *
     * @return Packer
     */
    public function create_packer(array $boxes)
    {
        if ($this->packaging_method === \UpsProVendor\WPDesk\WooCommerceShippingPro\Packer\PackerSettings::PACKING_METHOD_SEPARATELY) {
            $packer = new \UpsProVendor\WPDesk\Packer\PackerSeparately();
        } else {
            $packer = new \UpsProVendor\WPDesk\Packer\Packer();
            foreach ($boxes as $box) {
                $packer->add_box($box);
            }
        }
        return $packer;
    }
}
