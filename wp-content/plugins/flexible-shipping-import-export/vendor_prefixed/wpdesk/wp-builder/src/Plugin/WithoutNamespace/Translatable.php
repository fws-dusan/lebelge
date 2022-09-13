<?php

namespace FlexibleShippingImportExportVendor;

if (!\interface_exists('FlexibleShippingImportExportVendor\\WPDesk_Translable')) {
    require_once 'Translable.php';
}
/**
 * Have info about textdomain - how to translate texts
 *
 * have to be compatible with PHP 5.2.x
 */
interface WPDesk_Translatable extends \FlexibleShippingImportExportVendor\WPDesk_Translable
{
    /** @return string */
    public function get_text_domain();
}
