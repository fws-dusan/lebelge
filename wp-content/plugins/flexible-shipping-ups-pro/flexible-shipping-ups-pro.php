<?php
/**
 * Plugin Name: Flexible Shipping UPS PRO
 * Plugin URI: https://flexibleshipping.com/products/flexible-shipping-ups-pro//?utm_source=ups-pro&utm_medium=link&utm_campaign=plugin-list-page/
 * Description: WooCommerce UPS integration packed with many advanced features. Display the dynamically calculated live rates for UPS shipping methods and adjust them to your needs.
 * Version: 1.10.0
 * Author: WP Desk
 * Author URI: https://flexibleshipping.com/?utm_source=ups-pro&utm_medium=link&utm_campaign=plugin-list-author/
 * Text Domain: flexible-shipping-ups-pro
 * Domain Path: /lang/
 * Requires at least: 5.2
 * Tested up to: 5.9
 * WC requires at least: 5.2
 * WC tested up to: 5.6
 * Requires PHP: 7.0
 *
 * Copyright 2016 WP Desk Ltd.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * @package Flexible Shipping Ups Pro
 */

use WPDesk\FlexibleShippingUpsPro\Plugin\Plugin;
use WPDesk\FreeDisabler\UpsFreeDisabler;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

/* THIS VARIABLE CAN BE CHANGED AUTOMATICALLY */
$plugin_version = '1.10.0';

$plugin_name        = 'Flexible Shipping UPS PRO';
$product_id         = 'Flexible Shipping UPS PRO';
$plugin_class_name  = Plugin::class;
$plugin_text_domain = 'flexible-shipping-ups-pro';
$plugin_file        = __FILE__;
$plugin_dir         = __DIR__;

define( 'FLEXIBLE_SHIPPING_UPS_PRO_VERSION', $plugin_version );
define( $plugin_class_name, $plugin_version );

$requirements = [
	'php'     => '5.6',
	'wp'      => '4.5',
	'plugins' => [
		[
			'name'      => 'woocommerce/woocommerce.php',
			'nice_name' => 'WooCommerce',
		],
	],
];

require __DIR__ . '/vendor_prefixed/wpdesk/wp-plugin-flow/src/plugin-init-php52.php';

require_once __DIR__ . '/vendor_prefixed/guzzlehttp/guzzle/src/functions_include.php';
require_once __DIR__ . '/vendor_prefixed/guzzlehttp/promises/src/functions_include.php';
require_once __DIR__ . '/vendor_prefixed/guzzlehttp/psr7/src/functions_include.php';

// Disable free version.
if ( PHP_VERSION_ID > 50300 ) {
	require_once __DIR__ . '/src/PluginDisabler/UpsFreeDisabler.php';
	UpsFreeDisabler::disable_free();
}
