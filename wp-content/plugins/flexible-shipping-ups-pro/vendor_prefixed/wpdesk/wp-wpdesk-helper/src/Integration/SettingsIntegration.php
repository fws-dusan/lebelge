<?php

namespace UpsProVendor\WPDesk\Helper\Integration;

use UpsProVendor\WPDesk\Helper\Page\SettingsPage;
use UpsProVendor\WPDesk\PluginBuilder\Plugin\Hookable;
use UpsProVendor\WPDesk\PluginBuilder\Plugin\HookableCollection;
use UpsProVendor\WPDesk\PluginBuilder\Plugin\HookableParent;
/**
 * Integrates WP Desk main settings page with WordPress
 *
 * @package WPDesk\Helper
 */
class SettingsIntegration implements \UpsProVendor\WPDesk\PluginBuilder\Plugin\Hookable, \UpsProVendor\WPDesk\PluginBuilder\Plugin\HookableCollection
{
    use HookableParent;
    /** @var SettingsPage */
    private $settings_page;
    public function __construct(\UpsProVendor\WPDesk\Helper\Page\SettingsPage $settingsPage)
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
