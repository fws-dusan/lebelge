<?php
/**
	Plugin Name: Conditional Shipping Methods
	Plugin URI: https://flexibleshipping.com/products/conditional-shipping-methods-woocommerce/?utm_source=cm&utm_medium=link&utm_campaign=plugin-list-page
	Description: Conditionally display and hide the shipping methods in your shop. Define the rules when the specific shipping methods should be available to pick and when not to.
	Version: 1.0.0
	Author: Flexible Shipping
	Author URI: https://flexibleshipping.com/?utm_source=cm&utm_medium=link&utm_campaign=plugin-list-author
	Text Domain: flexible-shipping-conditional-methods
	Domain Path: /lang/
	Requires at least: 5.2
	Tested up to: 5.7
	WC requires at least: 4.8
	WC tested up to: 5.3
	Requires PHP: 7.0.10

	@package Flexible Shipping Vendors

	Copyright 2016 WP Desk Ltd.

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 2 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

/* THIS VARIABLE CAN BE CHANGED AUTOMATICALLY */
$plugin_version = '1.0.0';

$plugin_name        = 'Flexible Shipping Conditional Methods';
$plugin_class_name  = 'WPDesk\FS\ConditionalMethods\Plugin';
$plugin_text_domain = 'flexible-shipping-conditional-methods';
$product_id         = 'Flexible Shipping Conditional Methods';
$plugin_file        = __FILE__;
$plugin_dir         = dirname( __FILE__ );

define( 'FLEXIBLE_SHIPPING_CONDITIONAL_METHODS_VERSION', $plugin_version );
define( $plugin_class_name, $plugin_version );

$requirements = array(
	'php'     => '5.6',
	'wp'      => '4.5',
	'repo_plugins' => array(
		array(
			'name'      => 'woocommerce/woocommerce.php',
			'nice_name' => 'WooCommerce',
			'version'   => '4.6',
		),
	),
);

require __DIR__ . '/vendor_prefixed/wpdesk/wp-plugin-flow/src/plugin-init-php52.php';
