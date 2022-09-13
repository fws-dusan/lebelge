<?php
/**
 * Class RulesetFieldAssets
 *
 * @package WPDesk\FS\ConditionalMethods\WooCommerceSettings
 */

namespace WPDesk\FS\ConditionalMethods\WooCommerceSettings;

use FSConditionalMethodsVendor\WPDesk\PluginBuilder\Plugin\Hookable;

/**
 * Can enqueue assets for Rulesets field.
 */
class RulesetFieldAssets implements Hookable {

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
				'fs_cm_rules_settings',
				trailingslashit( $this->assets_url ) . 'js/settings.js',
				array( 'wp-i18n', 'jquery', 'jquery-ui-sortable', 'wc-enhanced-select' ),
				$this->scripts_version,
				true
			);
			wp_enqueue_script( 'fs_cm_rules_settings' );

			wp_enqueue_style(
				'fs_cm_rules_settings',
				trailingslashit( $this->assets_url ) . 'css/settings.css',
				array(),
				$this->scripts_version
			);
		}
	}

}
