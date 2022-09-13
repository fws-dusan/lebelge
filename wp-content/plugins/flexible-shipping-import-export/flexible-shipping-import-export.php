<?php
/**
	Plugin Name: Flexible Shipping Import/Export for WooCommerce
	Plugin URI: https://flexibleshipping.com/products/flexible-import-export-shipping-methods-woocommerce/?utm_source=fsie&utm_medium=link&utm_campaign=plugin-list-page
	Description: Use the CSV files to import or export your shipping methods. Edit, update, move or backup the ready configurations and shipping scenarios.
	Version: 1.1.0
	Author: Flexible Shipping
	Author URI: https://flexibleshipping.com/?utm_source=fsie&utm_medium=link&utm_campaign=plugin-list-author
	Text Domain: flexible-shipping-import-export
	Domain Path: /lang/
	Requires at least: 5.2
	Tested up to: 5.7
	WC requires at least: 4.7
	WC tested up to: 5.2
	Requires PHP: 7.0.10

	@package Flexible Shipping Import Export

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
$plugin_version = '1.1.0';

$plugin_name        = 'Flexible Shipping Import/Export for WooCommerce';
$plugin_class_name  = 'WPDesk\FS\TableRate\ImportExport\Plugin';
$plugin_text_domain = 'flexible-shipping-import-export';
$product_id         = 'Flexible Shipping Import/Export for WooCommerce';
$plugin_file        = __FILE__;
$plugin_dir         = dirname( __FILE__ );

define( 'FLEXIBLE_SHIPPING_IMPORT_EXPORT_VERSION', $plugin_version );
define( $plugin_class_name, $plugin_version );

$requirements = array(
	'php'     => '5.6',
	'wp'      => '4.5',
	'repo_plugins' => array(
		array(
			'name'      => 'flexible-shipping/flexible-shipping.php',
			'nice_name' => 'Flexible Shipping',
			'version'   => '4.1',
		),
	),
	'plugins' => array(
		array(
			'name'      => 'flexible-shipping/flexible-shipping.php',
			'nice_name' => 'Flexible Shipping',
			'version'   => '4.1',
		),
	),
);

require __DIR__ . '/vendor_prefixed/wpdesk/wp-plugin-flow/src/plugin-init-php52.php';
