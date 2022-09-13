<?php
/**
 * Contains all the common functions used in SMS, FB & ATC.
 * This incudes getting & setting of data mainly.
 *
 * @since 8.9.0
 * @package Abandoned-Cart-Pro-for-WooCommerce
 */

/**
 * Common function that can be used to get the data
 * from the notifications_meta table
 *
 * @param integer $template_id - Template ID.
 * @param string  $meta_key - Meta Key.
 * @return boolean|string - Meta Value. Returns false if meta key not found.
 *
 * @since 7.9
 */
function wcap_get_notification_meta( $template_id, $meta_key ) {

	global $wpdb;

	if ( $template_id > 0 && '' !== $meta_key ) {

		$query_data = $wpdb->get_results( // phpcs:ignore
			$wpdb->prepare(
				'SELECT meta_value FROM `' . WCAP_NOTIFICATIONS_META . '` WHERE template_id = %d AND meta_key = %s', // phpcs:ignore
				$template_id,
				$meta_key
			)
		);

		if ( is_array( $query_data ) && count( $query_data ) > 0 ) {
			return ( isset( $query_data[0]->meta_value ) ) ? $query_data[0]->meta_value : false;
		} else {
			return false;
		}
	} else {
		return false;
	}

}

/**
 * Common function that can be used to update the
 * Notifications_meta table
 *
 * @param integer $template_id - Template ID.
 * @param string  $meta_key - Meta Key.
 * @param string  $meta_value - Meta Value.
 *
 * @since 7.9
 */
function wcap_update_notification_meta( $template_id, $meta_key, $meta_value ) {

	global $wpdb;

	if ( $template_id > 0 && '' !== $meta_key ) {

		$update = $wpdb->update( // phpcs:ignore
			WCAP_NOTIFICATIONS_META,
			array(
				'meta_value' => $meta_value // phpcs:ignore
			),
			array(
				'template_id' => $template_id,
				'meta_key'    => $meta_key, // phpcs:ignore
			)
		);

		if ( 0 === $update && false === wcap_get_notification_meta( $template_id, $meta_key ) ) { // No record was found for update.
			wcap_add_notification_meta( $template_id, $meta_key, $meta_value );
		}
	}

}

/**
 * Common function that can be used to insert in the
 * Notifications_meta table
 *
 * @param integer $template_id - Template ID.
 * @param string  $meta_key - Meta Key.
 * @param string  $meta_value - Meta Value.
 *
 * @since 7.9
 */
function wcap_add_notification_meta( $template_id, $meta_key, $meta_value ) {

	global $wpdb;

	$update = $wpdb->insert( // phpcs:ignore
		WCAP_NOTIFICATIONS_META,
		array(
			'template_id' => $template_id,
			'meta_key'    => $meta_key, // phpcs:ignore
			'meta_value'  => $meta_value, // phpcs:ignore
		)
	);

}

/**
 * Returns the data from the Notifications meta
 * table that have the meta key as passed
 *
 * @param string $meta_key - Meta Key.
 * @return array $results - Results array.
 *
 * @since 7.9
 */
function wcap_get_notification_meta_by_key( $meta_key ) {
	global $wpdb;

	$meta_results = $wpdb->get_results( // phpcs:ignore
		$wpdb->prepare(
			'SELECT meta_id, template_id, meta_value FROM `' . WCAP_NOTIFICATIONS_META . '` WHERE meta_key = %s', // phpcs:ignore
			$meta_key
		)
	);

	return $meta_results;
}

/**
 * Returns the template status
 *
 * @param integer $template_id - Template ID.
 * @return boolean $status - Template status - true - active|false - inactive.
 *
 * @since 7.9
 */
function wcap_get_template_status( $template_id ) {

	$status = false;

	global $wpdb;

	$status_col = $wpdb->get_results( // phpcs:ignore
		$wpdb->prepare(
			'SELECT is_active FROM `' . WCAP_NOTIFICATIONS . '` WHERE id = %d', // phpcs:ignore
			$template_id
		)
	);

	$status = ( isset( $status_col[0] ) ) ? $status_col[0]->is_active : false;

	return $status;
}

