<?php

/**
 * Class MonsterInsights_GA_GiveWP_Tracking
 *
 * Tracks GiveWP transactions for donations as soon as they're set to paid on the server.
 *
 * Note: In this integration the "order' shall be assumed as "donation". As GiveWP plugin deals only with
 * donations.
 *
 * @since 7.4.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MonsterInsights_GA_GiveWP_Tracking extends MonsterInsights_eCommerce_Tracking_Abstract {

	/**
	 * When donation is processed, there is a payment_id created. From that moment the user_id can be saved
	 *
	 * @var string
	 */
	protected $store_user_id_hook = 'give_complete_donation';

	/**
	 * In GiveWP the name of the donation post type is 'give_payment'
	 *
	 * @var string
	 */
	protected $order_post_type = 'give_payment';

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
	 * Get Donor ID.
	 *
	 * @since 6.0.3
	 *
	 * @return void
	 */
	protected function get_user_id( $payment_id = 0 ) {
		$donor_id  = ( give_is_guest_payment( $payment_id ) ) ? absint( give_get_payment_donor_id( $payment_id ) ) : give_get_payment_user_id( $payment_id );

		return $donor_id;
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
		add_action( 'give_complete_donation', array( $this, 'maybe_do_transaction' ), 10 );

		// When to remove from GA
		add_action( 'give_update_payment_status',  array( $this, 'maybe_undo_transaction_as_per_status' ), 10, 3 );
		add_action( 'give_payment_deleted',  array( $this, 'maybe_undo_transaction' ), 10 );
	}

	/**
	 * This method will determine whether to do the transaction or not.
	 *
	 * @since 6.0.0
	 *
	 * @return void
	 */
	public function maybe_do_transaction( $payment_id = 0 ) {
		// Don't track test mode.
		if ( MonsterInsights_eCommerce_Helper::givewp_is_test_mode() ) {
			return;
		}

		$this->do_transaction( $payment_id );
	}

	/**
	 * This method will determine whether to undo the transaction or not based on the order status.
	 *
	 * @since 7.4.0
	 *
	 * @return void
	 */
	public function maybe_undo_transaction_as_per_status( $payment_id, $new_status, $old_status ) {

		$negative_statuses = MonsterInsights_eCommerce_Helper::givewp_negative_statutes();

		if ( ! in_array( $new_status, $negative_statuses, true ) ) {
			return;
		}

		$this->undo_transaction( $payment_id );
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
	 * Retrieving the payment method from the custom wp_give_donationmeta table for current payment
	 *
	 * @since 7.4.0
	 *
	 * @param int $payment_id
	 *
	 * @return string
	 */
	protected function get_payment_method( $payment_id ) {
		return give_get_payment_meta( $payment_id, '_give_payment_mode' );
	}

	/**
	 * Method for getting the order details from GiveWP
	 *
	 * @since 6.0.0
	 *
	 * @param int $payment_id
	 *
	 * @return array
	 */
	protected function get_order_details( $payment_id ) {

		$total    = give_get_payment_total( $payment_id );
		$currency = give_get_payment_currency_code( $payment_id );

		$items = array(
			array(
				'name'       => get_the_title( $payment_id ),
				'total'      => $total,
				'qty'        => 1,
				'product_id' => $payment_id
			)
		);

		return array(
			'items'        => $items,
			'total_amount' => $total,
			'total_tax'    => 0,
			'currency'     => $currency,
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
		);
	}

	/**
	 * Getting the order number.
	 *
	 * Instead of payment_id maybe there is a custom order_number
	 *
	 * @param integer $payment_id
	 *
	 * @return integer
	 */
	protected function get_order_number( $payment_id ) {
		return MonsterInsights_eCommerce_Helper::givewp_donation_id( $payment_id );
	}
}
