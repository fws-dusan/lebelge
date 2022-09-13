<?php
/**
 * Class DeleteRulesetHandler
 *
 * @package WPDesk\FS\ConditionalMethods\WooCommerceSettings
 */

namespace WPDesk\FS\ConditionalMethods\WooCommerceSettings;

use FSConditionalMethodsVendor\WPDesk\PluginBuilder\Plugin\Hookable;
use WPDesk\FS\ConditionalMethods\CustomPostType;
use WPDesk\FS\ConditionalMethods\Settings\SingleRulesetSettingsFactory;

/**
 * Handles delete ruleset action.
 */
class DeleteRulesetHandler implements Hookable {

	const GET_PARAMETER_NAME                 = ConditionalMethodsActionsUrls::FLEXIBLE_SHIPPING_CONDITIONAL_METHODS_DELETE_RULESET;
	const FLEXIBLE_SHIPPING_PACKAGES_DELETED = ConditionalMethodsActionsUrls::FLEXIBLE_SHIPPING_CONDITIONAL_METHODS_DELETED;

	/**
	 * @var ConditionalMethodsActionsUrls
	 */
	private $actions_urls;

	/**
	 * DeletePackageHandler constructor.
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
		add_action( 'admin_init', array( $this, 'handle_delete_ruleset' ) );
		add_action( 'admin_init', array( $this, 'add_message' ) );
	}

	/**
	 * .
	 *
	 * @return void
	 *
	 * @internal
	 */
	public function handle_delete_ruleset() {
		if ( isset( $_GET[ self::GET_PARAMETER_NAME ] )
			&& is_string( $_GET[ self::GET_PARAMETER_NAME ] )
			&& wp_verify_nonce( sanitize_text_field( stripslashes( $_GET[ self::GET_PARAMETER_NAME ] ) ) ) // phpcs:ignore.
			&& isset( $_GET['ruleset_id'] )
		) {
			$post = get_post( (int) sanitize_key( stripslashes( $_GET['ruleset_id'] ) ) ); // phpcs:ignore.
			if ( isset( $post ) && CustomPostType::POST_TYPE === $post->post_type ) {
				wp_delete_post( $post->ID );
				delete_option( ( new SingleRulesetSettingsFactory() )->prepare_option_name( $post->ID ) );
				wp_safe_redirect( $this->actions_urls->prepare_ruleset_deleted_url() );

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
		if ( isset( $_GET[ self::FLEXIBLE_SHIPPING_PACKAGES_DELETED ] ) ) { // phpcs:ignore.
			$this->add_wc_settings_message( __( 'Ruleset deleted.', 'flexible-shipping-conditional-methods' ) );
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
