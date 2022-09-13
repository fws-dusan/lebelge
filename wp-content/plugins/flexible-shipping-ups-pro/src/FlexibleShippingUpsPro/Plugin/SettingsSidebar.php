<?php
/**
 * Settings sidebar.
 *
 * @package WPDesk\FlexibleShippingUpsPro
 */

namespace WPDesk\FlexibleShippingUpsPro\Plugin;

use UpsProVendor\WPDesk\UpsShippingService\UpsShippingService;
use UpsProVendor\WPDesk\PluginBuilder\Plugin\Hookable;

/**
 * Can display settings sidebar.
 */
class SettingsSidebar implements Hookable {

	/**
	 * Hooks.
	 */
	public function hooks() {
		add_action(
			UpsShippingService::UNIQUE_ID . '_settings_sidebar',
			array(
				$this,
				'display_settings_sidebar_when_no_conditional_methods',
			)
		);
	}

	/**
	 * Maybe display settings sidebar.
	 *
	 * @return void
	 */
	public function display_settings_sidebar_when_no_conditional_methods() {
		if ( ! defined( 'FLEXIBLE_SHIPPING_CONDITIONAL_METHODS_VERSION' ) ) {
			$url = get_user_locale() === 'pl_PL' ? 'https://www.wpdesk.pl/sklep/conditional-shipping-methods/?utm_source=ups&utm_medium=button&utm_campaign=cross-cm' : 'https://flexibleshipping.com/products/conditional-shipping-methods-woocommerce/?utm_source=ups&utm_medium=button&utm_campaign=cross-cm';
			include __DIR__ . '/views/settings-sidebar-html.php';
		}
	}
}