/**
 * Returns the list of enabled reminder methods
 *
 * @return array $reminders_enabled - Reminder Methods that are enabled.
 * @since 7.10.0
 */
function wcap_get_enabled_reminders() {

	$reminders_enabled = array();

	$reminders_list = array();

	$reminders_list['emails'] = get_option( 'ac_enable_cart_emails', '' );
	$reminders_list['sms']    = get_option( 'wcap_enable_sms_reminders', '' );

	foreach ( $reminders_list as $names => $status ) {
		if ( 'on' === $status ) {
			array_push( $reminders_enabled, $names );
		}
	}

	$reminders_enabled = apply_filters( 'wcap_reminders_list', $reminders_enabled );

	return $reminders_enabled;
}

/**
 * Update existing template notifications table.
 *
 * @param int    $id - Template ID.
 * @param string $body - Template Body.
 * @param string $frequency - Frequency.
 * @param string $active - Template status.
 * @param string $coupon_code - Coupon code.
 * @param string $subject - Template subject.
 */
function wcap_update_notifications( $id, $body, $frequency, $active, $coupon_code, $subject = null ) {

	global $wpdb;

	$wpdb->update( // phpcs:ignore
		WCAP_NOTIFICATIONS,
		array(
			'body'        => $body,
			'frequency'   => $frequency,
			'is_active'   => $active,
			'coupon_code' => $coupon_code,
			'subject'     => $subject,
		),
		array( 'id' => $id )
	);
}

/**
 * Insert new template in notifications table.
 *
 * @param string $body - Template Body.
 * @param string $type - Template type.
 * @param string $active - Template status.
 * @param string $frequency - Frequency.
 * @param string $coupon_code - Coupon code.
 * @param string $default - Default template.
 * @param string $subject - Template subject.
 */
function wcap_insert_notifications( $body, $type, $active, $frequency, $coupon_code, $default, $subject = null ) {

	global $wpdb;

	$wpdb->insert( // phpcs:ignore
		WCAP_NOTIFICATIONS,
		array(
			'body'             => $body,
			'type'             => $type,
			'is_active'        => $active,
			'frequency'        => $frequency,
			'coupon_code'      => $coupon_code,
			'default_template' => $default,
			'subject'          => $subject,
		)
	);

	return $wpdb->insert_id;
}

/**
 * Returns the list of templates
 *
 * @param string $type Type of notification.
 * @return array Templates data.
 *
 * @since 7.9
 */
function wcap_get_notification_templates( $type ) {

	global $wpdb;

	// Get active templates.
	$template_data = $wpdb->get_results( // phpcs:ignore
		$wpdb->prepare(
			'SELECT * FROM `' . WCAP_NOTIFICATIONS . '` WHERE type = %s AND is_active = %s', // phpcs:ignore
			$type,
			'1'
		)
	);

	if ( is_array( $template_data ) && count( $template_data ) > 0 ) {

		$templates = array();

		$minute_seconds = 60;
		$hour_seconds   = 3600; // 60 * 60
		$day_seconds    = 86400; // 24 * 60 * 60

		foreach ( $template_data as $data ) {

			$frequency_array = explode( ' ', $data->frequency );

			switch ( $frequency_array[1] ) {
				case '':
				case 'minutes':
					$frequency = $frequency_array[0] * $minute_seconds;
					break;
				case 'hours':
					$frequency = $frequency_array[0] * $hour_seconds;
					break;
				case 'days':
					$frequency = $frequency_array[0] * $day_seconds;
					break;
			}

			$templates[ $frequency ] = array(
				'id'          => $data->id,
				'body'        => $data->body,
				'coupon_code' => $data->coupon_code,
			);

			if ( 'fb' === $type ) {
				$templates[ $frequency ]['subject'] = $data->subject;
			}
		}
	} else {
		$templates = array();
	}

	return $templates;
}

/**
 * Returns the list of carts with cart data for which the notification needs to be sent.
 *
 * @param string  $registered_time - Time before which, registered user carts need to be abandoned for notification to be sent.
 * @param string  $guest_time - Time before which guest cart needs to be abandoned for the notification to be sent.
 * @param integer $template_id - Template ID.
 * @return object $type - Template type.
 *
 * @since 7.9
 */
