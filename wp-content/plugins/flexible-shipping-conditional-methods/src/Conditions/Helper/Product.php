<?php
/**
 * Trait Product
 *
 * @package WPDesk\FS\ConditionalMethods\Conditions\Helper
 */

namespace WPDesk\FS\ConditionalMethods\Conditions\Helper;

use WC_Product;

trait Product {
	/**
	 * @param WC_Product $product .
	 *
	 * @return WC_Product
	 */
	private function get_product_from_item( $product ) {
		$parent_id = $product->get_parent_id();

		if ( ! $parent_id ) {
			return $product;
		}

		$parent_product = wc_get_product( $parent_id );

		return $parent_product ? $parent_product : $product;
	}
}
