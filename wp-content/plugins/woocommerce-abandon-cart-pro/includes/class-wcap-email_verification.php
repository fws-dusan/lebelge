<?php
/**
 * This file will add functions related to verifying email present on ATC field.
 *
 * @author  Tyche Softwares
 * @package Abandoned-Cart-Pro-for-WooCommerce/Admin/ATC
 * @since 8.8.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Wcap_Email_Verification' ) ) {

	/**
	 * Email Verification Class
	 */
	class Wcap_Email_Verification {

		/**
		 * Contructor.
		 */
		public function __construct() {
			add_action( 'wcap_add_new_settings', array( &$this, 'add_verification_settings' ) );

			add_filter( 'wcap_popup_params', array( &$this, 'wcap_add_api_param' ) );
		}

		/**
		 * Add new settings
		 */
		public function add_verification_settings() {

			add_settings_field(
				'wcap_enable_debounce',
				__( 'Enable Email Verification:', 'woocommerce-ac' ),
				array( &$this, 'wcap_enable_debounce_callback' ),
				'woocommerce_ac_page',
				'ac_general_settings_section',
				array( __( 'Enable this checkbox to allow email verification to be done via DeBounce API services.', 'woocommerce-ac' ) )
			);

			add_settings_field(
				'ac_debounce_api',
				__( 'Enter DeBounce API Key', 'woocommerce-ac' ),
				array( &$this, 'wcap_debounce_api_callback' ),
				'woocommerce_ac_page',
				'ac_general_settings_section',
				array( __( 'Enter DeBounce JS API Key.', 'woocommerce-ac' ) )
			);

			register_setting(
				'woocommerce_ac_settings',
				'wcap_enable_debounce'
			);

			register_setting(
				'woocommerce_ac_settings',
				'ac_debounce_api'
			);
		}

		/**
		 * Email Verification Callback.
		 *
		 * @param array $args Args Param.
		 */
		public static function wcap_enable_debounce_callback( $args ) {
			$enable_debounce = get_option( 'wcap_enable_debounce', '' );
			$debounce_choice = 'on' === $enable_debounce ? 'checked' : '';
			?>

			<input
				type="checkbox"
				id="wcap_enable_debounce"
				name="wcap_enable_debounce"
				value="on"
				<?php echo esc_attr( $debounce_choice ); ?> />
			<label for="wcap_enable_debounce"><?php echo esc_attr( $args[0] ); ?></label>
			<?php
		}

		/**
		 * DeBounce API Callback.
		 *
		 * @param array $args Args Param.
		 */
		public function wcap_debounce_api_callback( $args ) {
			$debounce_key = get_option( 'ac_debounce_api' );
			?>

			<input
				type="text"
				id="ac_debounce_api"
				name="ac_debounce_api"
				value="<?php echo isset( $debounce_key ) ? esc_attr( $debounce_key ) : ''; ?>" />
			<label for="ac_debounce_api"><?php echo esc_attr( $args[0] ); ?></label>
			<?php
		}

		/**
		 * Add Localize param to ATC
		 *
		 * @param array $localize_params Localize Params.
		 * @return array
		 */
		public function wcap_add_api_param( $localize_params ) {

			if ( 'on' === get_option( 'wcap_enable_debounce', '' ) && '' !== get_option( 'ac_debounce_api' ) ) {
				$localize_params['wcap_debounce_key'] = get_option( 'ac_debounce_api' );
			}
			return $localize_params;
		}
	}
}

return new Wcap_Email_Verification();
