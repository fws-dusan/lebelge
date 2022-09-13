<?php
/**
 * Contains all the functions used in Emails sent.
 * This includes automated reminders, manual reminders, tests & previews.
 *
 * @since 8.9.0
 * @package Abandoned-Cart-Pro-for-WooCommerce
 */

/**
 * Get the list of active email templates with content.
 *
 * @return array $results_template - List of active email templates.
 * @since 8.9.0
 */
function wcap_get_active_email_templates() {

	global $wpdb;

	// Fetch all active templates present in the system.
	$results_template = $wpdb->get_results( // phpcs:ignore
		"SELECT wpet . * FROM `" . WCAP_EMAIL_TEMPLATE_TABLE . "` AS wpet WHERE wpet.is_active = '1' ORDER BY `day_or_hour` DESC, `frequency` ASC" //phpcs:ignore
	);
	return $results_template;
}

/**
 * Return email subject with merged data.
 *
 * @param string $email_subject - Email Subject Line.
 * @param array  $merge_tag_values - Merge tags & the values to replace them with.
 * @return string $email_subject - Merge tags replaced with valid content.
 * @since 8.9.0
 */
function wcap_replace_email_merge_tags_subject( $email_subject, $merge_tag_values ) {

	$customer_firstname = isset( $merge_tag_values['customer.firstname'] ) ? $merge_tag_values['customer.firstname'] : '';
	$sub_line_prod_name = isset( $merge_tag_values['product.name'] ) ? $merge_tag_values['product.name'] : '';

	$email_subject = str_ireplace( '{{customer.firstname}}', $customer_firstname, $email_subject );
	$email_subject = str_ireplace( '{{product.name}}', $sub_line_prod_name, $email_subject );

	return $email_subject;
}

/**
 * Return email body with merged data.
 *
 * @param string $email_body - Email Body.
 * @param array  $merge_tag_values - Merge tags & the values to replace them with.
 * @return string $email_body - Merge tags replaced with valid content.
 * @since 8.9.0
 */
function wcap_replace_email_merge_tags_body( $email_body, $merge_tag_values ) {

	$customer_email       = isset( $merge_tag_values['customer.email'] ) ? $merge_tag_values['customer.email'] : '';
	$customer_firstname   = isset( $merge_tag_values['customer.firstname'] ) ? $merge_tag_values['customer.firstname'] : '';
	$customer_lastname    = isset( $merge_tag_values['customer.lastname'] ) ? $merge_tag_values['customer.lastname'] : '';
	$customer_fullname    = isset( $merge_tag_values['customer.fullname'] ) ? $merge_tag_values['customer.fullname'] : '';
	$customer_phone       = isset( $merge_tag_values['customer.phone'] ) ? $merge_tag_values['customer.phone'] : '';
	$coupon_code_to_apply = isset( $merge_tag_values['coupon.code'] ) ? $merge_tag_values['coupon.code'] : '';
	$order_date           = isset( $merge_tag_values['cart.abandoned_date'] ) ? $merge_tag_values['cart.abandoned_date'] : '';
	$checkout_link        = isset( $merge_tag_values['checkout.link'] ) ? $merge_tag_values['checkout.link'] : '';
	$cart_link            = isset( $merge_tag_values['cart.link'] ) ? $merge_tag_values['cart.link'] : '';
	$unsubscribe_link     = isset( $merge_tag_values['cart.unsubscribe'] ) ? $merge_tag_values['cart.unsubscribe'] : '';

	$shop_name     = get_option( 'blogname' );
	$shop_url      = get_option( 'siteurl' );
	$store_address = version_compare( WOOCOMMERCE_VERSION, '3.2.0', '>=' ) ? Wcap_Common::wcap_get_wc_address() : '';
	$admin_phone   = wcap_get_admin_phone();

	$email_body = str_ireplace( '{{customer.email}}', $customer_email, $email_body );
	$email_body = str_ireplace( '{{customer.firstname}}', $customer_firstname, $email_body );
	$email_body = str_ireplace( '{{customer.lastname}}', $customer_lastname, $email_body );
	$email_body = str_ireplace( '{{customer.fullname}}', $customer_fullname, $email_body );
	$email_body = str_ireplace( '{{customer.phone}}', $customer_phone, $email_body );

	$email_body = str_ireplace( '{{coupon.code}}', $coupon_code_to_apply, $email_body );
	$email_body = str_ireplace( '{{cart.abandoned_date}}', $order_date, $email_body );
	$email_body = str_ireplace( '{{shop.name}}', $shop_name, $email_body );
	$email_body = str_ireplace( '{{shop.url}}', $shop_url, $email_body );
	$email_body = str_ireplace( '{{store.address}}', $store_address, $email_body );
	$email_body = str_ireplace( '{{admin.phone}}', $admin_phone, $email_body );

	$email_body = str_ireplace( '{{checkout.link}}', $checkout_link, $email_body );
	$email_body = str_ireplace( '{{cart.link}}', $cart_link, $email_body );
	$email_body = str_ireplace( '{{cart.unsubscribe}}', $unsubscribe_link, $email_body );
	// This has been added for email formatting for iOS. The email renders to the left without the meta tag.
	$email_body = '<head><meta name="x-apple-disable-message-reformatting" /></head><body style="padding: 0;">' . $email_body . '</body>';
	return $email_body;
}

/**
 * It will create the unique coupon code.
 *
 * @param int            $discount_amt - Discount amount.
 * @param string         $get_discount_type - Discount type.
 * @param date           $get_expiry_date - Expiry date.
 * @param string         $discount_shipping - Shipping dicsount.
 * @param array | object $coupon_post_meta - Data of Parent coupon.
 * @param string         $individual_use - Force individual use.
 * @return string $final_string 12 Digit unique coupon code name
 * @since 2.3.6
 */
