<?php

namespace FlexibleShippingImportExportVendor\WPDesk\PluginBuilder\Plugin;

interface HookablePluginDependant extends \FlexibleShippingImportExportVendor\WPDesk\PluginBuilder\Plugin\Hookable
{
    /**
     * Set Plugin.
     *
     * @param AbstractPlugin $plugin Plugin.
     *
     * @return null
     */
    public function set_plugin(\FlexibleShippingImportExportVendor\WPDesk\PluginBuilder\Plugin\AbstractPlugin $plugin);
    /**
     * Get plugin.
     *
     * @return AbstractPlugin.
     */
    public function get_plugin();
}
