<?php
/**
 * Trait Term
 *
 * @package WPDesk\FS\ConditionalMethods\Conditions\Helper
 */

namespace WPDesk\FS\ConditionalMethods\Conditions\Helper;

use WP_Term;
use WPDesk\FS\ConditionalMethods\Conditions\ProductCategory;


trait Term {
	/**
	 * @param WP_Term $term .
	 *
	 * @return string
	 */
	private function get_term_formatted_name( WP_Term $term ) {
		$formatted_name = get_term_parents_list(
			$term->term_id,
			$term->taxonomy,
			array(
				'separator' => ProductCategory::CATEGORY_SEPARATOR,
				'link'      => false,
			)
		);

		if ( is_wp_error( $formatted_name ) ) {
			return '';
		}

		return trim( $formatted_name, ProductCategory::CATEGORY_SEPARATOR );
	}
}
