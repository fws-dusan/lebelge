<?php
/**
 * It will fetch the Add to cart data, generate and populate data in the modal.
 *
 * @author  Tyche Softwares
 * @package Abandoned-Cart-Pro-for-WooCommerce/Admin/Settings
 * @since 6.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
if ( ! class_exists( 'Wcap_Add_Cart_Popup_Modal' ) ) {

	/**
	 * It will fetch the Add to cart data, generate and populate data in the modal.
	 *
	 * @since 6.0
	 */
	class Wcap_Add_Cart_Popup_Modal {

		/**
		 * This function will add the add to cart popup medal's settings.
		 *
		 * @since 6.0
		 */
		public static function wcap_add_to_cart_popup_settings() {
			$id                = isset( $_GET['id'] ) ? sanitize_text_field( wp_unslash( $_GET['id'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification
			$get_template      = array();
			$frontend_settings = new stdClass();
			$coupon_settings   = new stdClass();
			$rules             = array();
			$save_mode         = 'save';
			if ( $id > 0 ) {
				$get_template = wcap_get_atc_template( sanitize_text_field( wp_unslash( $_GET['id'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification
				if ( false !== $get_template ) {
					$frontend_settings = json_decode( $get_template->frontend_settings );
					$coupon_settings   = json_decode( $get_template->coupon_settings );
					$rules             = json_decode( $get_template->rules );
				}
				$save_mode = 'update';
			}

			$wcap_disabled_field = '';
			$mode                = isset( $_GET['mode'] ) ? sanitize_text_field( wp_unslash( $_GET['mode'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
			?>
			<div id="wcap_popup_main_div" class="wcap_popup_main_div ">
				<input type="hidden" name="mode" value="<?php echo esc_attr( $mode ); ?>" />
				<input type="hidden" name="id" class='template_id' value="<?php echo esc_attr( $id ); ?>" />
				<input type="hidden" name="atc_settings_frm" value="<?php echo esc_attr( $save_mode ); ?>" />
				<table id="wcap_popup_main_table" class="wcap_popup_main_table test_borders">
					<tr id="wcap_popup_main_table_tr" class="wcap_popup_main_table_tr test_borders">
						<td id="wcap_popup_main_table_td_settings" class="wcap_popup_main_table_td_settings test_borders">    						
							<?php self::wcap_template_name_section( $get_template ); ?>
							<hr>
						</td>
					</tr>
					<tr id="wcap_popup_main_table_tr" class="wcap_popup_main_table_tr test_borders">
						<td id="wcap_popup_main_table_td_settings" class="wcap_popup_main_table_td_settings test_borders">
							<?php
							wc_get_template(
								'html-atc-rules-engine.php',
								array(
									'rules' => isset( $get_template->rules ) ? json_decode( $get_template->rules ) : array(),
									'match' => isset( $get_template->match_rules ) ? $get_template->match_rules : '',
								),
								'woocommerce-abandon-cart-pro/',
								WCAP_PLUGIN_PATH . '/includes/template/atc-rules/'
							);
							do_action( 'wcap_atc_settings_before_modal' );
							?>
							<hr>
						</td>
					</tr>
					<tr id="wcap_popup_main_table_tr" class="wcap_popup_main_table_tr test_borders">
						<td id="wcap_popup_main_table_td_settings" class="wcap_popup_main_table_td_settings test_borders">
							<div class="wcap_atc_all_fields_container" >
								<?php self::wcap_add_heading_section( $frontend_settings ); ?>
								<?php self::wcap_add_text_section( $frontend_settings ); ?>
								<?php self::wcap_email_placeholder_section( $frontend_settings ); ?>
								<?php self::wcap_button_section( $frontend_settings ); ?>
								<?php self::wcap_mandatory_modal_section( $frontend_settings ); ?>
								<?php self::wcap_non_mandatory_modal_section_field( $frontend_settings ); ?>
								<?php self::wcap_capture_phone( $frontend_settings ); ?>
								<?php self::wcap_phone_placeholder_section( $frontend_settings ); ?>
								<hr>
								<?php Wcap_Add_cart_Popup_modal::wcap_coupon_section( $coupon_settings ); ?>
							</div>
						</td>
						<td id="wcap_popup_main_table_td_preview" class="wcap_popup_main_table_td_preview test_borders">
							<div class="wcap_atc_all_fields_container" >
								<?php self::wcap_add_to_cart_popup_modal_preview( $frontend_settings ); ?>
							</div>
						</td>
					</tr>
					<tr>
						<td>
							<div class="wcap_atc_all_fields_container" >
								<p class="submit">
									<input type="submit" name="submit" id="submit" class="button button-primary" value="<?php esc_html_e( 'Save Changes', 'woocommerce-ac' ); ?>" >
									<input type="submit" name="submit" id="submit" class="wcap_reset_button button button-secondary" value="<?php esc_html_e( 'Reset to default configuration', 'woocommerce-ac' ); ?>" >
								</p>
							</div>
						</td>
					</tr>
				</table>
			</div>
			<?php
		}

		/**
		 * It will add the "Template Name" setting on the add to cart modal settings page.
		 *
		 * @param object $template_data - ATC Template Data.
		 * @since 6.0
		 */
		public static function wcap_template_name_section( $template_data ) {
			?>
				<table class="wcap_enable_atc wcap_atc_between_fields_space wcap_atc_content" id="wcap_template_name" >
					<th id="wcap_button_section_table_heading" class="wcap_button_section_table_heading"><?php esc_html_e( 'Template Name', 'woocommerce-ac' ); ?></th>
					<tr>
						<td>
							<?php
							$template_name = isset( $template_data->name ) ? $template_data->name : '';
							?>
							<input type="text" name="wcap_template_name" class="wcap_template_name" value="<?php echo esc_attr( $template_name ); ?>"  />
						</td>
					</tr>
				</table>
			<?php
		}

		/**
		 * It will Save the setting on the add to cart modal settings page.
		 *
		 * @since 6.0
		 */
		public static function wcap_add_to_cart_popup_save_settings() {

			// Rules.
			$rules = array();
			foreach ( $_POST as $key => $value ) { // phpcs:ignore WordPress.Security.NonceVerification
				if ( false !== strpos( $key, 'wcap_rule_type_' ) ) {
					// Get the id.
					$id         = substr( $key, -1 );
					$rule_type  = $value;
					$rule_cond  = isset( $_POST[ "wcap_rule_condition_$id" ] ) ? sanitize_text_field( wp_unslash( $_POST[ "wcap_rule_condition_$id" ] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
					$rule_value = isset( $_POST[ "wcap_rule_value_$id" ] ) ? $_POST[ "wcap_rule_value_$id" ] : ''; // phpcs:ignore

					$rules[] = array(
						'rule_type'      => $rule_type,
						'rule_condition' => $rule_cond,
						'rule_value'     => $rule_value,
					);
				}
			}
			// Front end Settings.
			$frontend_settings = array(
				'wcap_heading_section_text_email' => isset( $_POST['wcap_heading_section_text_email'] ) ? stripslashes( sanitize_text_field( wp_unslash( $_POST['wcap_heading_section_text_email'] ) ) ) : 'Please enter your email', // phpcs:ignore WordPress.Security.NonceVerification
				'wcap_popup_heading_color_picker' => isset( $_POST['wcap_popup_heading_color_picker'] ) ? sanitize_text_field( wp_unslash( $_POST['wcap_popup_heading_color_picker'] ) ) : '#737f97', // phpcs:ignore WordPress.Security.NonceVerification
				'wcap_text_section_text'          => isset( $_POST['wcap_text_section_text'] ) ? stripslashes( sanitize_text_field( wp_unslash( $_POST['wcap_text_section_text'] ) ) ) : 'To add this item to your cart, please enter your email address.', // phpcs:ignore WordPress.Security.NonceVerification
				'wcap_popup_text_color_picker'    => isset( $_POST['wcap_popup_text_color_picker'] ) ? sanitize_text_field( wp_unslash( $_POST['wcap_popup_text_color_picker'] ) ) : '#bbc9d2', // phpcs:ignore WordPress.Security.NonceVerification
				'wcap_email_placeholder_section_input_text' => isset( $_POST['wcap_email_placeholder_section_input_text'] ) ? sanitize_text_field( wp_unslash( $_POST['wcap_email_placeholder_section_input_text'] ) ) : 'Email Address', // phpcs:ignore WordPress.Security.NonceVerification
				'wcap_button_section_input_text'  => isset( $_POST['wcap_button_section_input_text'] ) ? stripslashes( sanitize_text_field( wp_unslash( $_POST['wcap_button_section_input_text'] ) ) ) : 'Add to Cart', // phpcs:ignore WordPress.Security.NonceVerification
				'wcap_button_color_picker'        => isset( $_POST['wcap_button_color_picker'] ) ? sanitize_text_field( wp_unslash( $_POST['wcap_button_color_picker'] ) ) : '#0085ba', // phpcs:ignore WordPress.Security.NonceVerification
				'wcap_button_text_color_picker'   => isset( $_POST['wcap_button_text_color_picker'] ) ? sanitize_text_field( wp_unslash( $_POST['wcap_button_text_color_picker'] ) ) : '#ffffff', // phpcs:ignore WordPress.Security.NonceVerification
				'wcap_non_mandatory_text'         => isset( $_POST['wcap_non_mandatory_modal_section_fields_input_text'] ) ? sanitize_text_field( wp_unslash( $_POST['wcap_non_mandatory_modal_section_fields_input_text'] ) ) : '', // phpcs:ignore WordPress.Security.NonceVerification
				'wcap_atc_mandatory_email'        => isset( $_POST['wcap_switch_atc_modal_mandatory'] ) ? sanitize_text_field( wp_unslash( $_POST['wcap_switch_atc_modal_mandatory'] ) ) : 'off', // phpcs:ignore WordPress.Security.NonceVerification
				'wcap_atc_capture_phone'          => isset( $_POST['wcap_switch_atc_capture_phone'] ) ? sanitize_text_field( wp_unslash( $_POST['wcap_switch_atc_capture_phone'] ) ) : 'off', // phpcs:ignore WordPress.Security.NonceVerification
				'wcap_atc_phone_placeholder'      => isset( $_POST['wcap_phone_placeholder_section_input_text'] ) ? sanitize_text_field( $_POST['wcap_phone_placeholder_section_input_text'] ) : 'Please enter your phone number in E.141 format', // phpcs:ignore WordPress.Security.NonceVerification
			);
			// Coupon Settings.
			$coupon_settings = array(
				'wcap_atc_auto_apply_coupon_enabled' => isset( $_POST['wcap_auto_apply_coupons_atc'] ) ? sanitize_text_field( wp_unslash( $_POST['wcap_auto_apply_coupons_atc'] ) ) : 'off', // phpcs:ignore WordPress.Security.NonceVerification
				'wcap_atc_coupon_type'               => isset( $_POST['wcap_atc_coupon_type'] ) ? sanitize_text_field( wp_unslash( $_POST['wcap_atc_coupon_type'] ) ) : '', // phpcs:ignore WordPress.Security.NonceVerification
				'wcap_atc_popup_coupon'              => isset( $_POST['coupon_ids'][0] ) ? sanitize_text_field( wp_unslash( $_POST['coupon_ids'][0] ) ) : 0, // phpcs:ignore WordPress.Security.NonceVerification
				'wcap_atc_discount_type'             => isset( $_POST['wcap_atc_discount_type'] ) ? sanitize_text_field( wp_unslash( $_POST['wcap_atc_discount_type'] ) ) : '', // phpcs:ignore WordPress.Security.NonceVerification
				'wcap_atc_discount_amount'           => isset( $_POST['wcap_atc_discount_amount'] ) ? sanitize_text_field( wp_unslash( $_POST['wcap_atc_discount_amount'] ) ) : 0, // phpcs:ignore WordPress.Security.NonceVerification
				'wcap_atc_coupon_free_shipping'      => isset( $_POST['wcap_atc_coupon_free_shipping'] ) ? sanitize_text_field( wp_unslash( $_POST['wcap_atc_coupon_free_shipping'] ) ) : 'off', // phpcs:ignore WordPress.Security.NonceVerification
				'wcap_atc_popup_coupon_validity'     => isset( $_POST['wcap_atc_coupon_validity'] ) ? sanitize_text_field( wp_unslash( $_POST['wcap_atc_coupon_validity'] ) ) : 0, // phpcs:ignore WordPress.Security.NonceVerification
				'wcap_countdown_cart'                => isset( $_POST['wcap_countdown_timer_cart'] ) ? sanitize_text_field( wp_unslash( $_POST['wcap_countdown_timer_cart'] ) ) : 'on', // phpcs:ignore WordPress.Security.NonceVerification
				'wcap_countdown_timer_msg'           => isset( $_POST['wcap_countdown_msg'] ) ? $_POST['wcap_countdown_msg'] : '', // phpcs:ignore
				'wcap_countdown_msg_expired'         => isset( $_POST['wcap_countdown_msg_expired'] ) ? sanitize_text_field( wp_unslash( $_POST['wcap_countdown_msg_expired'] ) ) : '', // phpcs:ignore WordPress.Security.NonceVerification
			);

			$template_name = isset( $_POST['wcap_template_name'] ) ? sanitize_text_field( wp_unslash( $_POST['wcap_template_name'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
			$match_rules   = isset( $_POST['wcap_match_rules'] ) ? sanitize_text_field( wp_unslash( $_POST['wcap_match_rules'] ) ) : 'all'; // phpcs:ignore WordPress.Security.NonceVerification

			$content = array(
				'name'              => $template_name,
				'match_rules'       => $match_rules,
				'rules'             => wp_json_encode( $rules ),
				'frontend_settings' => wp_json_encode( $frontend_settings ),
				'coupon_settings'   => wp_json_encode( $coupon_settings ),
			);

			if ( isset( $_POST['id'] ) && 0 < $_POST['id'] ) { // phpcs:ignore WordPress.Security.NonceVerification
				self::wcap_update_atc_template( sanitize_text_field( wp_unslash( $_POST['id'] ) ), $content ); // phpcs:ignore WordPress.Security.NonceVerification
			} else {
				self::wcap_insert_atc_template( $content );
			}

			// Delete the ATC cache for the front end.
			delete_option( 'wcap_atc_templates' );
			do_action( 'wcap_save_atc_settings' );
		}

		/**
		 * Update ATC template.
		 *
		 * @param int   $id - ATC Template ID.
		 * @param array $content - Template Content.
		 */
		public static function wcap_update_atc_template( $id, $content ) {
			global $wpdb;

			$wpdb->update( // phpcs:ignore
				WCAP_ATC_RULES_TABLE,
				$content,
				array(
					'id' => $id,
				)
			);
			return $id;
		}

		/**
		 * Insert new ATC Template.
		 *
		 * @param array $content - Template content.
		 */
		public static function wcap_insert_atc_template( $content ) {
			global $wpdb;

			$wpdb->insert( // phpcs:ignore
				WCAP_ATC_RULES_TABLE,
				$content
			);
			return $wpdb->insert_id;
		}
		/**
		 * It will add the setting for Heading section on the add to cart modal settings page.
		 *
		 * @param object $frontend_settings - ATC settings.
		 * @since 6.0
		 */
		public static function wcap_add_heading_section( $frontend_settings ) {
			$heading = isset( $frontend_settings->wcap_heading_section_text_email ) ? $frontend_settings->wcap_heading_section_text_email : __( 'Please enter your email.', 'woocommerce-ac' );
			?>
			<div id="wcap_heading_section_div" class="wcap_heading_section_div wcap_atc_between_fields_space">
				<table id="wcap_heading_section_table" class="wcap_heading_section_table wcap_atc_content">
					<th id="wcap_heading_section_table_heading" class="wcap_heading_section_table_heading"><?php esc_html_e( 'Modal Heading', 'woocommerce-ac' ); ?></th>
					<tr id="wcap_heading_section_tr" class="wcap_heading_section_tr" >
						<td id="wcap_heading_section_text_field" class="wcap_heading_section_text_field test_borders">
							<input type="text" id="wcap_heading_section_text_email" v-model="wcap_heading_section_text_email" name="wcap_heading_section_text_email" class = "wcap_heading_section_text_email"
							value="<?php echo esc_html( $heading ); ?>" >
						</td>            				
						<td id="wcap_heading_section_text_field_color" class="wcap_heading_section_text_field_color test_borders">
							<?php $wcap_popup_heading_color_picker = isset( $frontend_settings->wcap_popup_heading_color_picker ) ? $frontend_settings->wcap_popup_heading_color_picker : '#737f97'; ?>
							<span class = "colorpickpreview" style = "background:<?php echo esc_attr( $wcap_popup_heading_color_picker ); ?>"></span>
							<input type="text" class="wcap_popup_heading_color_picker colorpick" name="wcap_popup_heading_color_picker" value="{{wcap_popup_heading_color_picker}}" v-model="wcap_popup_heading_color" v-on:input="wcap_atc_popup_heading.color = $event.target.value" >
						</td>
					</tr>
				</table>
			</div>
			<?php
		}

		/**
		 * It will add the setting for Text displayed below heading section on the add to cart modal settings page.
		 *
		 * @param object $frontend_settings - ATC settings.
		 * @since 6.0
		 */
		public static function wcap_add_text_section( $frontend_settings ) {
			?>
			<div id="wcap_text_section_div" class="wcap_text_section_div wcap_atc_between_fields_space">
				<table id="wcap_text_section_table" class="wcap_text_section_table wcap_atc_content">
					<th id="wcap_text_section_table_heading" class="wcap_text_section_table_heading"><?php esc_html_e( 'Modal Text', 'woocommerce-ac' ); ?></th>
					<tr id="wcap_text_section_tr" class="wcap_text_section_tr" >
						<td id="wcap_text_section_text_field" class="wcap_text_section_text_field test_borders">
							<input type="text" id="wcap_text_section_text" v-model="wcap_text_section_text_field" class="wcap_text_section_input_text" name="wcap_text_section_text" >
						</td>                    		
						<td id="wcap_text_section_field_color" class="wcap_text_section_field_color test_borders">
							<?php $wcap_atc_popup_text_color = isset( $frontend_settings->wcap_popup_text_color_picker ) ? $frontend_settings->wcap_popup_text_color_picker : '#bbc9d2'; ?>
							<span class="colorpickpreview" style="background:<?php echo esc_attr( $wcap_atc_popup_text_color ); ?>"></span>
							<input type="text" class="wcap_popup_text_color_picker colorpick" name="wcap_popup_text_color_picker" value="{{wcap_popup_text_color}}" v-model="wcap_popup_text_color" v-on:input="wcap_atc_popup_text.color = $event.target.value" >
						</td>
					</tr>
				</table>
			</div>
			<?php
		}

		/**
		 * It will add the setting for email placeholder on the add to cart modal settings page.
		 *
		 * @param object $frontend_settings - ATC settings.
		 * @since 6.0
		 */
		public static function wcap_email_placeholder_section( $frontend_settings ) {
			?>
			<div id="wcap_email_placeholder_section_div" class="wcap_email_placeholder_section_div wcap_atc_between_fields_space">
				<table id="wcap_email_placeholder_section_table" class="wcap_email_placeholder_section_table wcap_atc_content">
				<th id="wcap_email_placeholder_section_table_heading" class="wcap_email_placeholder_section_table_heading"><?php esc_html_e( 'Email placeholder', 'woocommerce-ac' ); ?></th>
					<tr id="wcap_email_placeholder_section_tr" class="wcap_email_placeholder_section_tr" >
						<td id="wcap_email_placeholder_section_text_field" class="wcap_email_placeholder_section_text_field test_borders">
							<input type="text" id="wcap_email_placeholder_section_input_text" v-model="wcap_email_placeholder_section_input_text" class="wcap_email_placeholder_section_input_text" name="wcap_email_placeholder_section_input_text" >
						</td>
					</tr>
				</table>
			</div>
			<?php
		}

		/**
		 * It will add the setting for Add to cart button on the add to cart modal settings page.
		 *
		 * @param object $frontend_settings - ATC settings.
		 * @since 6.0
		 */
		public static function wcap_button_section( $frontend_settings ) {
			?>
			<div id="wcap_button_section_div" class="wcap_button_section_div wcap_atc_between_fields_space">
				<table id="wcap_button_section_table" class="wcap_button_section_table wcap_atc_content">
				<th id="wcap_button_section_table_heading" class="wcap_button_section_table_heading"><?php esc_html_e( 'Add to cart button text', 'woocommerce-ac' ); ?></th>
					<tr>
						<td id="wcap_button_section_text_field" class="wcap_button_section_text_field test_borders">
							<input type="text" id="wcap_button_section_input_text" v-model="wcap_button_section_input_text" class="wcap_button_section_input_text" name="wcap_button_section_input_text">
						</td>
					</tr>
					<tr id="wcap_button_color_section_tr" class="wcap_button_color_section_tr">
						<td id="wcap_button_color_section_text_field" class="wcap_button_color_section_text_field test_borders">
							<?php $wcap_atc_button_bg_color = isset( $frontend_settings->wcap_button_color_picker ) ? $frontend_settings->wcap_button_color_picker : '#0085ba'; ?>
							<span class="colorpickpreview" style="background:<?php echo esc_attr( $wcap_atc_button_bg_color ); ?>"></span>
							<input type="text" id="wcap_button_color_picker" value="{{wcap_button_bg_color}}" v-model="wcap_button_bg_color" v-on:input="wcap_atc_button.backgroundColor = $event.target.value" class="wcap_button_color_picker colorpick" name="wcap_button_color_picker">
						</td>
						<td id="wcap_button_text_color_section_text_field" class="wcap_button_text_color_section_text_field test_borders">
							<?php $wcap_button_text_color_picker = isset( $frontend_settings->wcap_button_text_color_picker ) ? $frontend_settings->wcap_button_text_color_picker : '#ffffff'; ?>
							<span class="colorpickpreview" style="background:<?php echo esc_attr( $wcap_button_text_color_picker ); ?>"></span>
							<input type="text" id="wcap_button_text_color_picker" value= "{{wcap_button_text_color}}" v-model="wcap_button_text_color" v-on:input="wcap_atc_button.color = $event.target.value" class="wcap_button_text_color_picker colorpick" name="wcap_button_text_color_picker" >
						</td>
					</tr>
				</table>
			</div>
			<?php
		}

		/**
		 * It will add the setting for Email address mandatory field on the add to cart modal settings page.
		 *
		 * @param object $frontend_settings - ATC settings.
		 * @since 6.0
		 */
		public static function wcap_mandatory_modal_section( $frontend_settings ) {
			?>
			<table class="wcap_atc_between_fields_space wcap_atc_content">
				<th id="wcap_button_section_table_heading" class="wcap_button_section_table_heading"><?php esc_html_e( 'Email address is mandatory?', 'woocommerce-ac' ); ?></th>
				<tr>
					<td>
						<?php
						$wcap_atc_email_mandatory = isset( $frontend_settings->wcap_atc_mandatory_email ) ? $frontend_settings->wcap_atc_mandatory_email : 'off';
						$active_text              = __( $wcap_atc_email_mandatory, 'woocommerce-ac' ); // phpcS:ignore
						?>
						<button type="button" class="wcap-switch-atc-modal-mandatory wcap-toggle-atc-modal-mandatory" wcap-atc-switch-modal-mandatory="<?php echo esc_attr( $wcap_atc_email_mandatory ); ?>" 
						onClick="wcap_button_choice( this, 'wcap-atc-switch-modal-mandatory' )">
						<?php echo esc_attr( $active_text ); ?> </button>
						<input type="hidden" name="wcap_switch_atc_modal_mandatory" id="wcap_switch_atc_modal_mandatory" value="<?php echo esc_attr( $wcap_atc_email_mandatory ); ?>" />
					</td>
				</tr>
			</table>
			<?php
		}

		/**
		 * It will add the setting for Email address non mandatory field on the add to cart modal settings page.
		 *
		 * @param object $frontend_settings - ATC settings.
		 * @since 6.0
		 */
		public static function wcap_non_mandatory_modal_section_field( $frontend_settings ) {
			$wcap_get_mandatory_field  = isset( $frontend_settings->wcap_atc_mandatory_email ) ? $frontend_settings->wcap_atc_mandatory_email : '';
			$wcap_disabled_email_field = '';
			if ( 'on' === $wcap_get_mandatory_field ) {
				$wcap_disabled_email_field = 'disabled="disabled"';
			}
			?>
			<div id="wcap_non_mandatory_modal_section_fields_div" class="wcap_non_mandatory_modal_section_fields_div wcap_atc_between_fields_space">
				<table id="wcap_non_mandatory_modal_section_fields_div_table" class="wcap_non_mandatory_modal_section_fields_div_table wcap_atc_content">
					<th id="wcap_non_mandatory_modal_section_fields_table_heading" 
					class="wcap_non_mandatory_modal_section_fields_table_heading"><?php esc_html_e( 'Not mandatory text', 'woocommerce-ac' ); ?></th>
					<tr id="wcap_non_mandatory_modal_section_fields_tr" class="wcap_non_mandatory_modal_section_fields_tr" >
						<td id="wcap_non_mandatory_modal_section_fields_text_field" class="wcap_non_mandatory_modal_section_fields_text_field test_borders">
							<input type="text" id="wcap_non_mandatory_modal_section_fields_input_text" v-model="wcap_non_mandatory_modal_input_text" class="wcap_non_mandatory_modal_section_fields_input_text" name="wcap_non_mandatory_modal_section_fields_input_text" 
							<?php
							echo esc_attr( $wcap_disabled_email_field );
							?>
							>
						</td>
					</tr>
				</table>
			</div>
			<?php
		}

		/**
		 * It will add the setting for Phone capture on the add to cart modal settings page.
		 *
		 * @param object $frontend_settings - ATC settings.
		 * @since 8.12.0
		 */
		public static function wcap_capture_phone( $frontend_settings ) {
			?>
			<table class="wcap_atc_between_fields_space wcap_atc_content">
				<th id="wcap_button_section_table_heading" class="wcap_button_section_table_heading"><?php esc_html_e( 'Capture Phone', 'woocommerce-ac' ); ?></th>
				<tr>
					<td>
						<?php
						$wcap_atc_capture_phone = isset( $frontend_settings->wcap_atc_capture_phone ) ? $frontend_settings->wcap_atc_capture_phone : 'off';
						$active_text            = __( $wcap_atc_capture_phone, 'woocommerce-ac' ); // phpcS:ignore
						?>
						<button type="button" class="wcap-switch-atc-capture-phone wcap-toggle-atc-capture-phone" wcap-atc-capture-phone="<?php echo esc_attr( $wcap_atc_capture_phone ); ?>" 
						onClick="wcap_button_choice( this, 'wcap-atc-capture-phone' )">
						<?php echo esc_attr( $active_text ); ?> </button>
						<input type="hidden" name="wcap_switch_atc_capture_phone" id="wcap_switch_atc_capture_phone" value="<?php echo esc_attr( $wcap_atc_capture_phone ); ?>" />
					</td>
				</tr>
			</table>
			<?php
		}

		/**
		 * It will add the setting for Phone field placeholder on the add to cart modal settings page.
		 *
		 * @param object $frontend_settings - ATC settings.
		 * @since 8.12.0
		 */
		public static function wcap_phone_placeholder_section( $frontend_settings ) {
			$phone_placeholder = isset( $frontend_settings->wcap_atc_phone_placeholder ) ? $frontend_settings->wcap_atc_phone_placeholder : 'Please enter your phone number in E.164 format';
			?>
			<div id="wcap_phone_placeholder_section_div" class="wcap_phone_placeholder_section_div wcap_atc_between_fields_space">
				<table id="wcap_phone_placeholder_section_table" class="wcap_phone_placeholder_section_table wcap_atc_content">
				<th id="wcap_phone_placeholder_section_table_heading" class="wcap_phone_placeholder_section_table_heading"><?php esc_html_e( 'Phone placeholder', 'woocommerce-ac' ); ?></th>
					<tr id="wcap_phone_placeholder_section_tr" class="wcap_phone_placeholder_section_tr" >
						<td id="wcap_phone_placeholder_section_text_field" class="wcap_phone_placeholder_section_text_field test_borders">
							<input type="text" id="wcap_phone_placeholder_section_input_text" v-model="wcap_phone_placeholder_section_input_text" class="wcap_phone_placeholder_section_input_text" name="wcap_phone_placeholder_section_input_text" value="<?php esc_attr_e( $phone_placeholder ); ?>" >
						</td>
					</tr>
				</table>
			</div>
			<?php
		}

		/**
		 * Auto Apply coupons for atc settings.
		 *
		 * @param object $coupon_settings - Coupon settings.
		 * @since 8.5.0
		 */
		public static function wcap_coupon_section( $coupon_settings ) {
			$auto_apply_coupon = isset( $coupon_settings->wcap_atc_auto_apply_coupon_enabled ) ? $coupon_settings->wcap_atc_auto_apply_coupon_enabled : 'off';
			$active_text       = __( $auto_apply_coupon, 'woocommerce-ac' ); // phpcs:ignore

			$wcap_atc_coupon_type = isset( $coupon_settings->wcap_atc_coupon_type ) ? $coupon_settings->wcap_atc_coupon_type : '';
			$pre_selected         = 'pre-selected' === $wcap_atc_coupon_type || '' === $wcap_atc_coupon_type ? 'selected' : '';
			$unique               = 'unique' === $wcap_atc_coupon_type ? 'selected' : '';

			$coupon_code_id = isset( $coupon_settings->wcap_atc_popup_coupon ) ? $coupon_settings->wcap_atc_popup_coupon : 0;

			$wcap_atc_discount_type = isset( $coupon_settings->wcap_atc_discount_type ) ? $coupon_settings->wcap_atc_discount_type : '';
			$percent_discount       = 'percent' === $wcap_atc_discount_type || '' === $wcap_atc_discount_type ? 'selected' : '';
			$amount_discount        = 'amount' === $wcap_atc_discount_type ? 'selected' : '';

			$wcap_atc_discount_amount      = isset( $coupon_settings->wcap_atc_discount_amount ) ? $coupon_settings->wcap_atc_discount_amount : '';
			$wcap_atc_coupon_free_shipping = isset( $coupon_settings->wcap_atc_coupon_free_shipping ) ? $coupon_settings->wcap_atc_coupon_free_shipping : '';
			$free_shipping_enabled         = 'on' === $wcap_atc_coupon_free_shipping ? 'checked' : '';

			$coupon_validity       = isset( $coupon_settings->wcap_atc_popup_coupon_validity ) ? $coupon_settings->wcap_atc_popup_coupon_validity : '';
			$countdown_msg         = isset( $coupon_settings->wcap_countdown_timer_msg ) ? htmlspecialchars_decode( $coupon_settings->wcap_countdown_timer_msg ) : htmlspecialchars_decode( 'Coupon <coupon_code> expires in <hh:mm:ss>. Avail it now.' );
			$countdown_msg_expired = isset( $coupon_settings->wcap_countdown_msg_expired ) ? $coupon_settings->wcap_countdown_msg_expired : 'The offer is no longer valid.';
			$countdown_cart        = isset( $coupon_settings->wcap_countdown_cart ) ? $coupon_settings->wcap_countdown_cart : 'on';
			$active_cart           = __( $countdown_cart, 'woocommerce-ac' ); // phpcs:ignore
			?>
			<div id='wcap_coupon_settings'>
				<table id='wcap_coupon_settings_div_table' class='wcap_coupon_settings_div_table wcap_atc_content'>
					<th id='wcap_auto_apply_coupons_heading' class='wcap_auto_apply_coupons_heading'><?php esc_html_e( 'Auto apply coupons on email address capture:', 'woocommerce-ac' ); ?></th>
					<tr>
						<td>
							<button type="button" class="wcap-auto-apply-coupons-atc wcap-toggle-auto-apply-coupons-status" wcap-atc-switch-coupon-enable = "<?php echo esc_attr( $auto_apply_coupon ); ?>"
							onClick="wcap_button_choice( this, 'wcap-atc-switch-coupon-enable' )">
							<?php echo esc_attr( $active_text ); ?></button>
							<input type="hidden" name="wcap_auto_apply_coupons_atc" id="wcap_auto_apply_coupons_atc" value="<?php echo esc_attr( $auto_apply_coupon ); ?>" />
						</td>
					</tr>
					<th id='wcap_atc_coupon_type_label' class='wcap_atc_coupon_type_label'><?php esc_html_e( 'Type of Coupon to apply:', 'woocommerce-ac' ); ?></th>
					<tr>
						<td>
							<select id='wcap_atc_coupon_type' name='wcap_atc_coupon_type'>
								<option value='pre-selected' <?php echo esc_html( $pre_selected ); ?>><?php esc_html_e( 'Existing Coupons', 'woocommerce-ac' ); ?></option>
								<option value='unique' <?php echo esc_html( $unique ); ?>><?php esc_html_e( 'Generate Unique Coupon code', 'woocommerce-ac' ); ?></option>
							</select>
						</td>
					</tr>
					<th id='wcap_auto_apply_coupon_id' class='wcap_auto_apply_coupon_id wcap_atc_pre_selected'><?php esc_html_e( 'Coupon code to apply:', 'woocommerce-ac' ); ?></th>
					<tr class='wcap_atc_pre_selected'>
						<td>
							<div id="coupon_options" class="panel">
								<div class="options_group">
									<p class="form-field" style="padding-left:0px !important;">
									<?php
										$json_ids = array();

									if ( $coupon_code_id > 0 ) {
										$coupon                      = get_the_title( $coupon_code_id );
										$json_ids[ $coupon_code_id ] = $coupon;
									}
									if ( version_compare( WC()->version, '3.0.0', '>=' ) ) {
										?>
											<select id="coupon_ids" name="coupon_ids[]" class="wc-product-search" multiple="multiple" style="width: 37%;" data-placeholder="<?php esc_attr_e( 'Search for a Coupon&hellip;', 'woocommerce' ); ?>" data-action="wcap_json_find_coupons" >
										<?php
										if ( $coupon_code_id > 0 ) {
											$coupon = get_the_title( $coupon_code_id );
											echo '<option value="' . esc_attr( $coupon_code_id ) . '"' . selected( true, true, false ) . '>' . wp_kses_post( $coupon ) . '</option>';
										}
										?>
											</select>
										<?php
									} else {
										?>
										<input type="hidden" id="coupon_ids" name="coupon_ids[]" class="wc-product-search" style="width: 30%;" data-placeholder="<?php esc_attr_e( 'Search for a Coupon&hellip;', 'woocommerce' ); ?>" data-multiple="true" data-action="wcap_json_find_coupons"
										data-selected=" <?php echo esc_attr( wp_json_encode( $json_ids ) ); ?> " value="<?php echo esc_html( implode( ',', array_keys( $json_ids ) ) ); ?>" />
										<?php
									}
									?>

									</p>
								</div>
							</div>
						</td>
					</tr>
					<th id='wcap_atc_discount_type_label' class='wcap_atc_discount_type_label wcap_atc_unique'><?php esc_html_e( 'Discount Type:', 'woocommerce-ac' ); ?></th>
					<tr class='wcap_atc_unique'>
						<td>
							<select id='wcap_atc_discount_type' name='wcap_atc_discount_type'>
								<option value='percent' <?php echo esc_html( $percent_discount ); ?>><?php esc_html_e( 'Percentage Discount', 'woocommerce-ac' ); ?></option>
								<option value='amount' <?php echo esc_html( $amount_discount ); ?>><?php esc_html_e( 'Fixed Cart Amount', 'woocommerce-ac' ); ?></option>
							</select>
						</td>
					</tr>
					<th id='wcap_atc_discount_amount_label' class='wcap_atc_discount_amount_label wcap_atc_unique'><?php esc_html_e( 'Discount Amount:', 'woocommerce-ac' ); ?></th>
					<tr class='wcap_atc_unique'>
						<td>
							<input type='number' id='wcap_atc_discount_amount' name='wcap_atc_discount_amount' min='0' value='<?php echo esc_html( $wcap_atc_discount_amount ); ?>' />
						</td>
					</tr>
					<th id='wcap_atc_coupon_free_shipping_label' class='wcap_atc_coupon_free_shipping_label wcap_atc_unique'><?php esc_html_e( 'Allow Free Shipping?', 'woocommerce-ac' ); ?></th>
					<tr class='wcap_atc_unique'>
						<td>
							<input type='checkbox' id='wcap_atc_coupon_free_shipping' name='wcap_atc_coupon_free_shipping' <?php echo esc_attr( $free_shipping_enabled ); ?> />
						</td>
					</tr>		
					<th id='wcap_atc_coupon_validity_label' class='wcap_atc_coupon_validity_label'><?php esc_html_e( 'Coupon validity (in minutes):', 'woocommerce-ac' ); ?></th>
					<tr>
						<td>
							<input type='number' id='wcap_atc_coupon_validity' name='wcap_atc_coupon_validity' min='0' value='<?php echo esc_attr( $coupon_validity ); ?>' />
						</td>
					</tr>
					<th id='countdown_timer_cart_label' class='countdown_timer_cart_label'><?php esc_html_e( 'Display Urgency message on Cart page (If disabled it will display only on Checkout page)', 'woocommerce-ac' ); ?></th>
					<tr>
						<td>
							<button type="button" class="wcap-countdown-timer-cart wcap-toggle-countdown-timer-cart" wcap-atc-countdown-timer-cart-enable = <?php echo esc_attr( $countdown_cart ); ?> 
							onClick="wcap_button_choice( this, 'wcap-atc-countdown-timer-cart-enable' )">
							<?php echo esc_attr( $active_text ); ?></button>
							<input type="hidden" name="wcap_countdown_timer_cart" id="wcap_countdown_timer_cart" value="<?php echo esc_attr( $countdown_cart ); ?>" />
						</td>
					</tr>
					<th id='wcap_countdown_msg_label' class='wcap_countdown_msg_label'><?php esc_html_e( 'Urgency message to boost your conversions', 'woocommerce-ac' ); ?></th>
					<tr>
						<td>
							<input type='text' id='wcap_countdown_msg' name='wcap_countdown_msg' placeholder='<?php echo esc_attr( 'Coupon <coupon_code> expires in <hh:mm:ss>. Avail it now.' ); ?>' value='<?php echo esc_attr( $countdown_msg ); ?>' />
							<br>
							<i><?php echo esc_html_e( 'Merge tags available: <coupon_code>, <hh:mm:ss>', 'woocommerce-ac' ); ?></i>
						</td>
					</tr>
					<th id='wcap_countdown_msg_label' class='wcap_countdown_msg_expired_label'><?php esc_html_e( 'Message to display after coupon validity is reached', 'woocommerce-ac' ); ?></th>
					<tr>
						<td>
							<input type='text' id='wcap_countdown_msg_expired' name='wcap_countdown_msg_expired' placeholder='<?php echo esc_attr( 'The offer is no longer valid.' ); ?>' value='<?php echo esc_attr( $countdown_msg_expired ); ?>' />
							<br>
						</td>
					</tr>
					<th id='wcap_atc_coupon_note' class='wcap_atc_coupon_note'><i><?php esc_html_e( 'Note: For orders which use the coupon selected/generated by the ATC module will be marked as "ATC Coupon Used" in WooCommerce->Orders.', 'woocommerce-ac' ); ?></i></th>
					<tr></tr>
				</table>
			</div>
			<?php
		}

		/**
		 * It will will show th preview of the Add To cart Popup modal with the changes made on any of the settings for it.
		 *
		 * @since 6.0
		 */
		public static function wcap_add_to_cart_popup_modal_preview( $frontend_settings ) {
			$wcap_atc_capture_phone = isset( $frontend_settings->wcap_atc_capture_phone ) ? $frontend_settings->wcap_atc_capture_phone : 'off';
			$display_phone = 'on' === $wcap_atc_capture_phone ? 'display: block;' : 'display: none;';
			?>
			<div class = "wcap_container">
				<div class = "wcap_popup_wrapper">
					<div class = "wcap_popup_content">
						<div class = "wcap_popup_heading_container">
							<div class = "wcap_popup_icon_container" >
								<span class = "wcap_popup_icon"  >
									<span class = "wcap_popup_plus_sign" v-bind:style = "wcap_atc_button">
									</span>
								</span>
							</div>
							<div class = "wcap_popup_text_container">
								<h2 class = "wcap_popup_heading" v-bind:style = "wcap_atc_popup_heading" >{{wcap_heading_section_text_email}}</h2>
								<div class = "wcap_popup_text" v-bind:style = "wcap_atc_popup_text" >{{wcap_text_section_text_field}}</div>
							</div>
						</div>
						<div class = "wcap_popup_form">
							<form action = "" name = "wcap_modal_form">
								<div class = "wcap_popup_input_field_container"  >
									<input class = "wcap_popup_input" type = "text" value = "" name = "email" placeholder = {{wcap_email_placeholder_section_input_text}} readonly >
								</div>
                    			<div class="wcap_popup_input_field_container atc_phone_field" style="<?php echo esc_attr( $display_phone ); ?> " >
                        			<input id="wcap_atc_phone" class="wcap_popup_input" type="text" name="wcap_atc_phone" placeholder= {{wcap_phone_placeholder_section_input_text}} readonly />
                    			</div>
								<button class = "wcap_popup_button" v-bind:style = "wcap_atc_button">{{wcap_button_section_input_text}}</button>
								<br>
								<br>
								<div id = "wcap_non_mandatory_text_wrapper" class = "wcap_non_mandatory_text_wrapper">
									<a class = "wcap_popup_non_mandatory_button" href = "" > {{wcap_non_mandatory_modal_input_text}}</a>
								</div>
							</form>
						</div>
						<div class = "wcap_popup_close" ></div>
					</div>
				</div>
			</div>
			<?php
		}
	}
}