function wcap_get_notification_carts( $registered_time, $guest_time, $template_id, $type = '' ) {

	global $wpdb;

	$carts = array();

	$sent_carts_str  = '';
	$sent_carts_list = wcap_get_notification_meta( $template_id, 'to_be_sent_cart_ids' );

	if ( $sent_carts_list ) {
		$sent_carts = explode( ',', $sent_carts_list );

		foreach ( $sent_carts as $cart_id ) {
			if ( '' !== $sent_carts_str ) {
				$sent_carts_str .= ( '' !== $cart_id ) ? ",'$cart_id'" : '';
			} else {
				$sent_carts_str = ( '' !== $cart_id ) ? "'$cart_id'" : '';
			}
		}
	}

	if ( 'fb' === $type || 'sms' === $type ) {
		$user_id_query = 'AND user_id >= 0';
	} else {
		$user_id_query = 'AND user_id > 0';
	}

	if ( '' !== $sent_carts_str ) {
		// Cart query.
		$cart_query = "SELECT DISTINCT wpac.id, wpac.abandoned_cart_info, wpac.abandoned_cart_time, wpac.user_id, wpac.language FROM `" . WCAP_ABANDONED_CART_HISTORY_TABLE . "` as wpac
                        WHERE cart_ignored IN ('0', '2')
                        AND recovered_cart = 0
                        AND unsubscribe_link = '0'
                        " . $user_id_query . "
                        AND wpac.id IN ( $sent_carts_str )
                        AND (( user_type = 'REGISTERED' AND abandoned_cart_time < %s )
                        OR ( user_type = 'GUEST' AND abandoned_cart_time < %s ))";

		$carts = $wpdb->get_results( $wpdb->prepare( $cart_query, $registered_time, $guest_time ) ); // phpcs:ignore

	}
	return $carts;
}

/**
 * Updates the Notifications meta table and removes
 * the Cart ID from the list of carts for which the SMS
 * needs to be sent.
 *
 * @param integer $template_id - Template ID.
 * @param integer $cart_id - Abandoned Cart ID.
 *
 * @since 7.9
 */
function wcap_update_meta( $template_id, $cart_id ) {

	global $wpdb;

	$list_carts = wcap_get_notification_meta( $template_id, 'to_be_sent_cart_ids' );

	$carts_array = explode( ',', $list_carts );

	if ( in_array( $cart_id, $carts_array ) ) { // phpcs:ignore
		$key = array_search( $cart_id, $carts_array ); // phpcs:ignore
		unset( $carts_array[ $key ] );

		$updated_cart_list = implode( ',', $carts_array );
		wcap_update_notification_meta( $template_id, 'to_be_sent_cart_ids', $updated_cart_list );
	}
}

/**
 * Creates a checkout link and inserts a record in the WCAP_TINY_URLS table.
 *
 * @param object $cart_data - Abandoned Cart Data.
 * @param array  $template_data - contains the id, coupon_code & body.
 * @param string $link_type - Link Type: sms_links.
 * @return integer $insert_id - ID of the record inserted in tiny_urls table.
 */
