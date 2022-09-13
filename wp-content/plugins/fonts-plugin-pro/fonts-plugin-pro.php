<?php
/**
 * Fonts Plugin Pro
 *
 * Plugin Name: Fonts Plugin Pro
 * Plugin URI:  https://fontsplugin.com
 * Description: Thank you for upgrading to Google Fonts Pro.
 * Version:     1.5.5
 * Author:      FontsPlugin.com
 * Author URI:  https://fontsplugin.com/
 * Text Domain: fonts-plugin-pro
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path: /languages
 *
 * @package   fonts-plugin-pro
 * @copyright Copyright (c) 2019, Fonts Plugin
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

define( 'OGF_PRO', true );
define( 'FPP_VERSION', '1.5.5' );
define( 'FPP_DIR_PATH', plugin_dir_path( __FILE__ ) );
define( 'FPP_DIR_URL', plugin_dir_url( __FILE__ ) );

require FPP_DIR_PATH . 'class-fonts-plugin-pro.php';
require FPP_DIR_PATH . '/inc/class-license-key.php';
require FPP_DIR_PATH . '/inc/class-fpp-updater.php';

/**
 * Initialize Fonts Plugin Pro.
 */
function fonts_plugin_pro_init() {
	$fonts_plugin_pro = new Fonts_Plugin_Pro();
}

add_action( 'plugins_loaded', 'fonts_plugin_pro_init', 20 );

/**
 * Initialize the updater. Hooked into `init` to work with the
 * wp_version_check cron job, which allows auto-updates.
 */
function fpp_plugin_updater() {

	// To support auto-updates, this needs to run during the wp_version_check cron job for privileged users.
	$doing_cron = defined( 'DOING_CRON' ) && DOING_CRON;
	if ( ! current_user_can( 'manage_options' ) && ! $doing_cron ) {
		return;
	}

	// Retrieve our license key from the DB.
	$license_key = trim( get_option( 'fpp_license_key' ) );

	// Setup the updater.
	$fpp_updater = new FPP_Updater(
		'https://fontsplugin.com/',
		__FILE__,
		array(
			'version' => FPP_VERSION,
			'license' => $license_key,
			'item_id' => 2191,
			'author'  => 'Fonts Plugin',
			'url'     => home_url(),
			'beta'    => false,
		)
	);

}
add_action( 'init', 'fpp_plugin_updater' );