function wcap_wp_coupon_code( $discount_amt, $get_discount_type, $get_expiry_date, $discount_shipping = 'no', $coupon_post_meta, $individual_use = 'yes' ) {
	$ten_random_string         = wp_random_string();
	$first_two_digit           = wp_rand( 0, 99 );
	$final_string              = $first_two_digit . $ten_random_string;
	$datetime                  = $get_expiry_date;
	$coupon_code               = $final_string;
	$coupon_product_categories = isset( $coupon_post_meta['product_categories'][0] ) && '' !== $coupon_post_meta['product_categories'][0] ? unserialize( $coupon_post_meta['product_categories'] [0] ) : array();

	$coupon_exculde_product_categories = isset( $coupon_post_meta['exclude_product_categories'][0] ) && '' !== $coupon_post_meta['exclude_product_categories'][0] ? unserialize( $coupon_post_meta['exclude_product_categories'][0] ) : array();

	$coupon_product_ids = isset( $coupon_post_meta['product_ids'][0] ) && '' !== $coupon_post_meta['product_ids'][0] ? $coupon_post_meta['product_ids'][0] : '';

	$coupon_exclude_product_ids = isset( $coupon_post_meta['exclude_product_ids'][0] ) && '' !== $coupon_post_meta['exclude_product_ids'][0] ? $coupon_post_meta['exclude_product_ids'][0] : '';

	$coupon_free_shipping = isset( $coupon_post_meta['free_shipping'][0] ) && '' !== $coupon_post_meta['free_shipping'][0] ? $coupon_post_meta['free_shipping'][0] : $discount_shipping;

	$coupon_minimum_amount = isset( $coupon_post_meta['minimum_amount'][0] ) && '' !== $coupon_post_meta['minimum_amount'][0] ? $coupon_post_meta['minimum_amount'][0] : '';

	$coupon_maximum_amount = isset( $coupon_post_meta['maximum_amount'][0] ) && '' !== $coupon_post_meta['maximum_amount'][0] ? $coupon_post_meta['maximum_amount'][0] : '';

	$coupon_exclude_sale_items = isset( $coupon_post_meta['exclude_sale_items'][0] ) && '' !== $coupon_post_meta['exclude_sale_items'][0] ? $coupon_post_meta['exclude_sale_items'] [0] : 'no';

	$use_limit = isset( $coupon_post_meta['usage_limit'][0] ) && '' !== $coupon_post_meta['usage_limit'][0] ? $coupon_post_meta['usage_limit'][0] : '';

	$use_limit_user = isset( $coupon_post_meta['usage_limit_per_user'][0] ) && '' !== $coupon_post_meta['usage_limit_per_user'][0] ? $coupon_post_meta['usage_limit_per_user'][0] : '';

	$atc_unique = isset( $coupon_post_meta['atc_unique_coupon'][0] ) && '' !== $coupon_post_meta['atc_unique_coupon'][0] ? $coupon_post_meta['atc_unique_coupon'][0] : false;

	if ( class_exists( 'WC_Free_Gift_Coupons' ) ) {
		$free_gift_coupon   = isset( $coupon_post_meta['gift_ids'][0] ) && '' !== $coupon_post_meta['gift_ids'][0] ? $coupon_post_meta['gift_ids'][0] : '';
		$free_gift_shipping = isset( $coupon_post_meta['free_gift_shipping'][0] ) && '' !== $coupon_post_meta['free_gift_shipping'][0] ? $coupon_post_meta['free_gift_shipping'][0] : 'no';
	}
	if ( is_plugin_active( 'yith-woocommerce-brands-add-on/init.php' ) ) {
		$coupon_brand = isset( $coupon_post_meta['brand'][0] ) && '' !== $coupon_post_meta['brand'][0] ? unserialize( $coupon_post_meta['brand'][0] ) : array();
	}
	$amount        = $discount_amt;
	$discount_type = $get_discount_type;

	// Add coupon meta.
	$coupon_meta = array(
		'discount_type'              => $discount_type,
		'coupon_amount'              => $amount,
		'minimum_amount'             => $coupon_minimum_amount,
		'maximum_amount'             => $coupon_maximum_amount,
		'individual_use'             => $individual_use,
		'free_shipping'              => $coupon_free_shipping,
		'product_ids'                => '',
		'exclude_product_ids'        => '',
		'usage_limit'                => $use_limit,
		'usage_limit_per_user'       => $use_limit_user,
		'date_expires'               => $datetime,
		'apply_before_tax'           => 'yes',
		'product_ids'                => $coupon_product_ids,
		'exclude_sale_items'         => $coupon_exclude_sale_items,
		'exclude_product_ids'        => $coupon_exclude_product_ids,
		'product_categories'         => $coupon_product_categories,
		'exclude_product_categories' => $coupon_exculde_product_categories,
		'wcap_created_by'            => 'wcap',
		'atc_unique_coupon'          => $atc_unique,
	);

	if ( class_exists( 'WC_Free_Gift_Coupons' ) ) {
		$coupon_meta['gif_ids']            = $free_gift_coupon;
		$coupon_meta['free_gift_shipping'] = $free_gift_shipping;
	}
	if ( is_plugin_active( 'yith-woocommerce-brands-add-on/init.php' ) ) {
		$coupon_meta['brand'] = $coupon_brand;
	}

	$coupon        = apply_filters(
		'wcap_cron_before_shop_coupon_create',
		array(
			'post_title'       => $coupon_code,
			'post_content'     => 'This coupon provides 5% discount on cart price.',
			'post_status'      => 'publish',
			'post_author'      => 1,
			'post_type'        => 'shop_coupon',
			'post_expiry_date' => $datetime,
			'meta_input'       => $coupon_meta,
		)
	);
	$new_coupon_id = wp_insert_post( $coupon );

	return $final_string;
}

/**
 * It will generate 12 digit unique string for coupon code.
 *
 * @return string $temp_array 12 digit unique string
 * @since 2.3.6
 */
function wp_random_string() {
	$character_set_array   = array();
	$character_set_array[] = array(
		'count'      => 5,
		'characters' => 'abcdefghijklmnopqrstuvwxyz',
	);
	$character_set_array[] = array(
		'count'      => 5,
		'characters' => '0123456789',
	);
	$temp_array            = array();
	foreach ( $character_set_array as $character_set ) {
		for ( $i = 0; $i < $character_set['count']; $i++ ) {
				$temp_array[] = $character_set['characters'][ wp_rand( 0, strlen( $character_set['characters'] ) - 1 ) ];
		}
	}
	shuffle( $temp_array );
	return implode( '', $temp_array );
}

/**
 * It will validate the email format.
 *
 * @param string $wcap_email_address Email address.
 * @return int 1 Correct format of email.
 * @since 3.7
 */
function wcap_validate_email_format( $wcap_email_address ) {

	if ( version_compare( phpversion(), '5.2.0', '>=' ) ) {
		$validated_email = filter_var( sanitize_text_field( $wcap_email_address ), FILTER_VALIDATE_EMAIL );
		$validated_value = $validated_email === $wcap_email_address ? 1 : 0;
	} else {
		$pattern         = '/^(?!(?:(?:\\x22?\\x5C[\\x00-\\x7E]\\x22?)|(?:\\x22?[^\\x5C\\x22]\\x22?)){255,})(?!(?:(?:\\x22?\\x5C[\\x00-\\x7E]\\x22?)|(?:\\x22?[^\\x5C\\x22]\\x22?)){65,}@)(?:(?:[\\x21\\x23-\\x27\\x2A\\x2B\\x2D\\x2F-\\x39\\x3D\\x3F\\x5E-\\x7E]+)|(?:\\x22(?:[\\x01-\\x08\\x0B\\x0C\\x0E-\\x1F\\x21\\x23-\\x5B\\x5D-\\x7F]|(?:\\x5C[\\x00-\\x7F]))*\\x22))(?:\\.(?:(?:[\\x21\\x23-\\x27\\x2A\\x2B\\x2D\\x2F-\\x39\\x3D\\x3F\\x5E-\\x7E]+)|(?:\\x22(?:[\\x01-\\x08\\x0B\\x0C\\x0E-\\x1F\\x21\\x23-\\x5B\\x5D-\\x7F]|(?:\\x5C[\\x00-\\x7F]))*\\x22)))*@(?:(?:(?!.*[^.]{64,})(?:(?:(?:xn--)?[a-z0-9]+(?:-+[a-z0-9]+)*\\.){1,126}){1,}(?:(?:[a-z][a-z0-9]*)|(?:(?:xn--)[a-z0-9]+))(?:-+[a-z0-9]+)*)|(?:\\[(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){7})|(?:(?!(?:.*[a-f0-9][:\\]]){7,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?)))|(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){5}:)|(?:(?!(?:.*[a-f0-9]:){5,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3}:)?)))?(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))(?:\\.(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))){3}))\\]))$/iD';
		$validated_value = preg_match( $pattern, $wcap_explode_emails_value );
	}

	return $validated_value;
}

/**
 * Replaces {{product.cart}} tag in the email body
 *
 * @param string $email_body - Email Body.
 * @param object $cart_details - Products present in the cart.
 * @param array  $email_settings - Email Settings for the plugin.
 * @return string $email_body - Email Body.
 *
 * @since 7.10.0
 */
