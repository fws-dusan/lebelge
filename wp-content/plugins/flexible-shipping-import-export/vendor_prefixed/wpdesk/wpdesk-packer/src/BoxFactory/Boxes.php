<?php

/**
 * Boxes interface.
 *
 * @package WPDesk\Packer\BoxFactory
 */
namespace FlexibleShippingImportExportVendor\WPDesk\Packer\BoxFactory;

use FlexibleShippingImportExportVendor\WPDesk\Packer\Box;
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
