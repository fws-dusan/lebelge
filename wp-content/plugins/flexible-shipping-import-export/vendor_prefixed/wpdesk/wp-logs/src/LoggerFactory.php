<?php

namespace FlexibleShippingImportExportVendor\WPDesk\Logger;

use FlexibleShippingImportExportVendor\Monolog\Logger;
/*
 * @package WPDesk\Logger
 */
interface LoggerFactory
{
    /**
     * Returns created Logger
     *
     * @param string $name
     *
     * @return Logger
     */
    public function getLogger($name);
}
