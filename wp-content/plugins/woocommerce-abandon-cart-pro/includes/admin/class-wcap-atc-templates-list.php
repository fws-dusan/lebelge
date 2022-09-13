<?php
/**
 * It will display the email template listing.
 *
 * @author   Tyche Softwares
 * @package Abandoned-Cart-Pro-for-WooCommerce/Admin/Template
 * @since 5.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Wcap_ATC_Templates_List' ) ) {
	/**
	 * It will display the email template listing, also it will add, update & delete the email template in the database.
	 *
	 * @since 5.0
	 */
	class Wcap_ATC_Templates_List {

		/**
		 * It will display the email template listing, also it will add, update & delete the email template in the database.
		 *
		 * @param string $wcap_action Action name.
		 * @param string $wcap_section Section Name.
		 * @param string $wcap_mode Mode name.
		 * @globals mixed $wpdb
		 * @globals mixed $woocommerce
		 * @since 5.0
		 */
		public static function wcap_display_atc_template_list( $wcap_action, $wcap_section, $wcap_mode ) {
			global $woocommerce, $wpdb;
			?>
			<p>
				<?php esc_html_e( 'Add different Add to Cart popup templates for different pages to maximize the possibility of collecting email addresses from users.', 'woocommerce-ac' ); ?>
			</p>
			<?php
			$wcap_action  = isset( $_GET['action'] ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
			$wcap_section = isset( $_GET['wcap_section'] ) ? sanitize_text_field( wp_unslash( $_GET['wcap_section'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
			$mode         = isset( $_GET['mode'] ) ? sanitize_text_field( wp_unslash( $_GET['mode'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification

			if ( isset( $_POST['atc_settings_frm'] ) && in_array( $_POST['atc_settings_frm'], array( 'save', 'update' ), true ) ) { // phpcs:ignore WordPress.Security.NonceVerification
				$update_id = Wcap_Add_Cart_Popup_Modal::wcap_add_to_cart_popup_save_settings();
				Wcap_Display_Notices::wcap_add_to_cart_popup_save_success();
			}

			if ( 'emailsettings' === $wcap_action && 'wcap_atc_settings' === $wcap_section && 'deleteatctemplate' === $wcap_mode ) { // phpcs:ignore WordPress.Security.NonceVerification
				$id = isset( $_GET['id'] ) && '' !== $_GET['id'] ? sanitize_text_field( wp_unslash( $_GET['id'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification
				if ( $id > 0 ) {
					$wpdb->delete( //phpcs:ignore
						WCAP_ATC_RULES_TABLE,
						array(
							'id' => $id,
						)
					);
					Wcap_Display_Notices::wcap_display_notice( 'wcap_template_deleted' );
				}
			}

			if ( isset( $_POST['atc_settings_frm'] ) && '' !== $_POST['atc_settings_frm'] && ( isset( $update_id ) && $update_id > 0 ) ) { // phpcs:ignore WordPress.Security.NonceVerification
				Wcap_Display_Notices::wcap_template_save_success();
			} elseif ( ( isset( $update_id ) && '' === $update_id ) && isset( $_POST['ac_settings_frm'] ) && '' !== $_POST['ac_settings_frm'] ) { // phpcs:ignore WordPress.Security.NonceVerification
				Wcap_Display_Notices::wcap_template_save_error();
			}

			?>
			<div>
				<p>
					<a cursor: pointer; href="<?php echo 'admin.php?page=woocommerce_ac_page&action=emailsettings&wcap_section=wcap_atc_settings&mode=addnewtemplate'; ?>" class="button-secondary"><?php esc_html_e( 'Add New Template', 'woocommerce-ac' ); ?></a>
				</p>
				<?php

				// From here you can do whatever you want with the data from the $result link.
				$wcap_atc_template_list = new Wcap_ATC_Templates_Table();
				$wcap_atc_template_list->wcap_templates_prepare_items();
				?>
				<div class="wrap">
					<form id="wcap-abandoned-templates" method="get" >
						<input type="hidden" name="page" value="woocommerce_ac_page" />
						<input type="hidden" name="action" value="emailsettings" />
						<input type="hidden" name="wcap_action" value="emailsettings" />
						<input type="hidden" name="section" value="wcap_atc_settings" />
						<input type="hidden" name="wcap_section" value="wcap_atc_settings" />
						<?php $wcap_atc_template_list->display(); ?>
					</form>
				</div>
				<p>
					<strong><i>
						<?php esc_html_e( 'Email Captured: ', 'woocommercer-ac' ); ?>
					<i></strong>
					<?php esc_html_e( 'Number of email addresses captured using the Add to Cart template.', 'woocommerce-ac' ); ?>
				</p>
				<p>
					<strong><i>
						<?php esc_html_e( 'Viewed: ', 'woocommerce-ac' ); ?>
					</i></strong>
					<?php esc_html_e( 'Number of times the popup was displayed when Add to Cart button is clicked.', 'woocommerce-ac' ); ?>
				</p>
				<p>
					<strong><i>
						<?php esc_html_e( 'No Thanks: ', 'woocommerce-ac' ); ?>
					</i></strong>
					<?php esc_html_e( 'Number of times the user chose to not give their email address in the template.', 'woocommerce-ac' ); ?>
				</p>
			</div>
			<?php
		}

		/**
		 * It will save the new created email templates.
		 *
		 * @return true | false $insert_template_successfuly_pro If template inserted successfully
		 * @since 5.0
		 */
		public static function wcap_save_email_template() {

			$rules               = self::wcap_rules();
			$coupon_code_options = self::wcap_coupon_options();
			$is_wc_template      = ( empty( $_POST['is_wc_template'] ) ) ? '0' : '1'; // phpcs:ignore WordPress.Security.NonceVerification
			$unique_coupon       = ( empty( $_POST['unique_coupon'] ) ) ? '0' : '1'; // phpcs:ignore WordPress.Security.NonceVerification

			$coupon_code_id = isset( $_POST['coupon_ids'][0] ) ? isset( $_POST['coupon_ids'][0] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification

			$subject       = isset( $_POST['woocommerce_ac_email_subject'] ) ? stripslashes( sanitize_text_field( wp_unslash( $_POST['woocommerce_ac_email_subject'] ) ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
			$body          = isset( $_POST['woocommerce_ac_email_body'] ) ? stripslashes( $_POST['woocommerce_ac_email_body'] ) : ''; // phpcs:ignore
			$email_freq    = isset( $_POST['email_frequency'] ) ? sanitize_text_field( wp_unslash( $_POST['email_frequency'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
			$day_hour      = isset( $_POST['day_or_hour'] ) ? sanitize_text_field( wp_unslash( $_POST['day_or_hour'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
			$template_name = isset( $_POST['woocommerce_ac_template_name'] ) ? sanitize_text_field( wp_unslash( $_POST['woocommerce_ac_template_name'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
			$email_header  = isset( $_POST['wcap_wc_email_header'] ) ? sanitize_text_field( wp_unslash( $_POST['wcap_wc_email_header'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
			$match_rules   = isset( $_POST['wcap_match_rules'] ) ? sanitize_text_field( wp_unslash( $_POST['wcap_match_rules'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification

			// Create the data array.
			$content = array(
				'subject'                     => $subject,
				'body'                        => $body,
				'frequency'                   => $email_freq,
				'day_or_hour'                 => $day_hour,
				'coupon_code'                 => $coupon_code_id,
				'template_name'               => $template_name,
				'discount'                    => $coupon_code_options['coupon_amount'],
				'discount_type'               => $coupon_code_options['discount_type'],
				'discount_shipping'           => $coupon_code_options['discount_shipping'],
				'discount_expiry'             => $coupon_code_options['coupon_expiry'],
				'individual_use'              => $coupon_code_options['individual_use'],
				'generate_unique_coupon_code' => $unique_coupon,
				'is_wc_template'              => $is_wc_template,
				'wc_email_header'             => $email_header,
				'match_rules'                 => $match_rules,
				'rules'                       => $rules,
			);

			$id      = isset( $_POST['id'] ) ? sanitize_text_field( wp_unslash( $_POST['id'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification
			$content = apply_filters( 'wcap_save_email_template_data', $content, $id );
			$check   = 0;
			if ( isset( $_POST['ac_settings_frm'] ) && 'save' === sanitize_text_field( wp_unslash( $_POST['ac_settings_frm'] ) ) ) { // phpcs:ignore WordPress.Security.NonceVerification
				$content['default_template'] = '0';
				$content['is_active']        = '0';
				$content['activated_time']   = current_time( 'timestamp' ); //phpcs:ignore
				$check                       = self::wcap_insert_template( $content );
			} elseif ( isset( $_POST['ac_settings_frm'] ) && 'update' === sanitize_text_field( wp_unslash( $_POST['ac_settings_frm'] ) ) ) { // phpcs:ignore WordPress.Security.NonceVerification
				$check = self::wcap_update_template( $content );
			}
			return $check;
		}

		/**
		 * It will insert the new email template data into the database.
		 *
		 * @param array $content - Array of column names & their values to be inserted in the DB.
		 * @globals mixed $wpdb
		 * @return int | false Insert ID | false - error.
		 * @since 5.0
		 */
		public static function wcap_insert_template( $content ) {

			global $wpdb;
			$wpdb->insert( //phpcs:ignore
				WCAP_EMAIL_TEMPLATE_TABLE,
				$content
			);

			$insert_id = $wpdb->insert_id;
			return $insert_id;

		}

		/**
		 * Return the coupon settings in the template.
		 *
		 * @return $coupon_code_options - Coupon code settings.
		 */
		public static function wcap_coupon_options() {

			$coupon_expiry = '';
			if ( isset( $_POST['wcac_coupon_expiry'] ) && '' !== $_POST['wcac_coupon_expiry'] ) { // phpcs:ignore WordPress.Security.NonceVerification
				$coupon_expiry = sanitize_text_field( wp_unslash( $_POST['wcac_coupon_expiry'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
			}

			if ( isset( $_POST['expiry_day_or_hour'] ) && '' !== $_POST['expiry_day_or_hour'] ) { // phpcs:ignore WordPress.Security.NonceVerification
				$expiry_day_or_hour = sanitize_text_field( wp_unslash( $_POST['expiry_day_or_hour'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
			}
			$coupon_expiry = $coupon_expiry . '-' . $expiry_day_or_hour;

			$discount_shipping = isset( $_POST['wcap_allow_free_shipping'] ) && '' !== $_POST['wcap_allow_free_shipping'] ? 'yes' : 'off'; // phpcs:ignore WordPress.Security.NonceVerification
			$individual_use    = empty( $_POST['individual_use'] ) ? '0' : '1'; // phpcs:ignore WordPress.Security.NonceVerification
			$discount_type     = isset( $_POST['wcap_discount_type'] ) && '' !== $_POST['wcap_discount_type'] ? sanitize_text_field( wp_unslash( $_POST['wcap_discount_type'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
			$coupon_amount     = isset( $_POST['wcap_coupon_amount'] ) && '' !== $_POST['wcap_coupon_amount'] ? sanitize_text_field( wp_unslash( $_POST['wcap_coupon_amount'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification

			$coupon_code_options = array(
				'discount_type'     => $discount_type,
				'coupon_amount'     => $coupon_amount,
				'coupon_expiry'     => $coupon_expiry,
				'discount_shipping' => $discount_shipping,
				'individual_use'    => $individual_use,
			);

			return $coupon_code_options;
		}

		/**
		 * Return a json encoded array of rules for the template.
		 *
		 * @return string $rules - JSON encode $rules array.
		 * @since 8.9.0
		 */
		public static function wcap_rules() {

			$rules = array();

			foreach ( $_POST as $key => $value ) { // phpcs:ignore WordPress.Security.NonceVerification
				if ( false !== strpos( $key, 'wcap_rule_type_' ) ) {
					// Get the id.
					$id         = substr( $key, -1 );
					$rule_type  = $value;
					$rule_cond  = isset( $_POST[ "wcap_rule_condition_$id" ] ) ? sanitize_text_field( wp_unslash( $_POST[ "wcap_rule_condition_$id" ] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
					$rule_value = isset( $_POST[ "wcap_rule_value_$id" ] ) ? $_POST[ "wcap_rule_value_$id" ] : ''; // phpcs:ignore

					if ( 'send_to' === $rule_type && is_array( $rule_value ) && in_array( 'email_addresses', $rule_value, true ) ) {
						$rules[] = array(
							'rule_type'      => $rule_type,
							'rule_condition' => $rule_cond,
							'rule_value'     => $rule_value,
							'emails'         => isset( $_POST['wcap_rules_email_addresses'] ) ? sanitize_text_field( wp_unslash( $_POST['wcap_rules_email_addresses'] ) ) : '', // phpcs:ignore WordPress.Security.NonceVerification
						);
					} else {
						$rules[] = array(
							'rule_type'      => $rule_type,
							'rule_condition' => $rule_cond,
							'rule_value'     => $rule_value,
						);
					}
				}
			}

			return json_encode( $rules ); //phpcs:ignore
		}

		/**
		 * It will update email template data into the database.
		 * It will insert the post meta for the email action, it will decide who will recive this email template.
		 *
		 * @param array $content - Array of fields & their values to be updated in teh DB.
		 * @globals mixed $wpdb
		 * @return int | false Number of rows updated | false for error.
		 * @since 5.0
		 */
		public static function wcap_update_template( $content ) {
			global $wpdb;

			$id = isset( $_POST['id'] ) ? sanitize_text_field( wp_unslash( $_POST['id'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification

			if ( $id > 0 ) {
				$update_count = $wpdb->update( // phpcs:ignore
					WCAP_EMAIL_TEMPLATE_TABLE,
					$content,
					array(
						'id' => $id,
					)
				);

				return $update_count;
			}

		}
	}
}
