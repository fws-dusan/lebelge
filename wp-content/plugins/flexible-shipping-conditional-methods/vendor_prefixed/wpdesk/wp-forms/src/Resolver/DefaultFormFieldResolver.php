<?php

namespace FSConditionalMethodsVendor\WPDesk\Forms\Resolver;

use FSConditionalMethodsVendor\WPDesk\View\Renderer\Renderer;
use FSConditionalMethodsVendor\WPDesk\View\Resolver\DirResolver;
use FSConditionalMethodsVendor\WPDesk\View\Resolver\Resolver;
/**
 * Use with View to resolver form fields to default templates.
 *
 * @package WPDesk\Forms\Resolver
 */
class DefaultFormFieldResolver implements \FSConditionalMethodsVendor\WPDesk\View\Resolver\Resolver
{
    /** @var Resolver */
    private $dir_resolver;
    public function __construct()
    {
        $this->dir_resolver = new \FSConditionalMethodsVendor\WPDesk\View\Resolver\DirResolver(__DIR__ . '/../../templates');
    }
    public function resolve($name, \FSConditionalMethodsVendor\WPDesk\View\Renderer\Renderer $renderer = null)
    {
        return $this->dir_resolver->resolve($name, $renderer);
    }
}