function wcap_replace_product_cart( $email_body, $cart_details, $email_settings ) {

	$replace_html = '';

	$cart_total                  = 0;
	$item_subtotal               = 0;
	$item_total                  = 0;
	$line_subtotal_tax_display   = 0;
	$after_item_subtotal         = 0;
	$after_item_subtotal_display = 0;

	$wcap_product_image_height = $email_settings['image_height'];
	$wcap_product_image_width  = $email_settings['image_width'];
	$checkout_link_track       = $email_settings['checkout_link'];
	$wcap_used_coupon          = $email_settings['coupon_used'];
	$currency                  = $email_settings['currency'];
	$abandoned_id              = $email_settings['abandoned_id'];
	$email_sent_id             = $email_settings['email_sent_id'];
	$utm                       = $email_settings['utm_params'];
	$cart_language             = isset( $email_settings['cart_lang'] ) ? $email_settings['cart_lang'] : '';
	if ( '' !== $utm && '?' !== substr( $utm, 0, 1 ) ) {
		$utm = "?$utm";
	}
	$go_blogname           = $email_settings['blog_name'];
	$go_siteurl            = $email_settings['site_url'];
	$encrypted_coupon_code = isset( $email_settings['encrypted_coupon_code'] ) ? $email_settings['encrypted_coupon_code'] : '';

	$line_subtotal_tax        = 0;
	$wcap_include_tax         = get_option( 'woocommerce_prices_include_tax', '' );
	$wcap_include_tax_setting = get_option( 'woocommerce_calc_taxes', '' );
	// This array will be used to house the columns in the hierarchy they appear.
	$position_array       = array();
	$start_position       = 0;
	$end_position         = 0;
	$image_start_position = 0;
	$name_start_position  = 0;

	$wcap_all_product_names     = '';
	$wcap_all_product_images    = '';
	$wcap_all_product_price     = '';
	$wcap_all_product_qty       = '';
	$wcap_all_product_sub_total = '';
	$custom_table               = false;
	$custom_replace             = '';
	$number_cols                = 0;
	$display_tax                = false;

	// Check which columns are present.
	$custom_table_html = stripos( $email_body, '<section>' ) > 0 ? true : false;
	if ( stripos( $email_body, '{{item.image}}' ) ) {
		$image_start_position                    = stripos( $email_body, '{{item.image}}' );
		$position_array[ $image_start_position ] = 'image';
		$number_cols += 1;
	}
	// capture any custom styles for the td tag.
	$image_tag_html = '';
	if ( $custom_table_html ) {
		$cart_html = substr( $email_body, stripos( $email_body, '<section>' ) );			
		$image_tag_pos = stripos( $cart_html, "<td class='item.image'" );
		$image_tag_pos = $image_tag_pos > 0 ? $image_tag_pos : stripos( $cart_html, '<td class="item.image"' );
		if ( $image_tag_pos > 0 ) {
			$image_tag_html    = substr( $cart_html, $image_tag_pos );
			$image_tag_end_pos = stripos( $image_tag_html, '</td>' );
			$image_tag_html    = substr_replace( $image_tag_html, '', $image_tag_end_pos );
			$image_tag_html   .= '</td>';
		}
	}

	if ( stripos( $email_body, '{{item.name}}' ) ) {
		$name_start_position                    = stripos( $email_body, '{{item.name}}' );
		$position_array[ $name_start_position ] = 'name';
		$number_cols += 1;
	}
	// capture any custom styles for the td tag.
	$name_tag_html = '';
	if ( $custom_table_html ) {
		$name_tag_pos = stripos( $cart_html, "<td class='item.name'" );
		$name_tag_pos = $name_tag_pos > 0 ? $name_tag_pos : stripos( $cart_html, '<td class="item.name"' );
		if ( $name_tag_pos > 0 ) {
			$name_tag_html    = substr( $cart_html, $name_tag_pos );
			$name_tag_end_pos = stripos( $name_tag_html, '</td>' );
			$name_tag_html    = substr_replace( $name_tag_html, '', $name_tag_end_pos );
			$name_tag_html   .= '</td>';
		}
	}

	if ( stripos( $email_body, '{{item.price}}' ) ) {
		$price_start_position                    = stripos( $email_body, '{{item.price}}' );
		$position_array[ $price_start_position ] = 'price';
		$display_tax                             = true;
		$number_cols                            += 1;
	}
	// capture any custom styles for the td tag.
	$price_tag_html = '';
	if ( $custom_table_html ) {
		$price_tag_pos = stripos( $cart_html, "<td class='item.price'" );
		$price_tag_pos = $price_tag_pos > 0 ? $price_tag_pos : stripos( $cart_html, '<td class="item.price"' );
		if ( $price_tag_pos > 0 ) {
			$price_tag_html    = substr( $cart_html, $price_tag_pos );
			$price_tag_end_pos = stripos( $price_tag_html, '</td>' );
			$price_tag_html    = substr_replace( $price_tag_html, '', $price_tag_end_pos );
			$price_tag_html   .= '</td>';
		}
	}

	if ( stripos( $email_body, '{{item.quantity}}' ) ) {
		$quantity_start_position                    = stripos( $email_body, '{{item.quantity}}' );
		$position_array[ $quantity_start_position ] = 'quantity';
		$number_cols += 1;
	}
	// capture any custom styles for the td tag.
	$qty_tag_html = '';
	if ( $custom_table_html ) {
		$qty_tag_pos = stripos( $cart_html, "<td class='item.quantity'" );
		$qty_tag_pos = $qty_tag_pos > 0 ? $qty_tag_pos : stripos( $cart_html, '<td class="item.quantity"' );
		if ( $qty_tag_pos > 0 ) {
			$qty_tag_html    = substr( $cart_html, $qty_tag_pos );
			$qty_tag_end_pos = stripos( $qty_tag_html, '</td>' );
			$qty_tag_html    = substr_replace( $qty_tag_html, '', $qty_tag_end_pos );
			$qty_tag_html   .= '</td>';
		}
	}

	if ( stripos( $email_body, '{{item.subtotal}}' ) ) {
		$subtotal_start_position                    = stripos( $email_body, '{{item.subtotal}}' );
		$position_array[ $subtotal_start_position ] = 'subtotal';
		$number_cols += 1;
	}
	// capture any custom styles for the td tag.
	$subtotal_tag_html = '';
	if ( $custom_table_html ) {
		$subtotal_tag_pos = stripos( $cart_html, "<td class='item.subtotal'" );
		$subtotal_tag_pos = $subtotal_tag_pos > 0 ? $subtotal_tag_pos : stripos( $cart_html, '<td class="item.subtotal"' );
		if ( $subtotal_tag_pos > 0 ) {
			$subtotal_tag_html    = substr( $cart_html, $subtotal_tag_pos );
			$subtotal_tag_end_pos = stripos( $subtotal_tag_html, '</td>' );
			$subtotal_tag_html    = substr_replace( $subtotal_tag_html, '', $subtotal_tag_end_pos );
			$subtotal_tag_html   .= '</td>';
		}
	}

	// Complete populating the array.
	ksort( $position_array );
	$tr_array   = explode( '<tr', $email_body );
	$check_html = '';
	$style      = '';
	foreach ( $tr_array as $tr_key => $tr_value ) {
		if ( ( stripos( $tr_value, '{{item.image}}' ) ||
				stripos( $tr_value, '{{item.name}}' ) ||
				stripos( $tr_value, '{{item.price}}' ) ||
				stripos( $tr_value, '{{item.quantity}}' ) ||
				stripos( $tr_value, '{{item.subtotal}}' ) ) &&
			! stripos( $tr_value, '{{cart.total}}' ) &&
			( null !== get_object_vars( $cart_details ) && count( get_object_vars( $cart_details ) ) > 0 ) &&
			stripos( $tr_value, 'wcap_custom_table' ) === false &&
			stripos( $tr_value, 'cart.link' ) === false ) {

			$style_start  = stripos( $tr_value, 'style' );
			$style_end    = stripos( $tr_value, '>', $style_start );
			$style_end    = $style_end - $style_start;
			$style        = substr( $tr_value, $style_start, $style_end );
			$tr_value     = '<tr' . $tr_value;
			$end_position = stripos( $tr_value, '</tr>' );
			$end_position = $end_position + 5;
			$check_html   = substr( $tr_value, 0, $end_position );
		} elseif ( stripos( $tr_value, 'wcap_custom_table' ) !== false ) {
			$tr_value     = '<tr' . $tr_value;
			$end_position = stripos( $tr_value, '</tr>' );
			$end_position = $end_position + 5;
			$custom_tr    = substr( $tr_value, 0, $end_position );
			$custom_table = true;
		}
	}
	$i            = 1;
	$bundle_child = array();
	foreach ( $cart_details as $k => $v ) {

		$product = wc_get_product( $v->product_id );

		$product_link_track = $checkout_link_track;
		if ( get_option( 'wcap_product_name_redirect', 'checkout' ) === 'product' ) {
			$product_url      = get_permalink( $v->product_id ) . $utm;
			$encoding_product = $email_sent_id . '&url=' . $product_url;
			$validate_product = Wcap_Common::encrypt_validate( $encoding_product );
			if ( isset( $encrypted_coupon_code ) && '' !== $encrypted_coupon_code ) {
				$product_link_track = $go_siteurl . '/?wacp_action=track_links&validate=' . $validate_product . '&c=' . $encrypted_coupon_code;
			} else {
				$product_link_track = $go_siteurl . '/?wacp_action=track_links&validate=' . $validate_product;
			}
		}

		$prod_name             = '';
		$image_url             = '';
		$item_price            = '';
		$quantity              = '';
		$item_subtotal_display = '';
		if ( false !== $product ) {
			$image_size = array( $wcap_product_image_width, $wcap_product_image_height, '1' );

			$image_id  = isset( $v->variation_id ) && $v->variation_id > 0 ? $v->variation_id : $v->product_id;
			$image_url = Wcap_Common::wcap_get_product_image( $image_id, $image_size );

			$quantity = $v->quantity;

			if ( version_compare( WOOCOMMERCE_VERSION, '3.0.0', '>=' ) ) {
				$wcap_product_type = $product->get_type();
				$item_name         = $product->get_title();
			} else {
				$wcap_product_type = $product->product_type;
				$item_name         = $product->post_title;
			}

			$prod_name = apply_filters( 'wcap_product_name', $item_name, $v->product_id );
			$sku_text  = __( 'SKU:', 'woocommerce-ac' );
			$sku_text  = apply_filters( 'wcap_sku_text_display', $sku_text, $cart_language );
			$wcap_product_sku = apply_filters( 'wcap_product_sku', $product->get_sku(), $v->product_id );
			if ( false !== $wcap_product_sku && '' !== $wcap_product_sku ) {
				if ( 'simple' === $wcap_product_type && '' !== $product->get_sku() ) {
					$wcap_sku = '<br> ' . $sku_text . ' ' . $product->get_sku();
				} else {
					$wcap_sku = '';
				}
				$prod_name = $prod_name . $wcap_sku;
			}

			$prod_name = apply_filters( 'wcap_after_product_name', $prod_name, $v->product_id );
			// Show variation.
			if ( isset( $v->variation_id ) && $v->variation_id > 0 ) {
				$variation_id = $v->variation_id;
				$variation    = wc_get_product( $variation_id );
				if ( false !== $variation ) {
					$name        = $variation->get_formatted_name();
					$explode_all = explode( '&ndash;', $name );

					if ( version_compare( WOOCOMMERCE_VERSION, '3.0.0', '>=' ) ) {
						if ( false !== $wcap_product_sku && '' !== $wcap_product_sku ) {
							$wcap_sku = '';
							if ( $variation->get_sku() ) {
								$wcap_sku = $sku_text . ' ' . $variation->get_sku() . '<br>';
							}
							$wcap_get_formatted_variation = wc_get_formatted_variation( $variation, true );

							$add_product_name = $prod_name . ' - ' . $wcap_sku . ' ' . $wcap_get_formatted_variation;
						} else {

							$wcap_get_formatted_variation = wc_get_formatted_variation( $variation, true );

							$add_product_name = $prod_name . '<br>' . $wcap_get_formatted_variation;
						}

						$pro_name_variation = (array) $add_product_name;
					} else {
						$pro_name_variation = array_slice( $explode_all, 1, -1 );
					}
					$product_name_with_variable = '';
					$explode_many_varaition     = array();
					foreach ( $pro_name_variation as $pro_name_variation_key => $pro_name_variation_value ) {
						$explode_many_varaition = explode( ',', $pro_name_variation_value );
						if ( ! empty( $explode_many_varaition ) ) {
							foreach ( $explode_many_varaition as $explode_many_varaition_key => $explode_many_varaition_value ) {
								$product_name_with_variable = $product_name_with_variable . html_entity_decode( $explode_many_varaition_value ) . '<br>';
							}
						} else {
							$product_name_with_variable = $product_name_with_variable . html_entity_decode( $explode_many_varaition_value ) . '<br>';
						}
					}
					$prod_name = apply_filters( 'wcap_after_variable_product_name', $product_name_with_variable, $v->product_id );
				}
			}
			// Price and Item Subtotal.
			// Item subtotal is calculated as product total including taxes.
			if ( isset( $wcap_include_tax ) && 'no' === $wcap_include_tax &&
				isset( $wcap_include_tax_setting ) && 'yes' === $wcap_include_tax_setting ) {

					$item_subtotal       = $item_subtotal + $v->line_total; // Tax is excluded, it should not be displayed here.
					$line_subtotal_tax  += $v->line_tax;
					$after_item_subtotal = $v->line_total;

			} elseif ( isset( $wcap_include_tax ) && 'yes' === $wcap_include_tax &&
					isset( $wcap_include_tax_setting ) && 'yes' === $wcap_include_tax_setting ) {

				// Item subtotal is calculated as product total including taxes.
				if ( $v->line_tax > 0 ) {

					$line_subtotal_tax_display += $v->line_tax;

					// After coupon code price.
					$after_item_subtotal = $item_subtotal + $v->line_total + $v->line_tax;

					// Calculate the product price.
					$item_subtotal = $item_subtotal + $v->line_subtotal + $v->line_subtotal_tax;
				} else {
					$item_subtotal              = $item_subtotal + $v->line_total;
					$line_subtotal_tax_display += $v->line_tax;
					$after_item_subtotal        = $item_subtotal + $v->line_tax;
				}
			} else {

				if ( $v->line_subtotal_tax > 0 ) {
					$after_item_subtotal = $v->line_total + $v->line_subtotal_tax;
					$item_subtotal       = $item_subtotal + $v->line_subtotal + $v->line_subtotal_tax;
				} else {
					$after_item_subtotal = $v->line_total;
					$item_subtotal       = $item_subtotal + $v->line_total;
				}
			}

			// Line total.
			$item_total                  = $item_subtotal;
			$item_price                  = $item_subtotal / $quantity;
			$after_item_subtotal_display = ( $item_subtotal - $after_item_subtotal ) + $after_item_subtotal_display;

			$item_subtotal_display = apply_filters( 'acfac_change_currency', Wcap_Common::wcap_get_price( $item_total, $currency ), $abandoned_id, $item_total, 'wcap_cron' );

			$item_price  = apply_filters( 'acfac_change_currency', Wcap_Common::wcap_get_price( $item_price, $currency ), $abandoned_id, $item_price, 'wcap_cron' );
			$cart_total += $after_item_subtotal;

			$item_subtotal = 0;
			$item_total    = 0;
			$replace_html .= '<tr ' . $style . '>';

			// If bundled product, get the list of sub products.
			if ( isset( $v->product_type ) && 'bundle' === $v->product_type && isset( $product->bundle_data ) && is_array( $product->bundle_data ) && count( $product->bundle_data ) > 0 ) {
				foreach ( $product->bundle_data as $b_key => $b_value ) {
					$bundle_child[] = $b_key;
				}
			}

			// Check if the product is a part of the bundles product, if yes, set qty and totals to blanks.
			if ( isset( $bundle_child ) && count( $bundle_child ) > 0 ) {
				if ( in_array( $v->product_id, $bundle_child, true ) ) {
					$item_subtotal_display = '';
					$item_price            = '';
					$quantity              = '';
				}
			}

			$wcap_all_product_names = $prod_name . ', ' . $wcap_all_product_names;

			$wcap_all_product_images = $image_url . ', ' . $wcap_all_product_images;

			$wcap_all_product_price = $item_price . ', ' . $wcap_all_product_price;

			$wcap_all_product_qty = $quantity . ', ' . $wcap_all_product_qty;

			$wcap_all_product_sub_total = $item_subtotal_display . ', ' . $wcap_all_product_sub_total;

			// For customized tables.
			if ( true === $custom_table ) {
				$custom_replace .= $custom_tr;
				$custom_replace  = str_ireplace( '{{item.name}}', $prod_name, $custom_replace );
				$custom_replace  = str_ireplace( '{{item.image}}', $image_url, $custom_replace );
				$custom_replace  = str_ireplace( '{{item.price}}', $item_price, $custom_replace );
				$custom_replace  = str_ireplace( '{{item.quantity}}', $quantity, $custom_replace );
				$custom_replace  = str_ireplace( '{{item.subtotal}}', $item_subtotal_display, $custom_replace );
			}

			foreach ( $position_array as $k => $v ) {
				switch ( $v ) {
					case 'image':
						if ( isset( $image_tag_html ) && '' !== $image_tag_html ) {
							$custom_td     = $image_tag_html;
							$custom_td     = str_ireplace( '{{item.image}}', '<a href="' . $checkout_link_track . '">' . $image_url . '</a>', $custom_td );
							$replace_html .= $custom_td;
						} else {
							$replace_html .= '<td style="text-align:center;"> <a href="' . $checkout_link_track . '">' . $image_url . '</a> </td>';
						}
						break;
					case 'name':
						if ( isset( $name_tag_html ) && '' !== $name_tag_html ) {
							$custom_td     = $name_tag_html;
							$custom_td     = str_ireplace( '{{item.name}}', '<a href="' . $product_link_track . '">' . $prod_name . '</a>', $custom_td );
							$replace_html .= $custom_td;
						} else {
							$replace_html .= '<td style="text-align:center;"> <a href="' . $product_link_track . '">' . $prod_name . '</a> </td>';
						}
						break;
					case 'price':
						$item_price = '' !== $item_price ? $item_price : '';
						if ( isset( $price_tag_html ) && '' !== $price_tag_html ) {
							$custom_td     = $price_tag_html;
							$custom_td     = str_ireplace( '{{item.price}}', $item_price, $custom_td );
							$replace_html .= $custom_td;
						} else {
							$replace_html .= '<td style="text-align:center;">' . $item_price . '</td>';
						}
						break;
					case 'quantity':
						if ( isset( $qty_tag_html ) && '' !== $qty_tag_html ) {
							$custom_td     = $qty_tag_html;
							$custom_td     = str_ireplace( '{{item.quantity}}', $quantity, $custom_td );
							$replace_html .= $custom_td;
						} else {
							$replace_html .= '<td style="text-align:center;">' . $quantity . '</td>';
						}
						break;
					case 'subtotal':
						$item_subtotal_display = '' !== $item_subtotal_display ? $item_subtotal_display : '';
						if ( isset( $subtotal_tag_html ) && '' !== $subtotal_tag_html ) {
							$custom_td     = $subtotal_tag_html;
							$custom_td     = str_ireplace( '{{item.subtotal}}', $item_subtotal_display, $custom_td );
							$replace_html .= $custom_td;
						} else {
							$replace_html .= '<td style="text-align:center;">' . $item_subtotal_display . '</td>';
						}
						break;
					default:
						$replace_html .= '<td></td>';
				}
			}
				$replace_html .= '</tr>';

		} else {
			$replace_html               .= '<tr> <td colspan="5"> Product you had added to cart is currently unavailable. Please choose another product from <a href="' . $go_siteurl . '">' . $go_blogname . '</a> </td> </tr>';
			$after_item_subtotal_display = '';
			$wcap_line_subtotal_tax      = '';
		}

		$i++;
	}

	$count_columns = count( $position_array ) - 2;

	if ( '' !== $wcap_used_coupon && isset( $after_item_subtotal_display ) && $after_item_subtotal_display > 0 ) {
		$after_item_subtotal_display = apply_filters( 'acfac_change_currency', Wcap_Common::wcap_get_price( $after_item_subtotal_display, $currency ), $abandoned_id, $after_item_subtotal_display, 'wcap_cron' );

		$replace_html .= '<tr>';
		if ( count( $position_array ) > 2 ) {
			for ( $count_c = 1; $count_c <= $count_columns; $count_c++ ) {
				$replace_html .= '<td></td>';
			}
		}
		// translators: Coupon Used.
		$replace_html .= '<td><strong>' . printf( esc_html__( 'Coupon: %s', 'woocommerce-ac' ), esc_attr( $wcap_used_coupon ) ) . '</strong></td>
						<td> - ' . $after_item_subtotal_display . '</td>
					</tr>';
	}
	$show_taxes = apply_filters( 'wcap_show_taxes', true );

	// Calculate the cart total.
	if ( isset( $wcap_include_tax ) && 'yes' === $wcap_include_tax &&
		isset( $wcap_include_tax_setting ) && 'yes' === $wcap_include_tax_setting ) {

		$cart_total                = apply_filters( 'acfac_change_currency', Wcap_Common::wcap_get_price( $cart_total, $currency ), $abandoned_id, $cart_total, 'wcap_cron' );
		$line_subtotal_tax_display = apply_filters( 'acfac_change_currency', Wcap_Common::wcap_get_price( $line_subtotal_tax_display, $currency ), $abandoned_id, $line_subtotal_tax_display, 'wcap_cron' );
		if ( $show_taxes ) {

			$cart_total = $cart_total . ' (' . __( 'includes Tax: ', 'woocommerce-ac' ) . $line_subtotal_tax_display . ')';
		} else {
			$cart_total = $cart_total;
		}
	} elseif ( isset( $wcap_include_tax ) && 'no' === $wcap_include_tax &&
		isset( $wcap_include_tax_setting ) && 'yes' === $wcap_include_tax_setting ) {
		if ( $display_tax && $show_taxes ) {
			$formatted_tax = wc_price( $line_subtotal_tax );
			$replace_html .= '<tr><td style="text-align: right;" colspan="' . $number_cols . '"><strong>' . esc_html__( 'Tax:', 'woocommerce-ac' ) . '</strong></td><td>' . $formatted_tax . '</td></tr>';
			$cart_total   += $line_subtotal_tax;
		}
		$cart_total = apply_filters( 'acfac_change_currency', Wcap_Common::wcap_get_price( $cart_total, $currency ), $abandoned_id, $cart_total, 'wcap_cron' );
	} else {

		$cart_total = apply_filters( 'acfac_change_currency', Wcap_Common::wcap_get_price( $cart_total, $currency ), $abandoned_id, $cart_total, 'wcap_cron' );
	}

	$wcap_all_product_names = substr( $wcap_all_product_names, 0, -2 );

	$wcap_all_product_images = substr( $wcap_all_product_images, 0, -2 );

	$wcap_all_product_price = substr( $wcap_all_product_price, 0, -2 );

	$wcap_all_product_qty = substr( $wcap_all_product_qty, 0, -2 );

	$wcap_all_product_sub_total = substr( $wcap_all_product_sub_total, 0, -2 );

	if ( true === $custom_table ) {
		$email_body = str_ireplace( $custom_tr, $custom_replace, $email_body );
	}

	// Populate/Add the product rows.
	$email_body = str_ireplace( $check_html, $replace_html, $email_body );
	// Populate the cart total.
	$email_body = str_ireplace( '{{cart.total}}', $cart_total, $email_body );
	if ( '' === $replace_html || '' === $check_html ) {
		$email_body = str_ireplace( '{{item.name}}', $wcap_all_product_names, $email_body );

		$email_body = str_ireplace( '{{item.image}}', $wcap_all_product_images, $email_body );

		$email_body = str_ireplace( '{{item.price}}', $wcap_all_product_price, $email_body );
		$email_body = str_ireplace( '{{item.quantity}}', $wcap_all_product_qty, $email_body );
		$email_body = str_ireplace( '{{item.subtotal}}', $wcap_all_product_sub_total, $email_body );
	} else {
		$email_body = str_ireplace( '{{item.name}}', '', $email_body );
		$email_body = str_ireplace( '{{item.image}}', '', $email_body );
		$email_body = str_ireplace( '{{item.price}}', '', $email_body );
		$email_body = str_ireplace( '{{item.quantity}}', '', $email_body );
		$email_body = str_ireplace( '{{item.subtotal}}', '', $email_body );
	}
	return $email_body;
}

