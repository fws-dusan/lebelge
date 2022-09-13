<?php

/**
 * Class MonsterInsights_GA_LifterLMS_eCommerce_Tracking
 *
 * Tracks LifterLMS transactions as soon as they're set to paid on the server.
 *
 * @since 6.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MonsterInsights_GA_LifterLMS_eCommerce_Tracking extends MonsterInsights_eCommerce_Tracking_Abstract {

	/**
	 * When order is processed, there is a payment_id created. From that moment the user_id can be saved
	 *
	 * @var string
	 */
	protected $store_user_id_hook = 'lifterlms_new_pending_order';

	/**
	 * In LifterLMS the name of the order post type is 'llms_order'
	 *
	 * @var string
	 */
	protected $order_post_type = 'llms_order';

	/**
	 * This method will return the value of $this->store_user_id_hook.
	 *
	 * This hook is used for saving the user id, after created a payment. So there will be a payment_id existing
	 *
	 * @return mixed|string
	 * @since [version]
	 *
	 */
	protected function get_store_user_id_hook() {
		return $this->store_user_id_hook;
	}

	/**
	 * Store the visitor ID and attached experiments and variations, as stored in the cookie, with the transaction.
	 *
	 * Overrides parent because LifterLMS doesn't have a hook that passed the WP_Post ID of the order as the first parameter.
	 *
	 * @param LLMS_Order $order Order object.
	 *
	 * @return  void
	 * @since [version]
	 *
	 */
	public function store_user_id( $order ) {

		$payment_id = 0;
		if ( class_exists( 'LLMS_Order' ) && is_a( $order, 'LLMS_Order' ) ) {
			$payment_id = $order->get( 'id' );
		}

		if ( $payment_id ) {
			parent::store_user_id( $payment_id );
		}

	}

	/**
	 * Get user ID of purchaser.
	 *
	 * @return int
	 * @since [version]
	 *
	 */
	protected function get_user_id( $payment_id = 0 ) {
		if ( function_exists( 'llms_get_post' ) ) {
			$order = llms_get_post( $payment_id );

			return $order ? $order->get( 'user_id' ) : 0;
		}

		return 0;
	}

	/**
	 * This method will return the value of $this->order_post_type.
	 *
	 * This is used to ensure we're detecting the right kind of post.
	 *
	 * @return mixed|string
	 * @since [version]
	 *
	 */
	protected function get_order_post_type() {
		return $this->order_post_type;
	}

	/**
	 * This method will add the actions to add/remove orders to GA on.
	 *
	 * This hook is used for changing the status of the payment.
	 *
	 * @return void
	 * @since 6.0.0
	 *
	 */
	protected function get_order_actions() {

		// When to send to GA.
		add_action( 'lifterlms_order_status_completed', array( $this, 'maybe_do_transaction' ), 10 );
		add_action( 'lifterlms_order_status_active', array( $this, 'maybe_do_transaction' ), 10 );

		// When to remove from GA.
		add_action( 'lifterlms_order_status_refunded', array( $this, 'maybe_undo_transaction' ), 10 );
		add_action( 'lifterlms_order_status_cancelled', array( $this, 'maybe_undo_transaction' ), 10 );
		add_action( 'lifterlms_order_status_failed', array( $this, 'maybe_undo_transaction' ), 10 );
		add_action( 'lifterlms_order_status_on-hold', array( $this, 'maybe_undo_transaction' ), 10 );
		add_action( 'lifterlms_order_status_trash', array( $this, 'maybe_undo_transaction' ), 10 );

	}

	/**
	 * This method will determine whether to do the transaction or not.
	 *
	 * @param LLMS_Order $order Order object.
	 *
	 * @return void
	 * @since [versoin]
	 *
	 */
	public function maybe_do_transaction( $order = null ) {

		// Don't track test mode.
		if ( MonsterInsights_eCommerce_Helper::llms_test_mode() ) {
			return;
		}

		if ( $order ) {
			$this->do_transaction( $order->get( 'id' ) );
		}

	}

	/**
	 * This method will determine whether to undo the transaction or not.
	 *
	 * @param LLMS_Order $order Order object.
	 *
	 * @return void
	 * @since [versoin]
	 *
	 */
	public function maybe_undo_transaction( $order = null ) {

		if ( $order ) {
			$this->undo_transaction( $order->get( 'id' ) );
		}

	}

	/**
	 * Retrieving the payment method from the post_meta for current payment
	 *
	 * @param int $payment_id
	 *
	 * @return string
	 * @since [version]
	 *
	 */
	protected function get_payment_method( $payment_id ) {
		if ( function_exists( 'llms_get_post' ) ) {
			$order = llms_get_post( $payment_id );
			if ( $order ) {
				$gateway = $order->get_gateway();
				if ( ! is_wp_error( $gateway ) && method_exists( $gateway, 'get_admin_title' ) ) {
					return $gateway->get_admin_title();
				}
			}
		}

		// Fallback to the Gateway ID stored in postmeta..
		return get_post_meta( $payment_id, 'payment_gateway', true );
	}

	/**
	 * Method for getting the order details from WooCommerce
	 *
	 * @param int $payment_id
	 *
	 * @return array
	 * @since 6.0.0
	 *
	 */
	protected function get_order_details( $payment_id ) {

		$order = llms_get_post( $payment_id );

		if ( $order && method_exists( $order, 'get' ) ) {
			return array(
				'items'        => array(
					array(
						'name'       => $order->get( 'product_title' ),
						'line_total' => $order->get( 'total' ),
						'qty'        => 1,
						'plan_id'    => $order->get( 'plan_id' ),
						'product_id' => $order->get( 'product_id' ),
					),
				),
				'total_amount' => $order->get( 'total' ),
				'total_tax'    => 0,
				'currency'     => $order->get( 'currency' ),
			);
		} else {
			return array(
				'items'        => array(),
				'total_amount' => 0,
				'total_tax'    => 0,
				'currency'     => '',
			);
		}
	}

	/**
	 * Parse each item in format for google analytics, containing all required field
	 *
	 * @param array $item
	 *
	 * @return array
	 * @since 6.0.0
	 *
	 */
	protected function parse_item( $item ) {

		return array(
			'in' => $item['name'],
			'ip' => $item['line_total'],
			'iq' => $item['qty'],
			'ic' => $this->get_product_sku( $item['plan_id'] ),
			'iv' => $this->get_product_cat( $item['product_id'] ),
		);
	}

	/**
	 * Getting the product SKU if exist otherwise return product_id
	 *
	 * @param integer $product_id
	 *
	 * @return mixed
	 */
	protected function get_product_sku( $product_id ) {

		$plan = llms_get_post( $product_id );
		if ( $plan ) {

			$sku = $plan->get( 'sku' );
			if ( $sku ) {
				return $sku;
			}
		}

		return $product_id;

	}

	/**
	 * Retrieve the slug of the first product term for a given product.
	 *
	 * @param int $product_id WP_Post ID of a course or membership.
	 *
	 * @return [type]
	 * @since [version]
	 *
	 */
	protected function get_product_cat( $product_id ) {

		$tax = false;

		$post_type = get_post_type( $product_id );
		if ( 'course' === $post_type ) {
			$tax = 'course_cat';
		} elseif ( 'llms_membership' === $post_type ) {
			$tax = 'membership_cat';
		}

		if ( $tax ) {

			$item_category = get_the_terms( $product_id, $tax );
			if ( is_array( $item_category ) && ! empty( $item_category[0] ) && is_object( $item_category[0] ) ) {
				return $item_category[0]->slug;
			}

		}

		return '';

	}

}
