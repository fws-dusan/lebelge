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

if ( ! class_exists( 'Wcap_Email_Template_List' ) ) {
	/**
	 * It will display the email template listing, also it will add, update & delete the email template in the database.
	 *
	 * @since 5.0
	 */
	class Wcap_Email_Template_List {

		/**
		 * Display submenu
		 *
		 * @param string $action - $_GET - action.
		 * @param string $section - $_GET - section.
		 * @param string $mode - $_GET - mode.
		 */
		public static function wcap_display_recovery_submenu( $action, $section, $mode ) {

			$menu = array(
				'emailtemplates' => array(
					'key'      => 'emailtemplates',
					'label'    => 'Email Templates',
					'active'   => '',
					'callback' => array( 'Wcap_Email_Template_List', 'wcap_display_email_template_list' ),
					'params'   => array( $action, $section, $mode ),
				),
				'sms'            => array(
					'key'      => 'sms',
					'label'    => 'SMS Notifications',
					'active'   => '',
					'callback' => array( 'Wcap_SMS', 'display_sms_list' ),
				),
			);

			$menu = apply_filters( 'wcap_recovery_submenu', $menu );

			// Set the class value for the view to be displayed.
			$menu[ $section ]['active'] = 'current';

			?>

				<!-- Setup the views -->
				<div id="wcap_content">
					<ul class="subsubsub">

						<?php foreach ( $menu as $m_key => $m_value ) : ?>
							<li>
								<a href="admin.php?page=woocommerce_ac_page&action=cart_recovery&section=<?php echo esc_attr( $m_key ); ?>" class="<?php echo esc_attr( $m_value['active'] ); ?>"><?php esc_html_e( $m_value['label'], 'woocomerce-ac' ); ?> </a> |
							</li>
						<?php endforeach; ?>
					</ul>
					<br class="clear">
				</div>
			<?php

			// Add content for each of the views.
			if ( isset( $menu[ $section ]['params'] ) ) {
				call_user_func_array( $menu[ $section ]['callback'], $menu[ $section ]['params'] );
			} else {
				$menu[ $section ]['callback']();
			}
		}

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
		public static function wcap_display_email_template_list( $wcap_action, $wcap_section, $wcap_mode ) {
			global $woocommerce, $wpdb;
			?>
			<p>
				<?php esc_html_e( 'Add email templates at different intervals to maximize the possibility of recovering your abandoned carts.', 'woocommerce-ac' ); ?>
			</p>
			<?php
			Wcap_Common::wcap_display_date_filter( 'cart_recovery' );

			// Save the field values.
			if ( isset( $_POST['ac_settings_frm'] ) && ( 'save' === $_POST['ac_settings_frm'] || 'update' === $_POST['ac_settings_frm'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
				$update_success = self::wcap_save_email_template();
			}

			if ( 'cart_recovery' === $wcap_action && 'emailtemplates' === $wcap_section && 'removetemplate' === $wcap_mode ) { // phpcs:ignore WordPress.Security.NonceVerification
				if ( isset( $_GET['id'] ) && '' !== $_GET['id'] ) { // phpcs:ignore WordPress.Security.NonceVerification
					$id = sanitize_text_field( wp_unslash( $_GET['id'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
					$wpdb->delete( //phpcs:ignore
						WCAP_EMAIL_TEMPLATE_TABLE,
						array(
							'id' => $id,
						)
					);
				}
			}

			if ( 'cart_recovery' === $wcap_action && 'emailtemplates' === $wcap_section && 'activate_template' === $wcap_mode ) { // phpcs:ignore WordPress.Security.NonceVerification
				global $wpdb;
				$template_id             = sanitize_text_field( wp_unslash( $_GET['id'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
				$current_template_status = isset( $_GET['active_state'] ) ? sanitize_text_field( wp_unslash( $_GET['active_state'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
				$active                  = ( '1' === (string) $current_template_status ) ? '0' : '1';

				$wpdb->update( //phpcs:ignore
					WCAP_EMAIL_TEMPLATE_TABLE,
					array(
						'is_active' => $active,
					),
					array(
						'id' => $template_id,
					)
				);

				wp_safe_redirect( admin_url( '/admin.php?page=woocommerce_ac_page&action=cart_recovery&section=emailtemplates' ) );
			}

			if ( isset( $_POST['ac_settings_frm'] ) && 'save' === $_POST['ac_settings_frm'] && ( isset( $update_success ) && $update_success > 0 ) ) { // phpcs:ignore WordPress.Security.NonceVerification
				Wcap_Display_Notices::wcap_template_save_success();
			} elseif ( ( isset( $update_success ) && '' === $update_success ) && isset( $_POST['ac_settings_frm'] ) && 'save' === $_POST['ac_settings_frm'] ) { // phpcs:ignore WordPress.Security.NonceVerification
				Wcap_Display_Notices::wcap_template_save_error();
			}

			if ( isset( $update_success ) && $update_success >= 0 && isset( $_POST['ac_settings_frm'] ) && 'update' === $_POST['ac_settings_frm'] ) { // phpcs:ignore WordPress.Security.NonceVerification
				Wcap_Display_Notices::wcap_template_updated_success();
			} elseif ( isset( $update_success ) && false === $update_success && isset( $_POST['ac_settings_frm'] ) && 'update' === $_POST['ac_settings_frm'] ) { // phpcs:ignore WordPress.Security.NonceVerification
				Wcap_Display_Notices::wcap_template_updated_error();
			}
			?>
			<div>
				<p>
					<a cursor: pointer; href="<?php echo 'admin.php?page=woocommerce_ac_page&action=cart_recovery&section=emailtemplates&mode=addnewtemplate'; ?>" class="button-secondary"><?php esc_html_e( 'Add New Template', 'woocommerce-ac' ); ?></a>
				</p>
				<?php

				// From here you can do whatever you want with the data from the $result link.
				$wcap_template_list = new Wcap_Templates_Table();
				$wcap_template_list->wcap_templates_prepare_items();
				?>
				<div class="wrap">
					<form id="wcap-abandoned-templates" method="get" >
						<input type="hidden" name="page" value="woocommerce_ac_page" />
						<input type="hidden" name="action" value="cart_recovry" />
						<input type="hidden" name="wcap_action" value="cart_recovry" />
						<input type="hidden" name="section" value="emailtemplates" />
						<input type="hidden" name="wcap_section" value="emailtemplates" />
						<?php $wcap_template_list->display(); ?>
					</form>
				</div>
				<p>
					<strong><i>
						<?php esc_html_e( 'Open Rate: ', 'woocommercer-ac' ); ?>
					<i></strong>
					<?php esc_html_e( 'Number of emails opened versus number of emails sent.', 'woocommerce-ac' ); ?>
				</p>
				<p>
					<strong><i>
						<?php esc_html_e( 'Link Click Rate: ', 'woocommerce-ac' ); ?>
					</i></strong>
					<?php esc_html_e( 'Number of links clicked versus number of emails sent. In cases where coupons are present for the template, the coupon application rate will be same as Link Click Rate, since coupons are auto applied when a link is clicked.', 'woocommerce-ac' ); ?>
				</p>
				<p>
					<strong><i>
						<?php esc_html_e( 'Coupon Redemption Rate: ', 'woocommerce-ac' ); ?>
					</i></strong>
					<?php esc_html_e( 'Number of Coupons applied (i.e. number of links clicked) versus number of emails opened.', 'woocommerce-ac' ); ?>
				</p>
				<p>
					<strong><i>
						<?php esc_html_e( 'Conversion Rate: ', 'woocommerce-ac' ); ?>
					</i></strong>
					<?php esc_html_e( 'Number of carts recovered versus number of emails sent.', 'woocommerce-ac' ); ?>
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

			$coupon_code_id = isset( $_POST['coupon_ids'][0] ) ? sanitize_text_field( wp_unslash( $_POST['coupon_ids'][0] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification

			$subject       = isset( $_POST['woocommerce_ac_email_subject'] ) ? stripslashes( sanitize_text_field( wp_unslash( $_POST['woocommerce_ac_email_subject'] ) ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
			$body          = isset( $_POST['woocommerce_ac_email_body'] ) ? stripslashes( $_POST['woocommerce_ac_email_body'] ) : ''; // phpcs:ignore
			$email_freq    = isset( $_POST['email_frequency'] ) ? sanitize_text_field( wp_unslash( $_POST['email_frequency'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
			$day_hour      = isset( $_POST['day_or_hour'] ) ? sanitize_text_field( wp_unslash( $_POST['day_or_hour'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
			$template_name = isset( $_POST['woocommerce_ac_template_name'] ) ? sanitize_text_field( wp_unslash( $_POST['woocommerce_ac_template_name'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
			$email_header  = isset( $_POST['wcap_wc_email_header'] ) ? sanitize_text_field( wp_unslash( $_POST['wcap_wc_email_header'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
			$match_rules   = isset( $_POST['wcap_match_rules'] ) ? sanitize_text_field( wp_unslash( $_POST['wcap_match_rules'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification

			// If the link merge tags are prefixed with http://|https:// please remove it.
			$rectify_links = array(
				'http://{{cart.link}}',
				'https://{{cart.link}}',
				'http://{{checkout.link}}',
				'https://{{checkout.link}}',
				'http://{{cart.unsubscribe}}',
				'https://{{cart.unsubscribe}}',
				'http://{{shop.url}}',
				'https://{{shop.url}}',
			);
			foreach ( $rectify_links as $merge_tag ) {
				$start_tag = stripos( $merge_tag, '{{' );
				$new_tag   = substr( $merge_tag, $start_tag );
				$body      = str_ireplace( $merge_tag, $new_tag, $body );
			}
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

			$id = isset( $_POST['id'] ) ? sanitize_text_field( wp_unslash( $_POST['id'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification
			$content = apply_filters( 'wcap_save_email_template_data', $content, $id );
			$check = 0;
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
					$rule_value = isset( $_POST[ "wcap_rule_value_$id" ] ) ? $_POST[ "wcap_rule_value_$id" ] : ''; // phpcs:ignore WordPress.Security.NonceVerification

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
