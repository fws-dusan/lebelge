<?php

namespace FlexibleShippingImportExportVendor\WPDesk\View\Resolver;

use FlexibleShippingImportExportVendor\WPDesk\View\Renderer\Renderer;
use FlexibleShippingImportExportVendor\WPDesk\View\Resolver\Exception\CanNotResolve;
/**
 * This resolver never finds the file
 *
 * @package WPDesk\View\Resolver
 */
class NullResolver implements \FlexibleShippingImportExportVendor\WPDesk\View\Resolver\Resolver
{
    public function resolve($name, \FlexibleShippingImportExportVendor\WPDesk\View\Renderer\Renderer $renderer = null)
    {
        throw new \FlexibleShippingImportExportVendor\WPDesk\View\Resolver\Exception\CanNotResolve("Null Cannot resolve");
    }
}