function generate_checkout_url( $cart_data, $template_data, $link_type ) {

	global $wpdb;

	$abandoned_id  = $cart_data->id;
	$cart_language = $cart_data->language;

	$template_id     = $template_data['id'];
	$coupon_id       = $template_data['coupon_code'];
	$coupon_to_apply = get_post( $coupon_id, ARRAY_A );
	$coupon_code     = $coupon_to_apply['post_title'];

	$checkout_page_id   = wc_get_page_id( 'checkout' );
	$checkout_page_link = $checkout_page_id ? get_permalink( $checkout_page_id ) : '';

	// Force SSL if needed.
	$ssl_is_used = is_ssl() ? true : false;

	if ( true === $ssl_is_used || 'yes' === get_option( 'woocommerce_force_ssl_checkout' ) ) {
		$checkout_page_https = true;
		$checkout_page_link  = str_replace( 'http:', 'https:', $checkout_page_link );
	}

	// check if WPML is active.
	$icl_register_function_exists = function_exists( 'icl_register_string' ) ? true : false;

	if ( $checkout_page_id ) {
		if ( true === $icl_register_function_exists ) {
			if ( 'en' === $cart_language ) { // phpcs:ignore
				// Do nothing.
			} else {
				$checkout_page_link = apply_filters( 'wpml_permalink', $checkout_page_link, $cart_language );
				// if ssl is enabled.
				if ( isset( $checkout_page_https ) && true === $checkout_page_https ) {
					$checkout_page_link = str_replace( 'http:', 'https:', $checkout_page_link );
				}
			}
		}
	}

	$wpdb->insert( // phpcs:ignore
		WCAP_TINY_URLS,
		array(
			'cart_id'           => $abandoned_id,
			'template_id'       => $template_id,
			'long_url'          => '',
			'short_code'        => '',
			'date_created'      => current_time( 'timestamp' ), // phpcs:ignore
			'counter'           => 0,
			'notification_data' => wp_json_encode( array( 'link_clicked' => 'Checkout Page' ) ),
		)
	);
	$insert_id = $wpdb->insert_id;

	$encoding_checkout = $insert_id . '&url=' . $checkout_page_link;
	$validate_checkout = Wcap_Common::encrypt_validate( $encoding_checkout );

	$site_url = get_option( 'siteurl' );

	if ( isset( $coupon_code ) && '' !== $coupon_code ) {
		$encrypted_coupon_code = Wcap_Common::encrypt_validate( $coupon_code );
		$checkout_link_track   = "$site_url/?wacp_action=$link_type&validate=$validate_checkout&c=$encrypted_coupon_code";
	} else {
		$checkout_link_track = "$site_url/?wacp_action=$link_type&validate=$validate_checkout";
	}

	$wpdb->update( // phpcS:ignore
		WCAP_TINY_URLS,
		array( 'long_url' => $checkout_link_track ),
		array( 'id' => $insert_id )
	);

	return $insert_id;
}

/**
 * Set Cart Session variables.
 *
 * @param string $session_key Key of the session.
 * @param string $session_value Value of the session.
 * @since 7.11.0
 */
function wcap_set_cart_session( $session_key, $session_value ) {
	WC()->session->set( $session_key, $session_value );
}

/**
 * Get Cart Session variables.
 *
 * @param string $session_key Key of the session.
 * @return mixed Value of the session.
 * @since 7.11.0
 */
function wcap_get_cart_session( $session_key ) {
	if ( ! is_object( WC()->session ) ) {
			return false;
	}
	return WC()->session->get( $session_key );
}

/**
 * Delete Cart Session variables.
 *
 * @param string $session_key Key of the session.
 * @since 7.11.0
 */
function wcap_unset_cart_session( $session_key ) {
	WC()->session->__unset( $session_key );
}

/**
 * Returns the Cart History Data.
 *
 * @param int $cart_id - Abandoned Cart ID.
 * @return object $cart_history - From the Abandoned Cart History table.
 * @since 8.7.0
 */
function wcap_get_data_cart_history( $cart_id ) {
	global $wpdb;

	$cart_history = $wpdb->get_results( // phpcs:ignore
		$wpdb->prepare(
			'SELECT id, user_id, abandoned_cart_info, abandoned_cart_time, cart_ignored, recovered_cart, user_type, language, checkout_link FROM ' . WCAP_ABANDONED_CART_HISTORY_TABLE . ' WHERE id = %d', // phpcs:ignore
			$cart_id
		)
	);

	if ( is_array( $cart_history ) && count( $cart_history ) > 0 ) {
		return $cart_history[0];
	} else {
		return false;
	}
}

/**
 * Returns the Guest Data.
 *
 * @param int $user_id - Guest User ID.
 * @return object $guest_data - From the Guest History table.
 * @since 8.7.0
 */
