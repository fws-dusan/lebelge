<?php
/**
 * Class AddRulesetHandler
 *
 * @package WPDesk\FS\ConditionalMethods\WooCommerceSettings
 */

namespace WPDesk\FS\ConditionalMethods\WooCommerceSettings;

use FSConditionalMethodsVendor\WPDesk\PluginBuilder\Plugin\Hookable;
use WPDesk\FS\ConditionalMethods\CustomPostType;

/**
 * Handles add ruleset action.
 */
class AddRulesetHandler implements Hookable {

	const GET_PARAMETER_NAME                            = ConditionalMethodsActionsUrls::FLEXIBLE_SHIPPING_CONDITIONAL_METHODS_ADD_RULESET;
	const FLEXIBLE_SHIPPING_CONDITIONAL_METHODS_CREATED = ConditionalMethodsActionsUrls::FLEXIBLE_SHIPPING_CONDITIONAL_METHODS_CREATED;

	/**
	 * @var ConditionalMethodsActionsUrls
	 */
	private $actions_urls;

	/**
	 * AddRulesetHandler constructor.
	 *
	 * @param ConditionalMethodsActionsUrls $actions_urls .
	 */
	public function __construct( ConditionalMethodsActionsUrls $actions_urls ) {
		$this->actions_urls = $actions_urls;
	}

	/**
	 * .
	 */
	public function hooks() {
		add_action( 'admin_init', array( $this, 'handle_add_ruleset' ) );
		add_action( 'admin_init', array( $this, 'add_message' ) );
	}

	/**
	 * .
	 *
	 * @return void
	 *
	 * @internal
	 */
	public function handle_add_ruleset() {
		if ( isset( $_GET[ self::GET_PARAMETER_NAME ] )
			&& is_string( $_GET[ self::GET_PARAMETER_NAME ] )
			&& wp_verify_nonce( sanitize_text_field( stripslashes( $_GET[ self::GET_PARAMETER_NAME ] ) ) ) // phpcs:ignore.
		) {
			$post_id = wp_insert_post( array( 'post_type' => CustomPostType::POST_TYPE ) );
			if ( $post_id && ! is_wp_error( $post_id ) ) {
				wp_safe_redirect( $this->actions_urls->prepare_ruleset_created_url( $post_id ) );
				$this->end_request();
			}
		}
	}

	/**
	 * @return void
	 *
	 * @codeCoverageIgnore
	 */
	protected function end_request() {
		die();
	}

	/**
	 * .
	 *
	 * @return void
	 *
	 * @internal
	 */
	public function add_message() {
		if ( isset( $_GET[ self::FLEXIBLE_SHIPPING_CONDITIONAL_METHODS_CREATED ] ) ) { // phpcs:ignore.
			$this->add_wc_settings_message( __( 'Ruleset created.', 'flexible-shipping-conditional-methods' ) );
		}
	}

	/**
	 * @param string $message_text .
	 *
	 * @return void
	 *
	 * @codeCoverageIgnore
	 */
	protected function add_wc_settings_message( $message_text ) {
		\WC_Admin_Settings::add_message( $message_text );
	}

}
