<?php

namespace FSConditionalMethodsVendor\WPDesk\Helper\Integration;

use FSConditionalMethodsVendor\WPDesk\Helper\Page\SettingsPage;
use FSConditionalMethodsVendor\WPDesk\PluginBuilder\Plugin\Hookable;
use FSConditionalMethodsVendor\WPDesk\PluginBuilder\Plugin\HookableCollection;
use FSConditionalMethodsVendor\WPDesk\PluginBuilder\Plugin\HookableParent;
/**
 * Integrates WP Desk main settings page with WordPress
 *
 * @package WPDesk\Helper
 */
class SettingsIntegration implements \FSConditionalMethodsVendor\WPDesk\PluginBuilder\Plugin\Hookable, \FSConditionalMethodsVendor\WPDesk\PluginBuilder\Plugin\HookableCollection
{
    use HookableParent;
    /** @var SettingsPage */
    private $settings_page;
    public function __construct(\FSConditionalMethodsVendor\WPDesk\Helper\Page\SettingsPage $settingsPage)
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