/**
 * Get the coupon which will be added in the email template.
 *
 * @param array  $discount_details - Contains coupon details such as discount amount, type etc.
 * @param string $coupon_code - Parent coupon code.
 * @param int    $default_template - Email template is default - 1|0.
 * @return string $coupon_code_to_apply
 * @since 8.9.0
 */
function wcap_get_coupon_email( $discount_details, $coupon_code, $default_template ) {

	$discount_expiry         = $discount_details['discount_expiry'];
	$discount_expiry_explode = explode( '-', $discount_expiry );
	$expiry_date_extend      = '';
	if ( '' != $discount_expiry_explode[0] && '0' != $discount_expiry_explode[0] ) {
		$discount_expiry    = str_replace( '-', ' ', $discount_expiry );
		$discount_expiry    = ' +' . $discount_expiry;
		$expiry_date_extend = strtotime( $discount_expiry );
	}

	$coupon_post_meta     = '';
	$discount_type        = $discount_details['discount_type'];
	$expiry_date          = apply_filters( 'wcap_coupon_expiry_date', $expiry_date_extend );
	$coupon_code_to_apply = '';
	$discount_shipping    = $discount_details['discount_shipping'];
	$individual_use       = '1' === $discount_details['individual_use'] ? 'yes' : 'no';
	$discount_amount      = $discount_details['discount_amount'];
	$generate_unique_code = $discount_details['generate_unique_code'];

	if ( '' === $coupon_code && '1' == $default_template ) {
			$coupon_post_meta     = apply_filters( 'wcap_update_unique_coupon_post_meta_email', $coupon_code, $coupon_post_meta );
			$coupon_code_to_apply = wcap_wp_coupon_code( $discount_amount, $discount_type, $expiry_date, $discount_shipping, $coupon_post_meta, $individual_use );
	} elseif ( '' !== $coupon_code && '1' == $generate_unique_code ) {
		$coupon_post_meta        = get_post_meta( $coupon_id );
		$coupon_expiry_timestamp = $coupon_post_meta['date_expires'][0];
		$discount_type           = $coupon_post_meta['discount_type'][0];
		$amount                  = $coupon_post_meta['coupon_amount'][0];
		if ( isset( $coupon_post_meta['date_expires'][0] ) && $coupon_expiry_timestamp >= $wcap_current_time && '' != $coupon_post_meta['date_expires'][0] ) {
			$expiry_date = $coupon_post_meta['date_expires'][0];
		}
		$coupon_post_meta     = apply_filters( 'wcap_update_unique_coupon_post_meta_email', $coupon_code, $coupon_post_meta );
		$coupon_code_to_apply = wcap_wp_coupon_code( $amount, $discount_type, $expiry_date, $discount_shipping, $coupon_post_meta );
	} elseif ( '1' === $generate_unique_code && '' === $coupon_code && '0' == $default_template ) {
			$coupon_post_meta     = apply_filters( 'wcap_update_unique_coupon_post_meta_email', $coupon_code, $coupon_post_meta );
			$coupon_code_to_apply = wcap_wp_coupon_code( $discount_amount, $discount_type, $expiry_date, $discount_shipping, $coupon_post_meta, $individual_use );
	} else {
		$coupon_code_to_apply = $coupon_code;
	}
	return $coupon_code_to_apply;
}