function wcap_get_data_guest_history( $user_id ) {

	global $wpdb;

	$guest_data = $wpdb->get_results( // phpcs:ignore
		$wpdb->prepare(
			'SELECT billing_first_name, billing_last_name, billing_country, billing_zipcode, email_id, phone, shipping_zipcode, shipping_charges FROM ' . WCAP_GUEST_CART_HISTORY_TABLE . ' WHERE id = %d', // phpcs:ignore
			$user_id
		)
	);

	if ( is_array( $guest_data ) && count( $guest_data ) > 0 ) {
		return $guest_data[0];
	} else {
		return false;
	}
}

/**
 * Return an array of product details.
 *
 * @param string $cart_data - Abandoned Cart Data frm the Cart History table.
 * @return array $product_details - Product Details.
 * @since 8.7.0
 */
function wcap_get_product_details( $cart_data ) {

	$product_details = array();
	$cart_value      = json_decode( stripslashes( $cart_data ) );

	if ( isset( $cart_value->cart ) && count( get_object_vars( $cart_value->cart ) ) > 0 ) {
		foreach ( $cart_value->cart as $product_data ) {
			$product_id = $product_data->variation_id > 0 ? $product_data->variation_id : $product_data->product_id;
			$details    = (object) array(
				'product_id'    => $product_data->product_id,
				'variation_id'  => $product_data->variation_id,
				'product_name'  => get_the_title( $product_id ),
				'line_subtotal' => $product_data->line_subtotal,
			);
			array_push( $product_details, $details );
		}
	}

	return $product_details;
}

/**
 * Return ATC template data.
 *
 * @param int $id - Template ID.
 * @return array|false - Results array.
 * @since 8.10.0
 */
function wcap_get_atc_template( $id ) {
	global $wpdb;

	$results = $wpdb->get_results( // phpcs:ignore
		$wpdb->prepare(
			'SELECT * FROM ' . WCAP_ATC_RULES_TABLE . ' WHERE id = %d', // phpcs:ignore
			absint( $id )
		)
	);

	if ( is_array( $results ) && count( $results ) > 0 ) {
		return $results[0];
	} else {
		return false;
	}
}

/**
 * Return active ATC templates.
 *
 * @return array $results - ATC Template Data.
 * @since 8.10.0
 */
function wcap_get_active_atc_templates() {
	global $wpdb;

	$results = $wpdb->get_results( // phpcs:ignore
		'SELECT * FROM ' . WCAP_ATC_RULES_TABLE . " WHERE is_active = '1'" // phpcs:ignore
	);

	if ( is_array( $results ) && count( $results ) > 0 ) {
		return $results;
	} else {
		return false;
	}
}

/**
 * Return ATC status.
 *
 * @return bool true | false.
 * @since 8.10.0
 */
function wcap_get_atc_active_status() {
	global $wpdb;

	$count = $wpdb->get_var( // phpcs:ignore
		'SELECT count(id) FROM ' . WCAP_ATC_RULES_TABLE . " WHERE is_active = '1'" // phpcs:ignore
	);

	if ( $count > 0 ) {
		return true;
	} else {
		return false;
	}
}

/**
 * Return ATC email mandatory status.
 *
 * @return bool true | false.
 * @since 8.10.0
 */
function wcap_get_atc_email_mandatory_status() {
	global $wpdb;

	$mandatory = false;

	$atc_templates = $wpdb->get_results( // phpcs:ignore
		'SELECT frontend_settings FROM ' . WCAP_ATC_RULES_TABLE . " WHERE is_active = '1'" // phpcs:ignore
	);

	if ( $atc_templates > 0 ) {
		foreach ( $atc_templates as $settings ) {
			$decoded        = json_decode( $settings->frontend_settings );
			$temp_mandatory = $decoded->wcap_atc_mandatory_email;
			if ( 'on' === $temp_mandatory ) {
				$mandatory = true;
			}
		}
	}
	return $mandatory;
}

/**
 * Return ATC coupon status.
 *
 * @return bool true | false.
 * @since 8.10.0
 */
function wcap_get_atc_coupon_status() {
	global $wpdb;

	$atc_coupon_status = false;

	$atc_templates = $wpdb->get_results( // phpcs:ignore
		'SELECT coupon_settings FROM ' . WCAP_ATC_RULES_TABLE . " WHERE is_active = '1'" // phpcs:ignore
	);

	if ( $atc_templates > 0 ) {
		foreach ( $atc_templates as $settings ) {
			$decoded        = json_decode( $settings->coupon_settings );
			$atc_coupon = $decoded->wcap_atc_auto_apply_coupon_enabled;
			if ( 'on' === $atc_coupon ) {
				$atc_coupon_status = true;
			}
		}
	}
	return $atc_coupon_status;
}

