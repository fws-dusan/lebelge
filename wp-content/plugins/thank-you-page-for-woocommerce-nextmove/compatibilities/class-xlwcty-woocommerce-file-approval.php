<?php

defined( 'ABSPATH' ) || exit;

/**
 * Compatibility with Woocommerce File Approval plugin
 */
class XLWCTY_Woocommerce_File_Approval {
	private static $ins = null;
	private static $active_plugins;

	public function __construct() {
		self::$active_plugins = (array) get_option( 'active_plugins', array() );

		if ( is_multisite() ) {
			self::$active_plugins = array_merge( self::$active_plugins, get_site_option( 'active_sitewide_plugins', array() ) );
		}

		$file_approval_active = in_array( 'woocommerce-file-approval/woocommerce-file-approval.php', self::$active_plugins, true ) || array_key_exists( 'woocommerce-file-approval/woocommerce-file-approval.php', self::$active_plugins );

		if ( ( function_exists( 'is_plugin_active' ) && is_plugin_active( 'woocommerce-file-approval/woocommerce-file-approval.php' ) ) || $file_approval_active ) {
			add_filter( 'woocommerce_get_checkout_order_received_url', array( $this, 'get_thank_you_page_url' ), 99, 2 );
		}
	}

	public static function get_instance() {
		if ( self::$ins == null ) {
			self::$ins = new self;
		}

		return self::$ins;
	}

	public function get_thank_you_page_url( $url, $order ) {
		$default_settings = XLWCTY_Core()->data->get_option();
		if ( isset( $default_settings['xlwcty_preview_mode'] ) && ( 'sandbox' === $default_settings['xlwcty_preview_mode'] ) ) {
			return $url;
		}
		$order_id = $order->get_id();
		if ( 0 != $order_id ) {
			$get_link = XLWCTY_Core()->data->setup_thankyou_post( $order_id, true )->get_page_link();
			if ( false !== $get_link ) {
				$get_link = trim( $get_link );
				$get_link = wp_specialchars_decode( $get_link );

				return ( XLWCTY_Common::prepare_single_post_url( $get_link, $order ) );
			}
		}

		return $url;
	}

}

if ( is_admin() ) {
	XLWCTY_Woocommerce_File_Approval::get_instance();
}