/**
 * This function will return the phone number of the customer for the cart.
 *
 * @param int | string $wcap_user_id User id.
 * @param string       $wcap_user_type User type.
 * @globals mixed $wpdb
 * @return string $wcap_customer_phone Phone number of customer.
 * @since: 7.0
 */
function wcap_get_customers_phone( $wcap_user_id, $wcap_user_type ) {
	global $wpdb;

	$wcap_customer_phone = '';
	if ( 'GUEST' === $wcap_user_type && $wcap_user_id > 0 ) {

		$results_guest = $wpdb->get_results( // phpcs:ignore
			$wpdb->prepare(
				'SELECT phone FROM `' . WCAP_GUEST_CART_HISTORY_TABLE . '` WHERE id = %d', // phpcs:ignore
				$wcap_user_id
			)
		);
		if ( count( $results_guest ) > 0 ) {
			$wcap_customer_phone = $results_guest[0]->phone;
		}
	} elseif ( 'GUEST' !== $wcap_user_type && $wcap_user_id > 0 ) {
		$user_phone_number = get_user_meta( $wcap_user_id, 'billing_phone' );
		if ( isset( $user_phone_number[0] ) ) {
			$wcap_customer_phone = $user_phone_number[0];
		}
	}
	return $wcap_customer_phone;
}