/**
 * Return ATC coupon msg display on Cart page.
 *
 * @return bool true | false.
 * @since 8.10.0
 */
function wcap_get_atc_coupon_msg_cart() {
	global $wpdb;

	$atc_msg_status = false;

	$atc_templates = $wpdb->get_results( // phpcs:ignore
		'SELECT coupon_settings FROM ' . WCAP_ATC_RULES_TABLE . " WHERE is_active = '1'" // phpcs:ignore
	);

	if ( $atc_templates > 0 ) {
		foreach ( $atc_templates as $settings ) {
			$decoded        = json_decode( $settings->coupon_settings );
			$atc_coupon = $decoded->wcap_countdown_cart;
			if ( 'on' === $atc_coupon ) {
				$atc_msg_status = true;
			}
		}
	}
	return $atc_msg_status;
}

/**
 * Return ATC template settings for page ID.
 *
 * @param int $page_id - Page ID.
 * @return array $template_settings - Template Settings.
 * @since 8.10.0
 */
function wcap_get_atc_template_for_page( $page_id ) {
	$active_templates = wcap_get_active_atc_templates();

	$template_match = array();
	$match_found    = false;
	$match_rule     = array();
	// Get the active ATC templates.
	if ( is_array( $active_templates ) && count( $active_templates ) > 0 ) {
		if ( count( $active_templates ) == 1 ) { // No match & a single record indicate the existing record is the default one and should be used in all the pages.
			$match_found          = true;
			$template_match['id'] = $active_templates[0]->id;
			$template_match['fs'] = json_decode( $active_templates[0]->frontend_settings );
			$template_match['cs'] = json_decode( $active_templates[0]->coupon_settings );
		} else {
			foreach ( $active_templates as $template_data ) {
				$match_rule = array();
				$rules      = isset( $template_data->rules ) ? json_decode( $template_data->rules ) : array();
				$match      = isset( $template_data->match_rules ) ? $template_data->match_rules : 'all';
				// if rules are found for the template.
				if ( count( $rules ) > 0 ) {
					foreach ( $rules as $rule_list ) {
						if ( '' !== $rule_list->rule_type && is_array( $rule_list->rule_value ) && count( $rule_list->rule_value ) > 0 ) {
							// check for each rule value based on rule type.
							foreach ( $rule_list->rule_value as $page_details ) {
								switch ( $rule_list->rule_type ) {
									case 'products':
										if ( is_product() || ( function_exists( 'is_producto' ) && is_producto() ) ) {
											if ( (int) $page_id === (int) $page_details ) { // Page ID matches the rule value.
												$template_match['id'] = $template_data->id;
												$template_match['fs'] = json_decode( $template_data->frontend_settings );
												$template_match['cs'] = json_decode( $template_data->coupon_settings );
												if ( 'all' === $match ) {
													array_push( $match_rule, true );
												} else {
													$match_found = true;
													break;
												}
											} elseif ( 'all' === $match ) {
												array_push( $match_rule, false );
											}
										}
										break;
									case 'custom_pages':
										if ( (int) $page_id === (int) $page_details ) { // Page ID matches the rule value.
											$template_match['id'] = $template_data->id;
											$template_match['fs'] = json_decode( $template_data->frontend_settings );
											$template_match['cs'] = json_decode( $template_data->coupon_settings );
											if ( 'all' === $match ) {
												array_push( $match_rule, true );
											} else {
												$match_found = true;
												break;
											}
										} elseif ( 'all' === $match ) {
											array_push( $match_rule, false );
										}
										break;
									case 'product_cat':
										if ( is_product_category() || ( is_product() || ( function_exists( 'is_producto' ) && is_producto() ) ) ) {
											$get_the_terms = get_the_terms( $page_id, 'product_cat' );
											foreach ( $get_the_terms as $terms ) {
												if ( (int) $terms->term_id === (int) $page_details || (int) $terms->parent === (int) $page_details ) { // Term ID (category ID) matches the rule value.
													$template_match['id'] = $template_data->id;
													$template_match['fs'] = json_decode( $template_data->frontend_settings );
													$template_match['cs'] = json_decode( $template_data->coupon_settings );
													$category_matched     = true;
													break;
												} else {
													$category_matched = false;
												}
											}
											if ( 'all' === $match ) {
												if ( $category_matched ) {
													array_push( $match_rule, true );
												} else {
													array_push( $match_rule, false );
												}
											} elseif ( 'any' === $match && $category_matched ) {
												$match_found = true;
												break;
											}
										}
										break;
								}
							}
						}
					}
					if ( 'all' === $match && count( $match_rule ) > 0 && ! in_array( false, $match_rule, true ) ) {
						$match_found = true;
					}
					if ( $match_found ) {
						break;
					}
				} else { // default template as it doesn't have any rules.
					$match_found          = true;
					$template_match['id'] = $template_data->id;
					$template_match['fs'] = json_decode( $template_data->frontend_settings );
					$template_match['cs'] = json_decode( $template_data->coupon_settings );
				}
			}
		}
		// If a match is found, use that template.
		$template_settings = array();
		if ( $match_found ) {
			$template_settings['wcap_heading_section_text_email']           = $template_match['fs']->wcap_heading_section_text_email;
			$template_settings['wcap_text_section_text']                    = $template_match['fs']->wcap_text_section_text;
			$template_settings['wcap_email_placeholder_section_input_text'] = $template_match['fs']->wcap_email_placeholder_section_input_text;
			$template_settings['wcap_button_section_input_text']            = $template_match['fs']->wcap_button_section_input_text;
			$template_settings['wcap_button_color_picker']                  = $template_match['fs']->wcap_button_color_picker;
			$template_settings['wcap_button_text_color_picker']             = $template_match['fs']->wcap_button_text_color_picker;
			$template_settings['wcap_popup_text_color_picker']              = $template_match['fs']->wcap_popup_text_color_picker;
			$template_settings['wcap_popup_heading_color_picker']           = $template_match['fs']->wcap_popup_heading_color_picker;
			$template_settings['wcap_non_mandatory_text']                   = $template_match['fs']->wcap_non_mandatory_text;
			$template_settings['wcap_atc_mandatory_email']                  = $template_match['fs']->wcap_atc_mandatory_email;
			$template_settings['wcap_atc_capture_phone']                    = isset( $template_match['fs']->wcap_atc_capture_phone ) ? $template_match['fs']->wcap_atc_capture_phone : 'off';
			$template_settings['wcap_phone_placeholder']                    = isset( $template_match['fs']->wcap_atc_phone_placeholder ) ? $template_match['fs']->wcap_atc_phone_placeholder : 'Please enter your phone number in E.164 format';
			$template_settings['template_id']                               = $template_match['id'];
			$template_settings['wcap_atc_auto_apply_coupon_enabled']        = $template_match['cs']->wcap_atc_auto_apply_coupon_enabled;
			$template_settings['wcap_atc_coupon_type']                      = $template_match['cs']->wcap_atc_coupon_type;
			$template_settings['wcap_atc_popup_coupon']                     = $template_match['cs']->wcap_atc_popup_coupon;
			$template_settings['wcap_countdown_cart']                       = $template_match['cs']->wcap_countdown_cart;
			$template_settings['wcap_atc_popup_coupon_validity']            = $template_match['cs']->wcap_atc_popup_coupon_validity;
			$template_settings['wcap_countdown_timer_msg']                  = htmlspecialchars( $template_match['cs']->wcap_countdown_timer_msg );
			$template_settings['wcap_countdown_msg_expired']                = $template_match['cs']->wcap_countdown_msg_expired;
		} else { // else load the default template.
			$template_settings['wcap_heading_section_text_email']           = 'Please enter your email';
			$template_settings['wcap_text_section_text']                    = 'To add this item to your cart, please enter your email address.';
			$template_settings['wcap_email_placeholder_section_input_text'] = 'Email Address';
			$template_settings['wcap_button_section_input_text']            = 'Add to Cart';
			$template_settings['wcap_button_color_picker']                  = '#0085ba';
			$template_settings['wcap_button_text_color_picker']             = '#ffffff';
			$template_settings['wcap_popup_text_color_picker']              = '#bbc9d2';
			$template_settings['wcap_popup_heading_color_picker']           = '#737f97';
			$template_settings['wcap_non_mandatory_text']                   = 'No Thanks';
			$template_settings['wcap_atc_mandatory_email']                  = 'off';
			$template_settings['wcap_atc_capture_phone']                    = 'off';
			$template_settings['template_id']                               = 0; // indicates default.
			$template_settings['wcap_atc_auto_apply_coupon_enabled']        = 'off';
			$template_settings['wcap_atc_coupon_type']                      = '';
			$template_settings['wcap_atc_popup_coupon']                     = 0;
			$template_settings['wcap_countdown_cart']                       = '';
			$template_settings['wcap_atc_popup_coupon_validity']            = 0;
			$template_settings['wcap_countdown_timer_msg']                  = '';
			$template_settings['wcap_countdown_msg_expired']                = '';
			$template_settings['wcap_phone_placeholder']                    = 'Please enter your phone number in E.164 format';
		}

		return $template_settings;
	}
}

