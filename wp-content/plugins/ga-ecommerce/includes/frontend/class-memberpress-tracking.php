<?php

/**
 * Class MonsterInsights_GA_MemberPress_eCommerce_Tracking
 *
 * Tracks MemberPress transactions as soon as they're set to paid on the server.
 *
 * @since 7.3.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MonsterInsights_GA_MemberPress_eCommerce_Tracking {

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

	public function __construct() {
		$this->get_order_actions();
	}

	/**
	 * This method will add the actions to add/remove orders to GA on.
	 *
	 * This hook is used for changing the status of the payment.
	 *
	 * @since 7.2.0
	 *
	 * @return void
	 */
	public function get_order_actions() {
		// Store cookie
		add_action( $this->store_user_id_hook, array( $this, 'store_user_id' ), 10 );

		// When to add to GA
		foreach ( $this->add_to_ga_hooks as $hook ) {
			add_action( $hook, array( $this, 'do_transaction' ), 10 );
		}

		// When to remove from GA
		foreach ( $this->remove_from_ga_hooks as $hook ) {
			add_action( $hook, array( $this, 'undo_transaction' ), 10 );
		}
	}

	/**
	 * Store the visitor ID and attached experiments and variations, as stored in the cookie, with the transaction.
	 *
	 * @since 7.3.0
	 *
	 * @param MeprTransaction $txn Transaction.
	 */
	public function store_user_id( $txn ) {
		$ga_uuid = $this->read_cookie();
		if ( $ga_uuid ) {
			$cookie = $this->get_cookie();
			$txn = new MeprTransaction( $txn->id );
			$txn->update_meta( $this->uuid_meta_key, $ga_uuid );
			$txn->update_meta( $this->cookie_meta_key, $cookie );
		}
	}

	/**
	 * Executing the transaction, only when the new status is paid.
	 *
	 * @since 7.3.0
	 *
	 * @param MeprTransaction $txn Transaction.
	 *
	 */
	public function do_transaction( $txn ) {
		if ( ! is_object( $txn ) ) {
			return;
		}

		// Don't track test mode.
		if ( MonsterInsights_eCommerce_Helper::mepr_test_mode() ) {
			return;
		}


  		$is_in_ga = $txn->get_meta( '_monsterinsights_is_in_ga', true );
		$skip_ga  = apply_filters( 'monsterinsights_ecommerce_do_transaction_skip_ga', false, $txn->id );
		if ( $is_in_ga === 'yes' || $skip_ga ) {
			return;
		}

		$mepr_options = MeprOptions::fetch();
		$cid 		 = $txn->get_meta( $this->uuid_meta_key, true );

		// If no CID, attempt to grab it from the original transaction.
		if ( empty( $cid ) ) {
			$sub       = new MeprSubscription( $txn->subscription_id );
			$first_txn = $sub->first_txn();
			$cid       = $first_txn->get_meta( $this->uuid_meta_key, true );
		}

		// Payload to add order
		monsterinsights_mp_api_call(
			array(
				'cid' => $cid,             				// Anonymous Client ID.
				't'   => 'transaction',    				// Transaction hit type.
				'ti'  => $txn->id,        				// transaction ID. Required.
				'ta'  => $txn->gateway,    				// Transaction affiliation.
				'tr'  => $txn->total,     				// Transaction revenue.
				'ts'  => 0.00,             				// Transaction shipping.
				'tt'  => $txn->tax_amount, 				// Transaction tax.
				'cu'  => $mepr_options->currency_code   // Currency code.
			)
		);

		// Payload to add product
		$obj = new MeprProduct( $txn->product_id );
		monsterinsights_mp_api_call(
			array(
				'cid' => $cid,           				  // Anonymous Client ID.
				't'   => 'item', 						  // Item hit type.
				'ti'  => $txn->id,        				  // Transaction ID. Required.
				'in'  => $obj->post_title,         		  // Item name. Required.
				'ip'  => $txn->amount,           		  // Item price.
				'iq'  => 1,             				  // Item quantity.
				'ic'  => $txn->product_id,     		      // Item code / SKU.
				//'iv' =>,      						  // Item variation / category.
				'cu'  => $mepr_options->currency_code,    // Currency code.

			)
		);

		// Update in GA
		$txn->update_meta( '_monsterinsights_is_in_ga', 'yes' );
	}

	/**
	 * Undo the transaction, will executed when going from paid to another status
	 *
	 * @since 7.3.0
	 *
	 * @link  https://support.google.com/analytics/answer/1037443?hl=en
	 *
	 * @param MeprTransaction $txn Transaction.
	 */
	public function undo_transaction( $txn ) {
		if ( ! is_object( $txn ) ) {
			return;
		}

		$is_in_ga = $txn->get_meta( '_monsterinsights_is_in_ga', true );
		$skip_ga  = apply_filters( 'monsterinsights_ecommerce_undo_transaction_skip_ga', false, $txn->id );
		if ( $is_in_ga !== 'yes' || $skip_ga ) {
			return;
		}

		$mepr_options = MeprOptions::fetch();
		$cid 		 = $txn->get_meta( $this->uuid_meta_key, true );

		// If no CID, attempt to grab it from the original transaction.
		if ( empty( $cid ) ) {
			$sub       = new MeprSubscription( $txn->subscription_id );
			$first_txn = $sub->first_txn();
			$cid       = $first_txn->get_meta( $this->uuid_meta_key, true );
		}

		// Payload to remove order
		monsterinsights_mp_api_call(
			array(
				'cid' => $cid,             				// Anonymous Client ID.
				't'   => 'transaction',    				// Transaction hit type.
				'ti'  => $txn->id,        				// transaction ID. Required.
				'ta'  => $txn->gateway,    				// Transaction affiliation.
				'tr'  => 0 - $txn->total,     			// Transaction revenue.
				'ts'  => 0.00,             				// Transaction shipping.
				'tt'  => 0 - $txn->tax_amount, 			// Transaction tax.
				'cu'  => $mepr_options->currency_code   // Currency code.
			)
		);

		// Payload to remove product
		$obj = new MeprProduct( $txn->product_id );
		monsterinsights_mp_api_call(
			array(
				'cid' => $cid,           				  // Anonymous Client ID.
				't'   => 'item', 						  // Item hit type.
				'ti'  => $txn->id,        				  // Transaction ID. Required.
				'in'  => $obj->post_title,         		  // Item name. Required.
				'ip'  => 0 - $txn->amount,           	  // Item price.
				'iq'  => 1,             				  // Item quantity.
				'ic'  => $txn->product_id,     		      // Item code / SKU.
				//'iv' =>,      						  // Item variation / category.
				'cu'  => $mepr_options->currency_code,    // Currency code.

			)
		);

		$txn->delete_meta( '_monsterinsights_is_in_ga' );
	}


	/**
	 * Returns the Google Analytics clientId to store for later use
	 *
	 * @since 7.3.0
	 *
	 * @link  https://developers.google.com/analytics/devguides/collection/analyticsjs/domains#getClientId
	 *
	 * @return bool|string False if cookie isn't set, GA UUID otherwise
	 */
	public function read_cookie() {
		if ( empty( $_COOKIE['_ga'] ) ) {
			return false;
		}

		/**
		 * Example cookie formats:
		 *
		 * GA1.2.XXXXXXX.YYYYY
		 * _ga=1.2.XXXXXXX.YYYYYY -- We want the XXXXXXX.YYYYYY part
		 *
		 */

		$ga_cookie    = $_COOKIE['_ga'];
		$cookie_parts = explode('.', $ga_cookie );
		if ( is_array( $cookie_parts ) && ! empty( $cookie_parts[2] ) && ! empty( $cookie_parts[3] ) ) {
			$uuid = (string) $cookie_parts[2] . '.' . (string) $cookie_parts[3];
			if ( is_string( $uuid ) ) {
				return $uuid;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	/**
	 * Returns the Google Analytics clientId to store for later use
	 *
	 * @since 7.3.0
	 *
	 * @return GA UUID or error code.
	 */
	public function get_cookie() {
		if ( empty( $_COOKIE['_ga'] ) ) {
			return 'FCE';
		}

		$ga_cookie    = $_COOKIE['_ga'];
		$cookie_parts = explode('.', $ga_cookie );
		if ( is_array( $cookie_parts ) && ! empty( $cookie_parts[2] ) && ! empty( $cookie_parts[3] ) ) {
			$uuid = (string) $cookie_parts[2] . '.' . (string) $cookie_parts[3];
			if ( is_string( $uuid ) ) {
				return $ga_cookie;
			} else {
				return 'FA';
			}
		} else {
			return 'FAE';
		}
	}

	/**
	 * Generate UUID v4 function - needed to generate a CID when one isn't available
	 *
	 * @link http://www.stumiller.me/implementing-google-analytics-measurement-protocol-in-php-and-wordpress/
	 *
	 * @since 7.3.0
	 * @return string
	 */
	public function generate_uuid() {

		return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',

			// 32 bits for "time_low"
			mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),

			// 16 bits for "time_mid"
			mt_rand( 0, 0xffff ),

			// 16 bits for "time_hi_and_version",
			// four most significant bits holds version number 4
			mt_rand( 0, 0x0fff ) | 0x4000,

			// 16 bits, 8 bits for "clk_seq_hi_res",
			// 8 bits for "clk_seq_low",
			// two most significant bits holds zero and one for variant DCE1.1
			mt_rand( 0, 0x3fff ) | 0x8000,

			// 48 bits for "node"
			mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
		);
	}
}