/**
 * This function will return the email address of the customer for the cart. As we have given the choice to the admin that
 * he can choose who will recive the template.
 *
 * @param int    $wcap_user_id User id.
 * @param string $wcap_user_type User type.
 * @globals mixed $wpdb
 * @return string $wcap_email_address Email ids of customer.
 * @since: 7.0
 */
function wcap_get_customers_email( $wcap_user_id, $wcap_user_type ) {
	global $wpdb;

	$wcap_email_address = '';
	if ( 'GUEST' === $wcap_user_type && $wcap_user_id > 0 ) {

		$results_guest = $wpdb->get_results( // phpcs:ignore
			$wpdb->prepare(
				'SELECT billing_first_name, billing_last_name, email_id FROM `' . WCAP_GUEST_CART_HISTORY_TABLE . '` WHERE id = %d', // phpcS:ignore
				$wcap_user_id
			)
		);
		if ( count( $results_guest ) > 0 ) {
			$wcap_email_address = $results_guest[0]->email_id;
		}
	} elseif ( 'GUEST' !== $wcap_user_type && $wcap_user_id > 0 ) {
		$key                = 'billing_email';
		$single             = true;
		$user_billing_email = get_user_meta( $wcap_user_id, $key, $single );
		if ( isset( $user_billing_email ) && '' !== $user_billing_email ) {
			$wcap_email_address = $user_billing_email;
		} else {
			$user_data = get_userdata( $wcap_user_id );
			if ( isset( $user_data->user_email ) && '' !== $user_data->user_email ) {
				$wcap_email_address = $user_data->user_email;
			}
		}
	}
	return $wcap_email_address;
}

/**
 * Returns the admin phone.
 */
function wcap_get_admin_phone() {
	$admin_args = array(
		'role'   => 'administrator',
		'fields' => array( 'id' ),
	);

	$admin_usr   = get_users( $admin_args );
	$uid         = $admin_usr[0]->id;
	$admin_phone = get_user_meta( $uid, 'billing_phone', true );
	return $admin_phone;
}

/**
 * Returns customer billing first & last name.
 *
 * @param string $user_type - Type of user Registered or Guest.
 * @param int    $user_id - User ID.
 * @return array $customer_details - Customer details.
 * @since 8.9.0
 */