/**
 * ATC Template for default preview in ATC Settings.
 *
 * @param int $template_id - ATC Template ID.
 * @return array $template_settings - Template Settings.
 * @since 8.10.0
 */
function wcap_get_atc_template_preview( $template_id ) {
	$atc_template = wcap_get_atc_template( $template_id );

	if ( false !== $atc_template && isset( $atc_template->frontend_settings ) ) {
		$frontend_settings                                    = json_decode( $atc_template->frontend_settings );
		$template_settings['wcap_heading_section_text_email'] = $frontend_settings->wcap_heading_section_text_email;
		$template_settings['wcap_text_section_text']          = $frontend_settings->wcap_text_section_text;
		$template_settings['wcap_email_placeholder_section_input_text'] = $frontend_settings->wcap_email_placeholder_section_input_text;
		$template_settings['wcap_button_section_input_text']            = $frontend_settings->wcap_button_section_input_text;
		$template_settings['wcap_button_color_picker']                  = $frontend_settings->wcap_button_color_picker;
		$template_settings['wcap_button_text_color_picker']             = $frontend_settings->wcap_button_text_color_picker;
		$template_settings['wcap_popup_text_color_picker']              = $frontend_settings->wcap_popup_text_color_picker;
		$template_settings['wcap_popup_heading_color_picker']           = $frontend_settings->wcap_popup_heading_color_picker;
		$template_settings['wcap_non_mandatory_text']                   = $frontend_settings->wcap_non_mandatory_text;
		$template_settings['wcap_atc_mandatory_email']                  = $frontend_settings->wcap_atc_mandatory_email;
		$template_settings['wcap_atc_phone_placeholder']                = isset( $frontend_settings->wcap_atc_phone_placeholder ) ? $frontend_settings->wcap_atc_phone_placeholder : '';
	} else { // else load the default template.
		$template_settings['wcap_heading_section_text_email']           = 'Please enter your email';
		$template_settings['wcap_text_section_text']                    = 'To add this item to your cart, please enter your email address.';
		$template_settings['wcap_email_placeholder_section_input_text'] = 'Email Address';
		$template_settings['wcap_button_section_input_text']            = 'Add to Cart';
		$template_settings['wcap_button_color_picker']                  = '#0085ba';
		$template_settings['wcap_button_text_color_picker']             = '#ffffff';
		$template_settings['wcap_popup_text_color_picker']              = '#bbc9d2';
		$template_settings['wcap_popup_heading_color_picker']           = '#737f97';
		$template_settings['wcap_non_mandatory_text']                   = 'No Thanks';
		$template_settings['wcap_atc_mandatory_email']                  = 'off';
		$template_settings['wcap_atc_phone_placeholder']                = 'Please enter your phone number in E.164 format';
	}

	return $template_settings;
}
