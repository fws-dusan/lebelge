<?php
/**
 * Trait SettingsPageDetector
 *
 * @package WPDesk\FS\ConditionalMethods\WooCommerceSettings
 */

namespace WPDesk\FS\ConditionalMethods\WooCommerceSettings;

/**
 * Can detect plugin settings pages.
 */
trait SettingsPageDetector {

	/**
	 * @return bool
	 */
	private function is_settings_page() {
		return isset( $_GET['page'] ) && isset( $_GET['tab'] ) && isset( $_GET['section'] )
			&& 'wc-settings' === $_GET['page']
			&& 'shipping' === $_GET['tab']
			&& 'flexible_shipping_conditional_methods' === $_GET['section'];
	}

}
