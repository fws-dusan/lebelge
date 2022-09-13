<?php

namespace FSProVendor\WPDesk\Helper\Integration;

use FSProVendor\WPDesk\Helper\Page\SettingsPage;
use FSProVendor\WPDesk\PluginBuilder\Plugin\Hookable;
use FSProVendor\WPDesk\PluginBuilder\Plugin\HookableCollection;
use FSProVendor\WPDesk\PluginBuilder\Plugin\HookableParent;
/**
 * Integrates WP Desk main settings page with WordPress
 *
 * @package WPDesk\Helper
 */
class SettingsIntegration implements \FSProVendor\WPDesk\PluginBuilder\Plugin\Hookable, \FSProVendor\WPDesk\PluginBuilder\Plugin\HookableCollection
{
    use HookableParent;
    /** @var SettingsPage */
    private $settings_page;
    public function __construct(\FSProVendor\WPDesk\Helper\Page\SettingsPage $settingsPage)
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