function wcap_get_customer_names( $user_type, $user_id ) {

	$customer_details    = array();
	$customer_first_name = '';
	$customer_last_name  = '';

	if ( 'GUEST' === $user_type ) {

		global $wpdb;

		$results_guest = $wpdb->get_row( // phpcs:ignore
			$wpdb->prepare(
				'SELECT billing_first_name, billing_last_name, email_id FROM `' . WCAP_GUEST_CART_HISTORY_TABLE . '` WHERE id = %d', // phpcs:ignore
				$user_id
			)
		);

		if ( isset( $results_guest->billing_first_name ) ) {
			$customer_first_name = $results_guest->billing_first_name;
		}
		if ( isset( $results_guest->billing_last_name ) ) {
			$customer_last_name = $results_guest->billing_last_name;
		}
	} else {

		$user_first_name_temp = get_user_meta( $user_id, 'billing_first_name', true );
		if ( '' === $user_first_name_temp && isset( $user_first_name_temp ) ) {
			$user_data           = get_userdata( $user_id );
			$customer_first_name = $user_data->first_name;
		} else {
			$customer_first_name = $user_first_name_temp;
		}

		$user_last_name_temp = get_user_meta( $user_id, 'billing_last_name', true );
		if ( '' === $user_last_name_temp && isset( $user_last_name_temp ) ) {
			$user_data          = get_userdata( $user_id );
			$customer_last_name = $user_data->last_name;
		} else {
			$customer_last_name = $user_last_name_temp;
		}
	}

	if ( '' !== $customer_first_name ) {
		$customer_details['first_name'] = $customer_first_name;
		$customer_details['full_name']  = $customer_first_name;
	}

	if ( '' !== $customer_last_name ) {
		$customer_details['last_name']  = $customer_last_name;
		$customer_details['full_name'] .= " $customer_last_name";
	}
	return $customer_details;

}

/**
 * Replace the {{up.sells}} merge tag with real time data.
 *
 * The {{up.sells}} merge tag is replaced with up sell products
 * for the abandoned products.
 *
 * @param string $email_body - Email body.
 * @param array  $cart_details - Details for the cart that was abandoned.
 * @param array  $email_settings - Email Settings.
 * @return string $email_body - Email Body with the Cross Sells data replaced.
 */
function wcap_replace_upsell_data( $email_body, $cart_details, $email_settings ) {

	$wcap_product_image_height = $email_settings['image_height'];
	$wcap_product_image_width  = $email_settings['image_width'];

	$upsells = false;

	if ( stripos( $email_body, '{{up.sells' ) ) {

		$upsells = true;

		$upsell_ids = array();

		// Get the text in a variable and replace it with {{up.sells}} in the email body
		// Default CSS.
		$up_button_text      = __( 'Add to Cart', 'woocommerce-ac' );
		$up_background_color = '#999ca1';
		$up_text_color       = 'black';
		$up_items            = 4;

		// Get the text in a variable and replace it with {{up.sells}} in the email body.
		$remaining_body = substr( $email_body, stripos( $email_body, '{{up.sells' ) );
		$tag_end        = stripos( $remaining_body, '}}' ) + 2;
		$upsell_data    = substr( $remaining_body, 0, $tag_end );
		$email_body     = str_ireplace( $upsell_data, '{{up.sells}}', $email_body );

		// Check what all is available & take the values.
		if ( stripos( $upsell_data, 'add-to-cart=' ) ) {
			$remaining_body = substr( $upsell_data, stripos( $upsell_data, 'add-to-cart=' ) + 13 );
			$up_button_text = substr( $remaining_body, 0, stripos( $remaining_body, '"' ) );
		}

		if ( stripos( $upsell_data, 'button-color=' ) ) {
			$remaining_body      = substr( $upsell_data, stripos( $upsell_data, 'button-color=' ) + 14 );
			$up_background_color = substr( $remaining_body, 0, stripos( $remaining_body, '"' ) );
		}

		if ( stripos( $upsell_data, 'text-color=' ) ) {
			$remaining_body = substr( $upsell_data, stripos( $upsell_data, 'text-color=' ) + 12 );
			$up_text_color  = substr( $remaining_body, 0, stripos( $remaining_body, '"' ) );
		}

		if ( stripos( $upsell_data, 'items=' ) ) {
			$remaining_body = substr( $upsell_data, stripos( $upsell_data, 'items=' ) + 7 );
			$up_items       = intval( substr( $remaining_body, 0, stripos( $remaining_body, '"' ) ) ) + 1;
		}
	}

	if ( $upsells ) {
		$abandoned_products = array();
		foreach ( $cart_details as $k => $v ) { // list of abandoned products.
			array_push( $abandoned_products, $v->product_id );
		}

		foreach ( $cart_details as $k => $v ) {
			// Upsells.
			$upsell_ids_list = get_post_meta( $v->product_id, '_upsell_ids', true );

			if ( is_array( $upsell_ids_list ) && count( $upsell_ids_list ) > 0 ) {

				foreach ( $upsell_ids_list as $u_key => $ids ) {
					if ( in_array( $ids, $abandoned_products, true ) ) {
						unset( $upsell_ids_list[ $u_key ] ); // Remove product if its already abandoned.
					}
				}

				$upsell_ids = array_unique( array_merge( $upsell_ids, $upsell_ids_list ) );
			}
		}

		// Upsells.
		$image_size    = array( $wcap_product_image_width, $wcap_product_image_height, 1 );
		$site_url      = $email_settings['site_url'];
		$email_sent_id = $email_settings['email_sent_id'];
		$upsells_table = '';

		if ( isset( $upsell_ids ) && is_array( $upsell_ids ) && count( $upsell_ids ) > 0 ) {

			$prds_added    = 1;
			$upsells_table = "<table border='0' align='center'><tbody><tr>";

			foreach ( $upsell_ids as $upsell_prd_id ) {

				$upsell_img_url   = WCAP_Common::wcap_get_product_image( $upsell_prd_id, $image_size );
				$encoding_prd     = $email_sent_id . '&url=' . get_permalink( $upsell_prd_id );
				$validate_prd_url = Wcap_Common::encrypt_validate( $encoding_prd );

				$upsell_prd_url             = $site_url . '/?wacp_action=track_links&validate=' . $validate_prd_url;
				$upsell_prd_name            = get_the_title( $upsell_prd_id );
				$upsell_add_cart_url_encode = Wcap_Common::encrypt_validate( $email_sent_id . '&url=' . get_permalink( $upsell_prd_id ) . "?add-to-cart=$upsell_prd_id" );
				$upsell_add_cart_url        = $site_url . '/?wacp_action=track_links&validate=' . $upsell_add_cart_url_encode;

				$prd_img     = "<a href='$upsell_prd_url' target='_blank'>$upsell_img_url</a><br>";
				$prd_name    = "<a href='$upsell_prd_url' target='_blank' style='text-decoration:none; color: black; font-weight: 500; margin-bottom: 10px; display: inline-block;'>$upsell_prd_name</a>";
				$cart_button = "<div><a href='$upsell_add_cart_url' data-quantity='1' data-product_id='$upsell_prd_id' target='_blank' style='text-decoration: none; background-color: $up_background_color; color: $up_text_color; padding: 10px; display:inline-block; '>$up_button_text</a></div>";

				if ( $prds_added % $up_items ) {
					$upsells_table .= "<td style='padding: 8px; text-align: center;'>$prd_img <br> $prd_name <br> $cart_button </td>";
				} else {
					$upsells_table .= "</tr><tr><td style='padding: 8px; text-align:center; '>$prd_img <br> $prd_name <br> $cart_button </td>";
				}

				$prds_added++;

			}

			$upsells_table .= '</tr></tbody></table>';
		}
		$email_body = str_ireplace( '{{up.sells}}', $upsells_table, $email_body );

	}
	return $email_body;
}

/**
 * Replace the {{cross.sells}} merge tag with real time data.
 *
 * The {{cross.sells}} merge tag is replaced with cross sell products
 * for the abandoned products.
 *
 * @param string $email_body - Email body.
 * @param array  $cart_details - Details for the cart that was abandoned.
 * @param array  $email_settings - Email Settings.
 * @return string $email_body - Email Body with the Cross Sells data replaced.
 */
