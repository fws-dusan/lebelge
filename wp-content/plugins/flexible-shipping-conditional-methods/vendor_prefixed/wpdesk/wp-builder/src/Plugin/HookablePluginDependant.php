<?php

namespace FSConditionalMethodsVendor\WPDesk\PluginBuilder\Plugin;

interface HookablePluginDependant extends \FSConditionalMethodsVendor\WPDesk\PluginBuilder\Plugin\Hookable
{
    /**
     * Set Plugin.
     *
     * @param AbstractPlugin $plugin Plugin.
     *
     * @return null
     */
    public function set_plugin(\FSConditionalMethodsVendor\WPDesk\PluginBuilder\Plugin\AbstractPlugin $plugin);
    /**
     * Get plugin.
     *
     * @return AbstractPlugin.
     */
    public function get_plugin();
}
