<?php

namespace FSConditionalMethodsVendor\WPDesk\View\Renderer;

use FSConditionalMethodsVendor\WPDesk\View\Resolver\Resolver;
/**
 * Can render templates
 */
class LoadTemplatePlugin implements \FSConditionalMethodsVendor\WPDesk\View\Renderer\Renderer
{
    private $plugin;
    private $path;
    public function __construct($plugin, $path = '')
    {
        $this->plugin = $plugin;
        $this->path = $path;
    }
    public function set_resolver(\FSConditionalMethodsVendor\WPDesk\View\Resolver\Resolver $resolver)
    {
    }
    public function render($template, array $params = null)
    {
        return $this->plugin->load_template($template, $this->path, $params);
    }
}
