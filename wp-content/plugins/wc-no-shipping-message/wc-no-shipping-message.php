<?php
/**
 * Plugin Name: WooCommerce No Shipping Message
 * Description: Replaces "No shipping options were found", "There are no shipping options available" and "No shipping method has been selected" messages on the cart and checkout pages with the provided text.
 * Version: 2.0.11
 * Author: dangoodman
 * Author URI: https://tablerateshipping.com
 * Requires PHP: 7.0
 * Requires at least: 4.0
 * Tested up to: 5.8
 * WC requires at least: 3.1
 * WC tested up to: 5.5
 */

use Wnsm\App;
use Wnsm\Hooks;
use Wnsm\Migrate;
use Wnsm\Settings;

call_user_func(static function()
{
	require_once(__DIR__.'/src/_autoload.php');

	Migrate::migrate(__DIR__.'/migrations', App::getVersion(__FILE__), 'wnsm_db_version');

	$app = new App(__FILE__);
	$settings = new Settings($app);
	$hooks = new Hooks($app, $settings);

    add_filter('woocommerce_shipping_settings', [$hooks, 'woocommerceShippingSettings']);

    add_filter('plugin_action_links_' . plugin_basename(wp_normalize_path(__FILE__)), [$hooks, 'pluginActionLinks']);

    add_action('admin_enqueue_scripts', [$hooks, 'adminEnqueueScripts']);

    foreach ([
        'woocommerce_cart_no_shipping_available_html' => Settings::MSG_CART,
        'woocommerce_no_shipping_available_html' => Settings::MSG_CHECKOUT,
    ] as $hook => $msgId) {
        add_filter($hook, $hooks->woocommerceNoShippingAvailableHtml($msgId));
    }

    add_action('woocommerce_after_checkout_validation', [$hooks, 'woocommerceCheckoutAfterValidation'], 10, 2);

    add_action('woocommerce_update_options_shipping_options', static function() { Settings::normalize(); });
});
