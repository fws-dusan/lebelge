<?php

/**
 * BoxesWithUnit interface.
 *
 * @package WPDesk\Packer\BoxFactory
 */
namespace UpsProVendor\WPDesk\Packer\BoxFactory;

/**
 * Boxes array.
 */
interface BoxesWithUnit extends \UpsProVendor\WPDesk\Packer\BoxFactory\Boxes
{
    /**
     * Returns true when metric units are used in boxes.
     *
     * @return bool
     */
    public function is_metric();
}
