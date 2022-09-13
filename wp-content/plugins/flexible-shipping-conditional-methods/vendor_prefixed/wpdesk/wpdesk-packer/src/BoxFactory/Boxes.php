<?php

/**
 * Boxes interface.
 *
 * @package WPDesk\Packer\BoxFactory
 */
namespace FSConditionalMethodsVendor\WPDesk\Packer\BoxFactory;

use FSConditionalMethodsVendor\WPDesk\Packer\Box;
/**
 * Boxes as array.
 */
interface Boxes
{
    /**
     * Get boxes array.
     *
     * @return Box[]
     */
    public function get_boxes();
}
