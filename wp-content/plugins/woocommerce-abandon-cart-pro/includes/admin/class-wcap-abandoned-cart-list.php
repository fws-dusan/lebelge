<?php
/**
 * Display the Abandoned carts list.
 *
 * @author  Tyche Softwares
 * @package Abandoned-Cart-Pro-for-WooCommerce/Admin/Tab
 * @since 5.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Wcap_Abandoned_Cart_List' ) ) {
	/**
	 * Display the Abandoned carts list.
	 *
	 * @since 5.0
	 */
	class Wcap_Abandoned_Cart_List {

		/**
		 * This function will show the abandoned cart list.
		 * It will show the all views of the abandoned cart list tab.
		 * It will also add the Print & CSV buttons.
		 *
		 * @globals mixed $wpdb
		 * @globals mixed $woocommerce
		 * @since 5.0
		 */
		public static function wcap_display_abandoned_cart_list() {
			global $woocommerce, $wpdb;
			$duration_range = '';
			if ( isset( $_POST['duration_select'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
				$duration_range       = sanitize_text_field( wp_unslash( $_POST['duration_select'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
				$_SESSION['duration'] = $duration_range;
			}
			if ( '' === $duration_range && isset( $_GET['duration_select'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
				$duration_range       = sanitize_text_field( wp_unslash( $_GET['duration_select'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
				$_SESSION['duration'] = $duration_range;
			}
			if ( isset( $_SESSION ['duration'] ) && '' !== $_SESSION ['duration'] ) {
				$duration_range = $_SESSION ['duration'];
			}
			if ( '' === $duration_range ) {
				$duration_range       = 'last_seven';
				$_SESSION['duration'] = $duration_range;
			}
			$wcap_ac_class = new Woocommerce_Abandon_Cart();

			$section               = isset( $_GET['wcap_section'] ) ? sanitize_text_field( wp_unslash( $_GET['wcap_section'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
			$trash_all             = isset( $_GET['wcap-trash-all'] ) ? sanitize_text_field( wp_unslash( $_GET['wcap-trash-all'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
			$display_trash_success = false;
			if ( 'wcap_trash_abandoned' === $section && 'wcap-trash-all' === $trash_all ) {
				$trash_deleted = $wpdb->delete( // phpcs:ignore
					WCAP_ABANDONED_CART_HISTORY_TABLE,
					array(
						'wcap_trash' => '1',
					)
				);

				$display_trash_success = true;
				$section               = 'wcap_all_abandoned';
				$_GET['wcap_section']  = 'wcap_all_abandoned';
			}
			?>
			<p>
				<?php esc_html_e( 'The list below shows all the carts that were abandoned and subsequently recovered. ', 'woocommerce-ac' ); ?>
			</p>
			<div id="abandoned_stats_date" class="postbox" style="display:block">
				<div class="inside">
					<form method="post" action="<?php echo esc_attr( "admin.php?page=woocommerce_ac_page&action=listcart&section=$section" ); ?>" id="ac_stats">
						<select id="duration_select" name="duration_select" >
							<?php
							foreach ( $wcap_ac_class->duration_range_select as $key => $value ) {
								$sel = '';
								if ( $key === $duration_range ) {
									$sel = __( ' selected ', 'woocommerce-ac' );
								}
								printf(
									"<option value='%s' %s>%s</option>",
									esc_html( $key ),
									esc_attr( $sel ),
									esc_html__( $value, 'woocommerce-ac' ) // phpcs:ignore
								);
							}
							$date_sett = $wcap_ac_class->start_end_dates[ $duration_range ];
							?>
						</select>

						<?php
						$start_date_range = '';
						if ( isset( $_POST['start_date'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
							$start_date_range        = sanitize_text_field( wp_unslash( $_POST['start_date'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
							$_SESSION ['start_date'] = $start_date_range;
						}

						if ( isset( $_SESSION ['start_date'] ) && '' !== $_SESSION ['start_date'] ) {
							$start_date_range = $_SESSION ['start_date'];
						}
						if ( '' === $start_date_range ) {
							$start_date_range        = $date_sett['start_date'];
							$_SESSION ['start_date'] = $start_date_range;
						}
						$end_date_range = '';
						if ( isset( $_POST['end_date'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
							$end_date_range        = sanitize_text_field( wp_unslash( $_POST['end_date'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
							$_SESSION ['end_date'] = $end_date_range;
						}
						if ( isset( $_SESSION ['end_date'] ) && '' !== $_SESSION ['end_date'] ) {
							$end_date_range = $_SESSION ['end_date'];
						}
						if ( '' === $end_date_range ) {
							$end_date_range        = $date_sett['end_date'];
							$_SESSION ['end_date'] = $end_date_range;
						}
						$hidden_start = '';
						$hidden_end   = '';
						if ( isset( $_POST['hidden_start'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
							$hidden_start              = sanitize_text_field( wp_unslash( $_POST['hidden_start'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
							$_SESSION ['hidden_start'] = $hidden_start;
						}
						if ( isset( $_SESSION['hidden_start'] ) ) {
							$hidden_start = $_SESSION['hidden_start'];
						}

						if ( isset( $_POST['hidden_end'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
							$hidden_end              = sanitize_text_field( wp_unslash( $_POST['hidden_end'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
							$_SESSION ['hidden_end'] = $hidden_end;
						}
						if ( isset( $_SESSION['hidden_end'] ) ) {
							$hidden_end = $_SESSION['hidden_end'];
						}

						if ( '' === $hidden_start && '' === $hidden_end ) {
							$hidden_dates = Wcap_Common::wcap_get_dates_from_range( $duration_range );
							$hidden_start = $hidden_dates['start_date'];
							$hidden_end   = $hidden_dates['end_date'];
						}
						$valid_statuses = array(
							'all'       => __( 'All', 'woocommerce-ac' ),
							'abandoned' => __( 'Abandoned', 'woocommerce-ac' ),
							'recovered' => __( 'Recovered', 'woocommerce-ac' ),
							'received'  => __( 'Abandoned - Order Received', 'woocommerce-ac' ),
							'unpaid'    => __( 'Abandoned - Pending Payment', 'woocommerce-ac' ),
							'cancelled' => __( 'Abandoned - Order Cancelled', 'woocommerce-ac' ),
						);

						$filtered_status = 'all';
						if ( isset( $_POST['cart_status'] ) && '' !== $_POST['cart_status'] ) { // phpcs:ignore WordPress.Security.NonceVerification
							$filtered_status         = sanitize_text_field( wp_unslash( $_POST['cart_status'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
							$_SESSION['cart_status'] = $filtered_status;
						} elseif ( isset( $_SESSION['cart_status'] ) && '' !== $_SESSION['cart_status'] ) { // phpcs:ignore WordPress.Security.NonceVerification
							$filtered_status = sanitize_text_field( wp_unslash( $_SESSION['cart_status'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
						}
						?>
						<label class="start_label" for="start_day"> <?php esc_html_e( 'Start Date:', 'woocommerce-ac' ); ?> </label>
						<input type="text" id="start_date" name="start_date" readonly="readonly" value="<?php echo esc_attr( $start_date_range ); ?>"/>
						<input type='hidden' id='hidden_start' name='hidden_start' value='<?php echo esc_attr( $hidden_start ); ?>' />
						<label class="end_label" for="end_day"> <?php esc_html_e( 'End Date:', 'woocommerce-ac' ); ?> </label>
						<input type="text" id="end_date" name="end_date" readonly="readonly" value="<?php echo esc_attr( $end_date_range ); ?>"/>
						<input type='hidden' id='hidden_end' name='hidden_end' value='<?php echo esc_attr( $hidden_end ); ?>' />
						&nbsp;
						<select id='cart_status' name='cart_status'>
							<?php
							foreach ( $valid_statuses as $key => $name ) {
								$selected = $filtered_status === $key ? 'selected' : '';
								printf(
									'<option value=%s %s>%s</option>',
									esc_html( $key ),
									esc_attr( $selected ),
									esc_html( $name )
								);
							}
							?>
						</select>
						<input type="submit" name="Submit" class="button-primary" value="<?php esc_attr_e( 'Go', 'woocommerce-ac' ); ?>"  />
					</form>
				</div>
			</div>
			<?php

			if ( 'wcap_trash_abandoned' === $section ) {
				$wcap_abandoned_order_list = new Wcap_Abandoned_Orders_Table();
				$wcap_trash_abandoned_list = new Wcap_Abandoned_Trash_Orders_Table();
				$wcap_trash_abandoned_list->wcap_abandoned_order_prepare_items();
			} else {
				$wcap_abandoned_order_list = new Wcap_Abandoned_Orders_Table();
				$wcap_abandoned_order_list->wcap_abandoned_order_prepare_items();
			}

			$wcap_abandoned_order_list->wcap_abandoned_order_prepare_items();

			$get_all_abandoned_count      = $wcap_abandoned_order_list->total_all_count;
			$get_trash_abandoned_count    = Wcap_Common::wcap_get_abandoned_order_count( 'wcap_trash_abandoned' );
			$get_registered_user_ac_count = $wcap_abandoned_order_list->total_registered_count;
			$get_guest_user_ac_count      = $wcap_abandoned_order_list->total_guest_count;
			$get_visitor_user_ac_count    = $wcap_abandoned_order_list->total_visitors_count;
			$get_unsubscribe_carts_count  = Wcap_Common::wcap_get_abandoned_order_count( 'wcap_all_unsubscribe_carts' );
			$wcap_user_gus_text           = $get_guest_user_ac_count > 1 ? __( 'Users', 'woocommerce-ac' ) : __( 'User', 'woocommerce-ac' );

			$wcap_user_reg_text = $get_registered_user_ac_count > 1 ? __( 'Users', 'woocommerce-ac' ) : __( 'User', 'woocommerce-ac' );

			$wcap_all_abandoned_carts   = '';
			$wcap_trash_abandoned       = '';
			$wcap_all_registered        = '';
			$wcap_all_guest             = '';
			$wcap_all_visitor           = '';
			$wcap_all_unsubscribe_carts = '';

			switch ( $section ) {
				case 'wcap_all_abandoned':
					$wcap_all_abandoned_carts = 'current';
					break;
				case 'wcap_trash_abandoned':
					$wcap_trash_abandoned = 'current';
					break;
				case 'wcap_all_registered':
					$wcap_all_registered = 'current';
					break;
				case 'wcap_all_guest':
					$wcap_all_guest = 'current';
					break;
				case 'wcap_all_visitor':
					$wcap_all_visitor = 'current';
					break;
				case 'wcap_all_unsubscribe_carts':
					$wcap_all_unsubscribe_carts = 'current';
					break;
				default:
					$wcap_all_abandoned_carts = 'current';
					break;
			}

			?>
			<?php
			if ( $display_trash_success ) {
				?>
				<div class="notice-success" id="trash-notice" style="background: #fff; text-align: left; border: 1px solid #ccd0d4; font-size: 14px; border-left-color: #46b450; border-left-width: 4px; margin: 5px 0 15px;">
					<p style="padding-left: 15px;"> 
					<?php
					echo wp_kses_post(
						sprintf(
							// Translators: Number of order deleted.
							__( '%1$d orders permanently deleted.', 'woocommerce-ac' ),
							esc_attr( $trash_deleted )
						)
					);
					?>
					</p>
				</div>
				<?php
			}
			?>
			<ul class="subsubsub" id="wcap_recovered_orders_list">
				<li>
					<a href="admin.php?page=woocommerce_ac_page&action=listcart&wcap_section=wcap_all_abandoned" class="<?php echo esc_attr( $wcap_all_abandoned_carts ); ?>"><?php esc_html_e( 'All ', 'woocommerce-ac' ); ?> <span class = "count" > <?php echo esc_html( "( $get_all_abandoned_count )" ); ?> </span></a>
				</li>
				<?php if ( $get_trash_abandoned_count > 0 ) { ?>
				<li>
					| <a href="admin.php?page=woocommerce_ac_page&action=listcart&wcap_section=wcap_trash_abandoned" class="<?php echo esc_attr( $wcap_trash_abandoned ); ?>"><?php esc_html_e( 'Trash ', 'woocommerce-ac' ); ?> <span class = "count" > <?php echo esc_html( "( $get_trash_abandoned_count )" ); ?> </a>
				</li>
				<?php } ?>
				<?php if ( $get_registered_user_ac_count > 0 ) { ?>
				<li>
					| <a href="admin.php?page=woocommerce_ac_page&action=listcart&wcap_section=wcap_all_registered" class="<?php echo esc_attr( $wcap_all_registered ); ?>"><?php esc_html_e( " Registered $wcap_user_reg_text ", 'woocommerce-ac' ); // phpcs:ignore ?> <span class = "count" > <?php echo esc_html( "( $get_registered_user_ac_count )" ); ?> </span></a>
				</li>
				<?php } ?>
				<?php if ( $get_guest_user_ac_count > 0 ) { ?>
				<li>
					| <a href="admin.php?page=woocommerce_ac_page&action=listcart&wcap_section=wcap_all_guest" class="<?php echo esc_attr( $wcap_all_guest ); ?>"><?php esc_html_e( " Guest $wcap_user_gus_text ", 'woocommerce-ac' ); // phpcs:ignore ?> <span class = "count" > <?php echo esc_html( "( $get_guest_user_ac_count )" ); ?> </span></a>
				</li>
				<?php } ?>
				<?php if ( $get_visitor_user_ac_count > 0 ) { ?>
				<li>
					| <a href="admin.php?page=woocommerce_ac_page&action=listcart&wcap_section=wcap_all_visitor" class="<?php echo esc_attr( $wcap_all_visitor ); ?>"><?php esc_html_e( ' Carts without Customer Details ', 'woocommerce-ac' ); ?> <span class = "count" > <?php echo esc_html( "( $get_visitor_user_ac_count )" ); ?> </span></a>
				</li>
				<?php } ?>
				<?php if ( $get_unsubscribe_carts_count > 0 ) { ?>
				<li>
					| <a href="admin.php?page=woocommerce_ac_page&action=listcart&wcap_section=wcap_all_unsubscribe_carts" class="<?php echo esc_attr( $wcap_all_unsubscribe_carts ); ?>"><?php esc_html_e( ' Unsubscribed Carts ', 'woocommerce-ac' ); ?> <span class = "count" > <?php echo esc_html( "( $get_unsubscribe_carts_count )" ); ?> </span></a>
				</li>
				<?php } ?>


			</ul>
			<br class="clear">
			<div id="wcap_ac_bulk_message" class="error">
				<p class="wcap_ac_bulk_message_p"></p>
			</div>
			<div style="background: white; text-align: center; border: 1px solid #e0e0d1; font-size: 14px;">
			<p style="font-size: 18px;">
			<?php
			echo wp_kses_post(
				sprintf(
					// Translators: Recovered carts count & amount of the orders recovered.
					__( '<strong>%1$d</strong> carts worth <strong>%2$s</strong> were recovered during the selected range.', 'woocommerce-ac' ),
					esc_attr( Wcap_Abandoned_Orders_Table::$recovered_count ),
					esc_attr( get_woocommerce_currency_symbol() . Wcap_Abandoned_Orders_Table::$recovered_amount )
				)
			);
			?>
			</p>
			</div>
			<div class="wrap">
				<form id="wcap-abandoned-orders" method="get" >
					<input type="hidden" name="page" value="woocommerce_ac_page" />
					<input type="hidden" name="action" value="listcart" />
					<input type="hidden" name="wcap_action" value="listcart" />
					<input type="hidden" name="wcap_section" value="<?php echo esc_attr( $section ); ?>" />

					<?php
						$print_args    = array(
							'wcap_download' => 'wcap.print',
							'cart_status'   => $filtered_status,
						);
						$download_args = array(
							'wcap_download' => 'wcap.csv',
							'cart_status'   => $filtered_status,
						);
						?>
					<div class='wcap-view-abandoned-orders-msg'></div>
					<div class="wcap-view-cart-data" style="display:none;"></div>
					<div class= "wcap_download" >
						<a href="#" id="wcap_print" class="button-secondary wcap-export"><?php esc_html_e( 'Print', 'woocommerce-ac' ); ?></a>
						<a href="#" id="wcap_csv" class="button-secondary wcap-export"><?php esc_html_e( 'CSV', 'woocommerce-ac' ); ?></a>
						<?php do_action( 'wcap_add_buttons_on_abandoned_orders' ); ?>
					</div>
					<?php
					if ( 'wcap_trash_abandoned' === $section ) {
						$wcap_trash_abandoned_list->display();
					} else {
						$wcap_abandoned_order_list->display();
					}
					?>
				</form>
			</div>
			<?php

		}
	}
}
