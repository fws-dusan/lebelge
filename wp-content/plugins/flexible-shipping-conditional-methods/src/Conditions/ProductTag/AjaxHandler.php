<?php
/**
 * Class AjaxHandler
 *
 * @package WPDesk\FS\ConditionalMethods\Conditions\ProductTag
 */

namespace WPDesk\FS\ConditionalMethods\Conditions\ProductTag;

use FSConditionalMethodsVendor\WPDesk\PluginBuilder\Plugin\Hookable;
use WP_Term;
use WPDesk\FS\ConditionalMethods\Conditions\Helper\Option;

/**
 * Can handle AJAX request.
 */
class AjaxHandler implements Hookable {
	use Option;

	const NONCE_ACTION = 'product_tag';
	const AJAX_ACTION = 'flexible-shipping-conditional-methods-product-tag';

	/**
	 * Hooks.
	 */
	public function hooks() {
		add_action( 'wp_ajax_' . self::AJAX_ACTION, array( $this, 'ajax_get_items' ) );
	}

	/**
	 * .
	 *
	 * @return void
	 * @internal
	 */
	public function ajax_get_items() {
		check_ajax_referer( self::NONCE_ACTION, 'security' );

		$product_tags = array();
		$search_text  = isset( $_GET['s'] ) ? wc_clean( wp_unslash( $_GET['s'] ) ) : ''; // phpcs:ignore.

		/** @var WP_Term[] $terms */
		$terms = get_terms(
			array(
				'taxonomy'   => 'product_tag',
				'hide_empty' => false,
				'name__like' => $search_text,
			)
		);

		if ( is_array( $terms ) ) {
			foreach ( $terms as $term ) {
				$product_tags[] = $this->prepare_option( (string) $term->term_id, $term->name );
			}
		}

		wp_send_json( $product_tags );
	}
}
