<?php

namespace FlexibleShippingImportExportVendor\WPDesk\Helper\Integration;

use FlexibleShippingImportExportVendor\WPDesk\Helper\Page\SettingsPage;
use FlexibleShippingImportExportVendor\WPDesk\PluginBuilder\Plugin\Hookable;
use FlexibleShippingImportExportVendor\WPDesk\PluginBuilder\Plugin\HookableCollection;
use FlexibleShippingImportExportVendor\WPDesk\PluginBuilder\Plugin\HookableParent;
/**
 * Integrates WP Desk main settings page with WordPress
 *
 * @package WPDesk\Helper
 */
class SettingsIntegration implements \FlexibleShippingImportExportVendor\WPDesk\PluginBuilder\Plugin\Hookable, \FlexibleShippingImportExportVendor\WPDesk\PluginBuilder\Plugin\HookableCollection
{
    use HookableParent;
    /** @var SettingsPage */
    private $settings_page;
    public function __construct(\FlexibleShippingImportExportVendor\WPDesk\Helper\Page\SettingsPage $settingsPage)
    {
        $this->add_hookable($settingsPage);
    }
    /**
     * @return void
     */
    public function hooks()
    {
        $this->hooks_on_hookable_objects();
    }
}
