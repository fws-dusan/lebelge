<?php

/**
 * Class MonsterInsights_GA_RCP_eCommerce_Tracking
 *
 * Tracks Restrict Content Pro transactions as soon as they're set to paid on the server.
 *
 * @since 7.4.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MonsterInsights_GA_RCP_eCommerce_Tracking extends MonsterInsights_eCommerce_Tracking_Abstract {

	/**
	 * When order is processed, there is a payment_id created. From that moment the user_id can be saved
	 *
	 * @var string
	 */
	protected $store_user_id_hook = 'rcp_create_payment';

	/**
	 * In RCP the name of the order post type is 'rcp-payments'
	 *
	 * @var string
	 */
	protected $order_post_type = '';

	/**
	 * This method will return the value of $this->store_user_id_hook.
	 *
	 * This hook is used for saving the user id, after created a payment. So there will be a payment_id existing
	 *
	 * @since 6.0.0
	 *
	 * @return mixed|string
	 */
	protected function get_store_user_id_hook() {
		return $this->store_user_id_hook;
	}

	/**
	 * Override the parent function to bypass the check for post type. As in this plugin
	 * the payments are stored in a custom table which does not have a post type
	 *
	 * @since 7.4.0
	 *
	 * @param int $payment_id ID of the payment.
	 *
	 * @return bool
	 */
	protected function check_payment_post_type( $payment_id ) {
		return true;
	}

	/**
	 * Get user ID of purchaser.
	 *
	 * @since 6.0.3
	 *
	 * @return void
	 */
	protected function get_user_id( $payment_id = 0, $args = [] ) {
		$user_id = 0;

		if ( is_array( $args ) && ! empty( $args ) ) {
			if ( array_key_exists( 'user_id', $args ) ) {
				$user_id = absint( $args['user_id'] );
			}
		}

		return $user_id;
	}

	/**
	 * This method will return the value of $this->order_post_type.
	 *
	 * This is used to ensure we're detecting the right kind of post.
	 *
	 * @since 6.0.0
	 *
	 * @return mixed|string
	 */
	protected function get_order_post_type() {
		return $this->order_post_type;
	}


	/**
	 * This method will add the actions to add/remove orders to GA on.
	 *
	 * This hook is used for changing the status of the payment.
	 *
	 * @since 6.0.0
	 *
	 * @return void
	 */
	protected function get_order_actions() {
		// When to send to GA
		add_action( 'rcp_update_payment_status_complete', array( $this, 'maybe_do_transaction' ), 10 );

		// When to remove from GA
		add_action( 'rcp_update_payment_status_refunded',  array( $this, 'maybe_undo_transaction' ), 10 );
		add_action( 'rcp_update_payment_status_failed',  array( $this, 'maybe_undo_transaction' ), 10 );
		add_action( 'rcp_update_payment_status_abandoned',  array( $this, 'maybe_undo_transaction' ), 10 );
	}

	/**
	 * This method will determine whether to do the transaction or not.
	 *
	 * @since 6.0.0
	 *
	 * @param int   $payment_id ID of the payment
	 * @param array $args       Arguments for the payment.
	 *                          @see https://help.ithemes.com/hc/en-us/articles/360052452813-rcp-create-payment
	 *
	 * @return void
	 */
	public function maybe_do_transaction( $payment_id = 0 ) {
		$order        = MonsterInsights_eCommerce_Helper::get_rcp_payment( $payment_id );

		// Don't track test mode.
		if ( MonsterInsights_eCommerce_Helper::is_rcp_test_mode( $order->gateway ) ) {
			return;
		}

		$this->do_transaction( $payment_id );
	}

	/**
	 * This method will determine whether to undo the transaction or not.
	 *
	 * @since 6.0.0
	 *
	 * @return void
	 */
	public function maybe_undo_transaction( $payment_id = 0 ) {
		$this->undo_transaction( $payment_id );
	}

	/**
	 * Retrieving the payment method for current payment
	 *
	 * @since 6.0.0
	 *
	 * @param int $payment_id
	 *
	 * @return string
	 */
	protected function get_payment_method( $payment_id ) {
		$rcp_payment = MonsterInsights_eCommerce_Helper::get_rcp_payment( $payment_id );

		return $rcp_payment->payment_type;
	}

	/**
	 * Method for getting the order details.
	 *
	 * @since 6.0.0
	 *
	 * @param int $payment_id
	 *
	 * @return array
	 */
	protected function get_order_details( $payment_id ) {
		// Getting the order details
		$rcp_order = MonsterInsights_eCommerce_Helper::get_rcp_payment( $payment_id );

		// Getting the items in cart
		$items = array(
			array(
				'name'       => $rcp_order->subscription,
				'total'      => $rcp_order->amount,
				'qty'        => 1,
				'product_id' => $rcp_order->object_id,
				'category'   => $rcp_order->object_type
			)
		);

		return array(
			'items'        => $items,
			'total_amount' => $rcp_order->subtotal,
			'total_tax'    => 0,
			'currency'     => rcp_get_currency(),
		);
	}

	/**
	 * Parse each item in format for google analytics, containing all required field
	 *
	 * @since 6.0.0
	 *
	 * @param array $item
	 *
	 * @return array
	 */
	protected function parse_item( $item ) {

		return array(
			'in' => $item['name'],
			'ip' => $item['total'],
			'iq' => $item['qty'],
			'ic' => $item['product_id'],
			'iv' => $item['category']
		);
	}

	/**
	 * Executing the transaction, only when the new status is paid.
	 *
	 * @since 6.0.0
	 *
	 * @param int $payment_id
	 *
	 */
	protected function do_transaction( $payment_id ) {
		$rcp_payments = MonsterInsights_eCommerce_Helper::rcp_payments();
		$order        = MonsterInsights_eCommerce_Helper::get_rcp_payment( $payment_id );

		// Don't track test mode.
		if ( MonsterInsights_eCommerce_Helper::is_rcp_test_mode( $order->gateway ) ) {
			return;
		}

		$is_in_ga = $rcp_payments->get_meta( $payment_id, '_monsterinsights_is_in_ga', true );
		$skip_ga  = apply_filters( 'monsterinsights_ecommerce_do_transaction_skip_ga', false, $payment_id );
		if ( $is_in_ga === 'yes' || $skip_ga ) {
			return;
		}

		$payload = $this->get_payment_payload( $payment_id );

		$this->send_hit( $payload['main'] );

		foreach ( $payload['products'] as $single_payload ) {
			$this->send_hit( $single_payload );
		}

		$rcp_payments->update_meta( $payment_id, '_monsterinsights_is_in_ga', 'yes' );
	}

	/**
	 * Undo the transaction, will executed when going from paid to another status
	 *
	 * @since 6.0.0
	 *
	 * @link  https://support.google.com/analytics/answer/1037443?hl=en
	 *
	 * @param int $payment_id
	 */
	protected function undo_transaction( $payment_id ) {
		$rcp_payments = MonsterInsights_eCommerce_Helper::rcp_payments();
		$order        = MonsterInsights_eCommerce_Helper::get_rcp_payment( $payment_id );

		// Don't track test mode.
		if ( MonsterInsights_eCommerce_Helper::is_rcp_test_mode( $order->gateway ) ) {
			return;
		}

		$is_in_ga = $rcp_payments->get_meta( $payment_id, '_monsterinsights_is_in_ga', true );
		$skip_ga  = apply_filters( 'monsterinsights_ecommerce_undo_transaction_skip_ga', false, $payment_id );
		if ( $is_in_ga !== 'yes' || $skip_ga ) {
			return;
		}

		$payload = $this->get_payment_payload( $payment_id );

		// Reverse the transaction
		$payload['main']['tr'] = 0 - $payload['main']['tr'];
		$payload['main']['tt'] = 0 - $payload['main']['tt'];

		$this->send_hit( $payload['main'] );

		// Reverse each product too
		foreach ( $payload['products'] as $single_payload ) {
			$single_payload['iq'] = 0 - $single_payload['iq'];
			$this->send_hit( $single_payload );
		}

		$rcp_payments->delete_meta( $payment_id, '_monsterinsights_is_in_ga' );
	}

	/**
	 * Default array, with values that should be in every payload
	 *
	 * @since 6.0.0
	 *
	 * @param int $payment_id
	 *
	 * @return array $payload
	 */
	protected function get_default_payload( $payment_id ) {
		$rcp_payments = MonsterInsights_eCommerce_Helper::rcp_payments();

		$ga_uuid  = $rcp_payments->get_meta( $payment_id, $this->uuid_meta_key, true );
		if ( ! is_string( $ga_uuid ) || '' === $ga_uuid ) {
			$ga_uuid   = $this->generate_uuid();
		}

		$payload = array(
			'cid' => $ga_uuid,
			't'   => 'transaction',
			'ti'  => $this->get_order_number( $payment_id ),
			'ta'  => $this->get_payment_method( $payment_id ),
			'ts'  => '0.00',
		);

		$user_id = $this->get_user_id( $payment_id );
		if ( ! empty( $user_id ) ) {
			$payload['uid'] = $user_id;
		}

		return $payload;
	}

	/**
	 * Store the visitor ID and attached experiments and variations, as stored in the cookie, with the transaction.
	 *
	 * @since 6.0.0
	 *
	 * @param int $payment_id The ID of the payment to attached the data to.
	 */
	public function store_user_id( $payment_id ) {
		$rcp_payments = MonsterInsights_eCommerce_Helper::rcp_payments();

		$ga_uuid = $this->read_cookie();
		if ( $ga_uuid ) {
			$cookie = $this->get_cookie();
			$rcp_payments->update_meta( $payment_id, $this->uuid_meta_key,   $ga_uuid );
			$rcp_payments->update_meta( $payment_id, $this->cookie_meta_key, $cookie );
		}
	}
}
