<?php

namespace WPDesk\FS\ConditionalMethods\ConditionalForm\WooCommerce;

use FSConditionalMethodsVendor\WPDesk\PluginBuilder\Plugin\Hookable;
use WPDesk\FS\ConditionalMethods\WooCommerceSettings\SettingsPageDetector;

class ConditionalFormFieldAssets implements Hookable {

	use SettingsPageDetector;

	/**
	 * @var string
	 */
	private $assets_url;
	/**
	 * @var string
	 */
	private $scripts_version;

	/**
	 * PackagesFieldAssets constructor.
	 *
	 * @param string $assets_url .
	 * @param string $scripts_version .
	 */
	public function __construct( $assets_url, $scripts_version ) {
		$this->assets_url      = $assets_url;
		$this->scripts_version = $scripts_version;
	}

	/**
	 * .
	 */
	public function hooks() {
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
	}

	/**
	 * .
	 *
	 * @return void
	 *
	 * @internal
	 */
	public function admin_enqueue_scripts() {
		if ( $this->is_settings_page() ) {
			wp_register_script(
				'fs_cm_conditional_form',
				trailingslashit( $this->assets_url ) . 'js/conditional-form.js',
				array( 'wp-i18n', 'jquery', 'jquery-ui-sortable', 'wc-enhanced-select' ),
				$this->scripts_version,
				true
			);
			wp_enqueue_script( 'fs_cm_conditional_form' );

			wp_enqueue_style(
				'fs_cm_conditional_form',
				trailingslashit( $this->assets_url ) . 'css/conditional-form.css',
				array(),
				$this->scripts_version
			);
		}
	}

}
