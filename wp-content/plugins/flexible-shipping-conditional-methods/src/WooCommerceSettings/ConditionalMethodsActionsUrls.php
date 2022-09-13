<?php
/**
 * Class ConditionalMethodsActionsUrls
 *
 * @package WPDesk\FS\ConditionalMethods\WooCommerceSettings
 */

namespace WPDesk\FS\ConditionalMethods\WooCommerceSettings;

/**
 * Provides actions URLs.
 */
class ConditionalMethodsActionsUrls {

	const FLEXIBLE_SHIPPING_CONDITIONAL_METHODS_CREATED        = 'flexible-shipping-conditional-methods-created';
	const FLEXIBLE_SHIPPING_CONDITIONAL_METHODS_DELETED        = 'flexible-shipping-conditional-methods-deleted';
	const FLEXIBLE_SHIPPING_CONDITIONAL_METHODS_ADD_RULESET    = 'flexible-shipping-conditional-methods-add-ruleset';
	const FLEXIBLE_SHIPPING_CONDITIONAL_METHODS_DELETE_RULESET = 'flexible-shipping-conditional-methods-delete-ruleset';

	/**
	 * @return string
	 */
	public function prepare_settings_url() {
		return admin_url( $this->prepare_relative_admin_url() );
	}

	/**
	 * @param int|string $ruleset_id .
	 *
	 * @return string
	 */
	public function prepare_single_ruleset_settings_url( $ruleset_id ) {
		return admin_url( sprintf( '%1$s&ruleset_id=%2$s', $this->prepare_relative_admin_url(), $ruleset_id ) );
	}

	/**
	 * @return string
	 */
	public function prepare_add_ruleset_url() {
		return admin_url( sprintf( 'admin.php?%1$s=%2$s', self::FLEXIBLE_SHIPPING_CONDITIONAL_METHODS_ADD_RULESET, wp_create_nonce() ) );
	}

	/**
	 * @param int|string $ruleset_id .
	 *
	 * @return string
	 */
	public function prepare_delete_ruleset_url( $ruleset_id ) {
		return admin_url( sprintf( 'admin.php?%1$s=%2$s&ruleset_id=%3$s', self::FLEXIBLE_SHIPPING_CONDITIONAL_METHODS_DELETE_RULESET, wp_create_nonce(), $ruleset_id ) );
	}

	/**
	 * @return string
	 */
	public function prepare_ruleset_deleted_url() {
		return admin_url( sprintf( '%1$s&%2$s', $this->prepare_relative_admin_url(), self::FLEXIBLE_SHIPPING_CONDITIONAL_METHODS_DELETED ) );
	}

	/**
	 * @param int $ruleset_id .
	 *
	 * @return string
	 */
	public function prepare_ruleset_created_url( $ruleset_id ) {
		return admin_url( sprintf( '%1$s&ruleset_id=%2$s&%3$s', $this->prepare_relative_admin_url(), $ruleset_id, self::FLEXIBLE_SHIPPING_CONDITIONAL_METHODS_CREATED ) );
	}

	/**
	 * @return string
	 */
	private function prepare_relative_admin_url() {
		return 'admin.php?page=wc-settings&tab=shipping&section=flexible_shipping_conditional_methods';
	}

}
