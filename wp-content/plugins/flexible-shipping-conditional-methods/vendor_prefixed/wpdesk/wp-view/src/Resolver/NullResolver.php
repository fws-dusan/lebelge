<?php

namespace FSConditionalMethodsVendor\WPDesk\View\Resolver;

use FSConditionalMethodsVendor\WPDesk\View\Renderer\Renderer;
use FSConditionalMethodsVendor\WPDesk\View\Resolver\Exception\CanNotResolve;
/**
 * This resolver never finds the file
 *
 * @package WPDesk\View\Resolver
 */
class NullResolver implements \FSConditionalMethodsVendor\WPDesk\View\Resolver\Resolver
{
    public function resolve($name, \FSConditionalMethodsVendor\WPDesk\View\Renderer\Renderer $renderer = null)
    {
        throw new \FSConditionalMethodsVendor\WPDesk\View\Resolver\Exception\CanNotResolve("Null Cannot resolve");
    }
}
