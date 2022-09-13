<?php

namespace UpsProVendor\WPDesk\View\Resolver;

use UpsProVendor\WPDesk\View\Renderer\Renderer;
use UpsProVendor\WPDesk\View\Resolver\Exception\CanNotResolve;
/**
 * This resolver never finds the file
 *
 * @package WPDesk\View\Resolver
 */
class NullResolver implements \UpsProVendor\WPDesk\View\Resolver\Resolver
{
    public function resolve($name, \UpsProVendor\WPDesk\View\Renderer\Renderer $renderer = null)
    {
        throw new \UpsProVendor\WPDesk\View\Resolver\Exception\CanNotResolve("Null Cannot resolve");
    }
}
