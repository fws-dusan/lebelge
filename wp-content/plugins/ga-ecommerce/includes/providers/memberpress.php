<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class MonsterInsights_eCommerce_MemberPress_Integration extends MonsterInsights_Enhanced_eCommerce_Integration {

	// Holds instance of eCommerce object to ensure no double instantiation of hooks
	private static $instance;

	/**
	 * @var string $uuid_meta_key The name of the meta key used to store the UUID
	 */
	public $uuid_meta_key   = '_yoast_gau_uuid';
	public $cookie_meta_key = '_monsterinsights_cookie';

	/**
	 * When order is processed, there is a payment pending created. From that moment the user_id can be saved
	 *
	 * @var string
	 */
	public $store_user_id_hook = 'mepr-txn-status-pending';

	/**
	 * When order is completed, one of these hooks will be fired. Thanks to MI's system, orders will only be
	 * tracked into GA once regardless of how many of these are fired per order, and regardless of how many times.
	 *
	 * @var array
	 */
	public $add_to_ga_hooks = array( 'mepr-txn-status-complete', 'mepr-txn-status-confirmed');

	/**
	 * When order is refunded, one of these hooks will be fired. Thanks to MI's system, orders will only be
	 * tracked out of GA once regardless of how many of these are fired per order, and regardless of how many times.
	 *
	 * @var array
	 */
	public $remove_from_ga_hooks = array( 'mepr-txn-status-refunded' );

	public static function get_instance() {
		if ( ! self::$instance ) {
			self::$instance = new MonsterInsights_eCommerce_MemberPress_Integration();
			self::$instance->hooks();
		}
		return self::$instance;
	}

	private function hooks() {
		// Setup Funnel steps for MemberPress
		$this->funnel_steps = $this->get_funnel_steps();

		// Store cookie
		add_action( $this->store_user_id_hook, array( $this, 'store_user_id' ), 10 );

		// Checkout Page
		add_action( 'mepr-above-checkout-form',  array( $this, 'checkout_page' ) );

		// When to add to GA
		foreach ( $this->add_to_ga_hooks as $hook ) {
			add_action( $hook, array( $this, 'do_transaction' ), 10 );
		}

		// When to remove from GA
		foreach ( $this->remove_from_ga_hooks as $hook ) {
			add_action( $hook, array( $this, 'undo_transaction' ), 10 );
		}

		// PayPal Redirect
		add_filter( 'mepr_gateway_pay_pal_ec_return_notify_url', array( $this, 'change_paypal_return_url' ), 10, 1 );
		add_filter( 'mepr_gateway_pay_pal_standard_return_notify_url', array( $this, 'change_paypal_return_url' ), 10, 1 );
	}

	/**
	 * Store the visitor ID and attached experiments and variations, as stored in the cookie, with the transaction.
	 *
	 * @since 7.3.0
	 *
	 * @param MeprTransaction $txn Transaction.
	 */
	public function store_user_id( $txn ) {
		$ga_uuid = monsterinsights_get_client_id();
		if ( $ga_uuid ) {
			$cookie = monsterinsights_get_cookie();
			$txn->update_meta( $this->uuid_meta_key, $ga_uuid );
			$txn->update_meta( $this->cookie_meta_key, $cookie );
		}
	}

	private function track_checkout_ua( $product_id ) {
		if ( ! monsterinsights_get_ua_to_output() ) {
			return;
		}

		$obj = new MeprProduct( $product_id );

		$atts = array(
			't'     => 'event',                           					  // Type of hit
			'ec'    => 'Checkout',                           				  // Event Category
			'ea'    => 'Started Checkout',                       			  // Event Action
			'el'    => 'Checkout Page: ' . $obj->post_title,                  // Event Label
			'ev'    => '',                                 					  // Event Value (unused)
			'cos'   => 1,                                					  // Checkout Step
			'pa'    => $this->get_funnel_action( 'started_checkout' ),        // Product Action
			'pal'   => '',                               					  // Product Action List
			'nonInteraction' => true,                        				  // Set as non-interaction event
		);

		if ( monsterinsights_get_option( 'userid', false ) && is_user_logged_in() ) {
			$atts['uid'] = get_current_user_id(); // UserID tracking
		}

		// Declare items in cart
		$items = array();
		$items["pr1id"]  = $product_id;      				 // Product ID
		$items["pr1nm"]  = $obj->post_title;    			 // Product Name
		//$items["pr1ca"]  = $first_category;    			 // Product Category
		//$items["pr1va"]  = $variation;        			 // Product Variation Title
		$items["pr1pr"]  = MonsterInsights_eCommerce_Helper::round_price( $obj->price ); 					 // Product Price
		$items["pr1qt"]  = 1;   	    					 // Product Quantity
		$items["pr1ps"]  = 1;            				     // Product Order

		$atts = array_merge( $atts, $items );
		monsterinsights_mp_track_event_call( $atts );
	}

	private function track_checkout_v4( $product_id ) {
		if ( ! function_exists( 'monsterinsights_get_v4_id_to_output' ) ||
		     ! function_exists( 'monsterinsights_mp_collect_v4' ) ||
		     ! monsterinsights_get_v4_id_to_output()
		) {
			return;
		}
		$obj = new MeprProduct( $product_id );

		$items = array(
			array(
				'item_id'   => $product_id,
				'item_name' => $obj->post_title,
				'price'     => MonsterInsights_eCommerce_Helper::round_price( $obj->price ),
				'quantity'  => 1,
			),
		);

		$args = array(
			'events' => array(
				array(
					'name'   => 'begin_checkout',
					'params' => array(
						'items' => $items,
					),
				),
			),
		);

		if ( monsterinsights_get_option( 'userid', false ) && is_user_logged_in() ) {
			$args['user_id'] = get_current_user_id(); // UserID tracking
		}

		monsterinsights_mp_collect_v4( $args );
	}

	public function checkout_page( $product_id ) {

		// If page refresh, don't re-track
		if ( monsterinsights_is_page_reload() ) {
			return;
		}

		// Don't track test mode.
		if ( MonsterInsights_eCommerce_Helper::mepr_test_mode() ) {
			return;
		}

		$this->track_checkout_ua( $product_id );
		$this->track_checkout_v4( $product_id );
	}

	public function save_user_cid( $payment_id ) {
		$tracked_already = get_post_meta( $payment_id, '_yoast_gau_uuid', true );

		// Don't track checkout complete if already sent
		if ( ! empty( $tracked_already ) ) {
			return;
		}

		$ga_uuid = monsterinsights_get_client_id();
		if ( $ga_uuid ) {
			$cookie = monsterinsights_get_cookie();
			update_post_meta( $payment_id, '_yoast_gau_uuid',   $ga_uuid );
			update_post_meta( $payment_id, '_monsterinsights_cookie', $cookie );
		}
	}

	private function track_transaction_ua( $txn, $cid, $discount, $mepr_options ) {
		if ( ! monsterinsights_get_ua_to_output() ) {
			return;
		}

		$affiliate_id = '';

		if ( MonsterInsights_eCommerce_Helper::easy_affiliate()->is_easy_affiliate_active() ) {
			$affiliate_id = MonsterInsights_eCommerce_Helper::easy_affiliate()->get_easy_affiliation_memberpress_affiliate_id( $txn );
		}

		if ( MonsterInsights_eCommerce_Helper::is_affiliate_wp_active() ) {
			$affiliate_id = MonsterInsights_eCommerce_Helper::get_affiliate_wp_affiliate_id( $txn, 'memberpress' );
		}

		$atts = array(
			't'   => 'event',                                                // Type of hit
			'ec'  => 'Checkout',                                             // Event Category
			'ea'  => 'Completed Checkout',                                   // Event Action
			'el'  => $txn->id,                                               // Event Label
			'ev'  => round( $txn->total * 100 ),                             // Event Value
			'cos' => 2,                                                      // Checkout Step
			'pa'  => $this->get_funnel_action( 'completed_purchase' ),       // Product Action
			'cid' => $cid,                                                   // GA Client ID
			'ti'  => $txn->id,                                               // Transaction ID
			'ta'  => $affiliate_id,                                          // Affiliation
			'tr'  => $txn->total,                                            // Revenue
			'tt'  => $txn->tax_amount,                                       // Taxes
			'ts'  => 0.00,                                                   // Shipping
			'tcc' => $discount,                                              // Discount code
			'cu'  => $mepr_options->currency_code,                           // Currency
		);

		if ( monsterinsights_get_option( 'userid', false ) ) {
			$atts['uid'] = $txn->user_id; // UserID tracking
		}

		// Declare items in cart
		$obj            = new MeprProduct( $txn->product_id );
		$items          = array();
		$items["pr1id"] = $txn->product_id;                                                                  // Product ID
		$items["pr1nm"] = $obj->post_title;                                                                  // Product Name
		//$items["pr1ca"]  = $first_category;    			 // Product Category
		//$items["pr1va"]  = $variation;        			 // Product Variation Title
		$items["pr1pr"] = MonsterInsights_eCommerce_Helper::round_price( $txn->amount );     // Product Price
		$items["pr1qt"] = 1;                                                                                 // Product Quantity
		$items["pr1ps"] = 1;                                                                                 // Product Order

		$atts = array_merge( $atts, $items );
		monsterinsights_mp_track_event_call( $atts );
	}

	private function track_transaction_v4( $txn, $cid, $discount, $mepr_options ) {
		if ( ! function_exists( 'monsterinsights_get_v4_id_to_output' ) ||
		     ! function_exists( 'monsterinsights_mp_collect_v4' ) ||
		     ! monsterinsights_get_v4_id_to_output()
		) {
			return;
		}

		$obj   = new MeprProduct( $txn->product_id );
		$items = array(
			array(
				'item_id'   => $txn->product_id,
				'item_name' => $obj->post_title,
				'price'     => MonsterInsights_eCommerce_Helper::round_price( $txn->amount ),
				'quantity'  => 1,
			),
		);

		$events = array(
			array(
				'name'   => 'purchase',
				'params' => array(
					'transaction_id' => $txn->id,
					'items'          => $items,
					'value'          => MonsterInsights_eCommerce_Helper::round_price( $txn->total ),
					'tax'            => $txn->tax_amount,
					'shipping'       => 0.00,
					'coupon'         => $discount,
					'currency'       => $mepr_options->currency_code,
				),
			),
		);

		if ( MonsterInsights_eCommerce_Helper::easy_affiliate()->is_easy_affiliate_active() ) {

			$affiliate_id = MonsterInsights_eCommerce_Helper::easy_affiliate()->get_easy_affiliation_woo_affiliate_id( $txn );

			if ( is_int( $affiliate_id ) && $affiliate_id > 0 ) {
				$events[0]['params']['affiliation'] = $affiliate_id;
			}
		}

		if ( MonsterInsights_eCommerce_Helper::is_affiliate_wp_active() ) {

			$affiliate_id = MonsterInsights_eCommerce_Helper::get_affiliate_wp_affiliate_id( $txn, 'memberpress' );

			if ( is_int( $affiliate_id ) && $affiliate_id > 0 ) {
				$events[0]['params']['affiliation'] = $affiliate_id;
			}
		}

		$args = array(
			'client_id' => $cid,
			'events'    => $events,
		);

		if ( monsterinsights_get_option( 'userid', false ) ) {
			$args['user_id'] = $txn->user_id; // UserID tracking
		}

		monsterinsights_mp_collect_v4( $args );
	}

	/**
	 * @param MeprTransaction $txn The transaction object.
	 */
	public function do_transaction( $txn ) {
		if ( ! is_object( $txn ) ) {
			return;
		}

		// Don't track test mode.
		if ( MonsterInsights_eCommerce_Helper::mepr_test_mode() ) {
			return;
		}

		// Don't report transactions that are not payments.
		if ( ! empty( $txn->txn_type ) && MeprTransaction::$payment_str !== $txn->txn_type ) {
			return;
		}

		$skip_renewals = apply_filters( 'monsterinsights_ecommerce_skip_renewals', true );
		if ( $skip_renewals && $txn->is_rebill() ) {
			return;
		}

  		$is_in_ga = $txn->get_meta( '_monsterinsights_is_in_ga', true );
		$skip_ga  = apply_filters( 'monsterinsights_ecommerce_do_transaction_skip_ga', false, $txn->id );
		if ( $is_in_ga === 'yes' || $skip_ga ) {
			return;
		}

		$mepr_options = MeprOptions::fetch();
		$cid 		  = $txn->get_meta( $this->uuid_meta_key, true );
		$discount     = $txn->coupon_id;

		// If no CID, attempt to grab it from the original transaction.
		if ( empty( $cid ) ) {
			$sub       = new MeprSubscription( $txn->subscription_id );
			$first_txn = $sub->first_txn();
			$cid       = $first_txn->get_meta( $this->uuid_meta_key, true );
		}

		$this->track_transaction_ua( $txn, $cid, $discount, $mepr_options );
		$this->track_transaction_v4( $txn, $cid, $discount, $mepr_options );

		// Update in GA
		$txn->update_meta( '_monsterinsights_is_in_ga', 'yes' );
	}

	private function track_refund_ua( $txn, $cid ) {
		if ( ! monsterinsights_get_ua_to_output() ) {
			return;
		}

		$atts = array(
			't'   => 'event', // Type of hit
			'ec'  => 'Orders', // Event Category
			'ea'  => 'Refunded', // Event Action
			'el'  => $txn->id, // Event Label
			'ev'  => - 1 * round( $txn->total * 100 ), // Event Value
			'cid' => $cid, // GA Client ID
		);

		$ee_atts = array(
			'pa' => 'refund', // Product Action
			'ti' => $txn->id, // Transaction ID
		);

		// If it's a full refund, then it's an EE event
		$atts = array_merge( $atts, $ee_atts );

		if ( monsterinsights_get_option( 'userid', false ) ) {
			$atts['uid'] = $txn->user_id; // UserID tracking
		}

		monsterinsights_mp_track_event_call( $atts );
	}

	private function track_refund_v4( $txn, $cid ) {
		if ( ! function_exists( 'monsterinsights_get_v4_id_to_output' ) ||
		     ! function_exists( 'monsterinsights_mp_collect_v4' ) ||
		     ! monsterinsights_get_v4_id_to_output()
		) {
			return;
		}

		$events = array(
			array(
				'name'   => 'refund',
				'params' => array(
					'transaction_id' => $txn->id,
					'value' => MonsterInsights_eCommerce_Helper::round_price( $txn->total ),
				),
			),
		);

		$args = array(
			'client_id' => $cid,
			'events'    => $events,
		);

		if ( monsterinsights_get_option( 'userid', false ) ) {
			$args['user_id'] = $txn->user_id;
		}

		monsterinsights_mp_collect_v4( $args );
	}

	public function undo_transaction( $txn ) {
		if ( ! is_object( $txn ) ) {
			return;
		}

		$skip_renewals  = apply_filters( 'monsterinsights_ecommerce_skip_renewals', true );
		if ( $skip_renewals && $txn->is_rebill() ) {
			return;
		}

		// Don't report transactions that are not payments.
		if ( ! empty( $txn->txn_type ) && MeprTransaction::$payment_str !== $txn->txn_type ) {
			return;
		}

		$is_in_ga = $txn->get_meta( '_monsterinsights_refund_is_in_ga', true );
		$skip_ga  = apply_filters( 'monsterinsights_ecommerce_undo_transaction_skip_ga', false, $txn->id );
		if ( $is_in_ga === 'yes' || $skip_ga ) {
			return;
		}

		$mepr_options = MeprOptions::fetch();
		$cid 		  = $txn->get_meta( $this->uuid_meta_key, true );
		$discount     = $txn->coupon_id;

		// If no CID, attempt to grab it from the original transaction.
		if ( empty( $cid ) ) {
			$sub       = new MeprSubscription( $txn->subscription_id );
			$first_txn = $sub->first_txn();
			$cid       = $first_txn->get_meta( $this->uuid_meta_key, true );
		}

		$this->track_refund_ua( $txn, $cid );
		$this->track_refund_v4( $txn, $cid );

		$txn->update_meta( '_monsterinsights_refund_is_in_ga', 'yes' );
	}

	/**
	 * Add utm_nooverride to the PayPal return URL so the original source of the transaction won't be overridden.
	 *
	 * @since 7.3.0
	 *
	 * @param array $paypal_args
	 *
	 * @link  https://support.bigcommerce.com/questions/1693/How+to+properly+track+orders+in+Google+Analytics+when+you+accept+PayPal+as+a+method+of+payment.
	 *
	 * @return array
	 */
	public function change_paypal_return_url( $paypal_url ) {
		// If already added, remove
		$paypal_url = remove_query_arg( 'utm_nooverride', $paypal_url );

		// Add UTM no override
		$paypal_url = add_query_arg( 'utm_nooverride', '1', $paypal_url );
		return $paypal_url;
	}

	private function get_funnel_steps() {
		return array(
			'started_checkout' => array(
				'action' => 'checkout',
				'step'   => 1,
			),
			'completed_purchase' => array(
				'action' => 'purchase',
				'step'   => 2,
			),
		);
	}
}