function wcap_replace_crosssell_data( $email_body, $cart_details, $email_settings ) {

	$wcap_product_image_height = $email_settings['image_height'];
	$wcap_product_image_width  = $email_settings['image_width'];

	$crosssells = false;

	if ( stripos( $email_body, '{{cross.sells' ) ) {

		$crosssells    = true;
		$crosssell_ids = array();

		// Get the text in a variable and replace it with {{up.sells}} in the email body.
		// Default CSS.
		$cross_button_text      = __( 'Add to Cart', 'woocommerce-ac' );
		$cross_background_color = '#999ca1';
		$cross_text_color       = 'black';
		$cross_items            = 4;

		// Get the text in a variable and replace it with {{up.sells}} in the email body.
		$remaining_body = substr( $email_body, stripos( $email_body, '{{cross.sells' ) );
		$tag_end        = stripos( $remaining_body, '}}' ) + 2;
		$crosssell_data = substr( $remaining_body, 0, $tag_end );
		$email_body     = str_ireplace( $crosssell_data, '{{cross.sells}}', $email_body );

		// Check what all is available & take the values.
		if ( stripos( $crosssell_data, 'add-to-cart=' ) ) {
			$remaining_body    = substr( $crosssell_data, stripos( $crosssell_data, 'add-to-cart=' ) + 13 );
			$cross_button_text = substr( $remaining_body, 0, stripos( $remaining_body, '"' ) );
		}

		if ( stripos( $crosssell_data, 'button-color=' ) ) {
			$remaining_body         = substr( $crosssell_data, stripos( $crosssell_data, 'button-color=' ) + 14 );
			$cross_background_color = substr( $remaining_body, 0, stripos( $remaining_body, '"' ) );
		}

		if ( stripos( $crosssell_data, 'text-color=' ) ) {
			$remaining_body   = substr( $crosssell_data, stripos( $crosssell_data, 'text-color=' ) + 12 );
			$cross_text_color = substr( $remaining_body, 0, stripos( $remaining_body, '"' ) );
		}

		if ( stripos( $crosssell_data, 'items=' ) ) {
			$remaining_body = substr( $crosssell_data, stripos( $crosssell_data, 'items=' ) + 7 );
			$cross_items    = intval( substr( $remaining_body, 0, stripos( $remaining_body, '"' ) ) ) + 1;
		}
	}

	if ( $crosssells ) {

		$abandoned_products = array();
		foreach ( $cart_details as $k => $v ) { // list of abandoned products.
			array_push( $abandoned_products, $v->product_id );
		}

		foreach ( $cart_details as $k => $v ) {
			$crosssell_ids_list = get_post_meta( $v->product_id, '_crosssell_ids', true );
			if ( is_array( $crosssell_ids_list ) && count( $crosssell_ids_list ) > 0 ) {

				foreach ( $crosssell_ids_list as $u_key => $ids ) {
					if ( in_array( $ids, $abandoned_products, true ) ) {
						unset( $crosssell_ids_list[ $u_key ] ); // Remove product if its already abandoned.
					}
				}

				$crosssell_ids = array_unique( array_merge( $crosssell_ids, $crosssell_ids_list ) );
			}
		}

		// cross sells.
		$image_size       = array( $wcap_product_image_width, $wcap_product_image_height, 1 );
		$site_url         = $email_settings['site_url'];
		$email_sent_id    = $email_settings['email_sent_id'];
		$crosssells_table = '';

		if ( isset( $crosssell_ids ) && is_array( $crosssell_ids ) && count( $crosssell_ids ) > 0 ) {

			$prds_added       = 1;
			$crosssells_table = "<table border='0' align='center'><tbody><tr>";

			foreach ( $crosssell_ids as $crosssell_prd_id ) {

				$crosssell_img_url = WCAP_Common::wcap_get_product_image( $crosssell_prd_id, $image_size );
				$encoding_prd      = $email_sent_id . '&url=' . get_permalink( $crosssell_prd_id );
				$validate_prd_url  = Wcap_Common::encrypt_validate( $encoding_prd );

				$crosssell_prd_url             = $site_url . '/?wacp_action=track_links&validate=' . $validate_prd_url;
				$crosssell_prd_name            = get_the_title( $crosssell_prd_id );
				$crosssell_add_cart_url_encode = Wcap_Common::encrypt_validate( $email_sent_id . '&url=' . get_permalink( $crosssell_prd_id ) . "?add-to-cart=$crosssell_prd_id" );
				$crosssell_add_cart_url        = $site_url . '/?wacp_action=track_links&validate=' . $crosssell_add_cart_url_encode;

				$prd_img     = "<div><a href='$crosssell_prd_url' target='_blank'>$crosssell_img_url</a></div>";
				$prd_name    = "<div><a href='$crosssell_prd_url' target='_blank' style='text-decoration:none; color: black; font-weight: 500; margin-bottom: 10px; display: inline-block;'>$crosssell_prd_name</a></div>";
				$cart_button = "<div><a href='$crosssell_add_cart_url' data-quantity='1' data-product_id='$crosssell_prd_id' target='_blank' style='text-decoration: none; background-color: $cross_background_color; color: $cross_text_color; padding: 10px; display:inline-block; '>$cross_button_text</a></div>";

				if ( $prds_added % $cross_items ) {
					$crosssells_table .= "<td style='padding: 8px; text-align: center;'>$prd_img $prd_name $cart_button </td>";
				} else {
					$crosssells_table .= "</tr><tr><td style='padding: 8px; text-align:center; '>$prd_img $prd_name $cart_button </td>";
				}

				$prds_added++;

			}

			$crosssells_table .= '</tr></tbody></table>';

		}
		$email_body = str_ireplace( '{{cross.sells}}', $crosssells_table, $email_body );

	}

	return $email_body;
}

/**
 * Returns the template ID & frequency of the last email in the reminder sequence.
 *
 * @return array - key: template ID|value: frequency (in timestamp)
 * @since 8.9.1
 */
function wcap_get_last_email_template() {

	global $wpdb;
	$get_active = $wpdb->get_results(
		"SELECT id, frequency, day_or_hour FROM `" . WCAP_EMAIL_TEMPLATE_TABLE . "` WHERE is_active = '1' ORDER BY `day_or_hour` DESC, `frequency` ASC" //phpcs:ignore
	);
	$minute_seconds   = 60;
	$hour_seconds     = 3600; // 60 * 60
	$day_seconds      = 86400; // 24 * 60 * 60
	$list_frequencies = array();
	if ( is_array( $get_active ) && count( $get_active ) > 0 ) {
		foreach ( $get_active as $active ) {
			switch ( $active->day_or_hour ) {
			case 'Minutes':
				$template_freq = $active->frequency * $minute_seconds;
				break;
			case 'Days':
				$template_freq = $active->frequency * $day_seconds;
				break;
			case 'Hours':
				$template_freq = $active->frequency * $hour_seconds;
				break;
			}
			$list_frequencies[ $active->id ] = (int) $template_freq;
		}
	
		arsort( $list_frequencies, SORT_NUMERIC );
		reset( $list_frequencies );
		$template_id = key( $list_frequencies );
	
		return array(
			$template_id => array_shift( $list_frequencies )
		);
	}
	return false;
}

/**
 * It will give the translated text from the WPML.
 *
 * @param string $get_translated_text Id of the message.
 * @param string $message Message.
 * @param string $language Selected language.
 * @global mixed $wpdb.
 * @return $message Message.
 * @since 2.6
 */
function wcap_get_translated_texts( $get_translated_text, $message, $language ) {
	if ( function_exists( 'icl_register_string' ) ) {
		$translated = apply_filters( 'wpml_translate_single_string', $message, 'WCAP', $get_translated_text, $language );
		return $translated;

	} else {
		return $message;
	}
}
