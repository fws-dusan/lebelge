<?php
/**
 * Class AjaxHandler
 *
 * @package WPDesk\FS\ConditionalMethods\Conditions\ProductCategory
 */

namespace WPDesk\FS\ConditionalMethods\Conditions\ProductCategory;

use FSConditionalMethodsVendor\WPDesk\PluginBuilder\Plugin\Hookable;
use WP_Term;
use WPDesk\FS\ConditionalMethods\Conditions\Helper\Option;
use WPDesk\FS\ConditionalMethods\Conditions\Helper\Term;

/**
 * Can handle AJAX request.
 */
class AjaxHandler implements Hookable {
	use Term;
	use Option;

	const NONCE_ACTION = 'product_category';
	const AJAX_ACTION = 'flexible-shipping-conditional-methods-product-category';

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

		$search_text = isset( $_GET['s'] ) ? wc_clean( wp_unslash( $_GET['s'] ) ) : ''; // phpcs:ignore.

		$found_categories = array();

		/** @var WP_Term[] $terms */
		$terms = get_terms(
			array(
				'taxonomy'   => array( 'product_cat' ),
				'orderby'    => 'id',
				'order'      => 'ASC',
				'hide_empty' => false,
				'fields'     => 'all',
				'name__like' => $search_text,
			)
		);

		if ( $terms && is_array( $terms ) ) {
			foreach ( $terms as $term ) {
				$found_categories[] = $this->prepare_option( (string) $term->term_id, $this->get_term_formatted_name( $term ) );
			}
		}

		wp_send_json( $found_categories );
	}
}
