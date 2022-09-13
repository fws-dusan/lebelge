<?php
/**
 * Class ProductCategory
 *
 * @package WPDesk\FS\ConditionalMethods\Conditions
 */

namespace WPDesk\FS\ConditionalMethods\Conditions;

use FSConditionalMethodsVendor\WPDesk\Forms\Field;
use FSConditionalMethodsVendor\WPDesk\Forms\Field\SelectField;
use FSConditionalMethodsVendor\WPDesk\Forms\Field\WooSelect;
use WC_Cart;
use WP_Term;
use WPDesk\FS\ConditionalMethods\Conditions\Helper\Option;
use WPDesk\FS\ConditionalMethods\Conditions\Helper\Product;
use WPDesk\FS\ConditionalMethods\Conditions\Helper\Term;
use WPDesk\FS\ConditionalMethods\Conditions\ProductCategory\AjaxHandler;

/**
 * Product category condition.
 */
class ProductCategory extends AbstractCondition {
	use Term;
	use Option;
	use Product;

	const CATEGORY_SEPARATOR = ' > ';

	/**
	 * ProductCategory constructor.
	 */
	public function __construct() {
		parent::__construct(
			'product_category',
			__( 'Product category', 'flexible-shipping-conditional-methods' ),
			__( 'The Actions defined further will be taken for the selected shipping methods if the Condition based on Product category is met for the cart or package.', 'flexible-shipping-conditional-methods' ),
			__( 'Product', 'flexible-shipping-conditional-methods' ),
			10
		);
	}

	/**
	 * @return Field[]
	 */
	public function get_fields() {
		$fields = array(
			$this->prepare_source_select(),
			$this->prepare_operator_matches(),
			( new WooSelect() )
				->set_name( $this->get_option_id() )
				->set_multiple()
				->add_class( 'product_category' )
				->set_placeholder( __( 'search product category', 'flexible-shipping-conditional-methods' ) )
				->add_data( 'ajax-url', $this->get_ajax_url() )
				->add_data( 'async', true )
				->add_data( 'autoload', true )
				->set_label( ' ' ),
		);

		return $fields;
	}

	/**
	 * @return SelectField
	 */
	private function prepare_source_select() {
		return ( new SelectField() )
			->set_name( 'source' )
			->set_options(
				array(
					array(
						'value' => 'cart',
						'label' => _x( 'cart', 'product category', 'flexible-shipping-conditional-methods' ),
					),
					array(
						'value' => 'package',
						'label' => _x( 'package', 'product category', 'flexible-shipping-conditional-methods' ),
					),
				)
			)
			->set_label( _x( 'in the', 'product category', 'flexible-shipping-conditional-methods' ) );
	}

	/**
	 * @return SelectField
	 */
	private function prepare_operator_matches() {
		return ( new SelectField() )
			->set_name( 'matches' )
			->set_options(
				array(
					array(
						'value' => 'any',
						'label' => _x( 'any of', 'product category', 'flexible-shipping-conditional-methods' ),
					),
					array(
						'value' => 'all',
						'label' => _x( 'all of', 'product category', 'flexible-shipping-conditional-methods' ),
					),
					array(
						'value' => 'none',
						'label' => _x( 'none of', 'product category', 'flexible-shipping-conditional-methods' ),
					),
				)
			)
			->set_label( _x( 'matches', 'product category', 'flexible-shipping-conditional-methods' ) );
	}

	/**
	 * @param array<string, string|array> $option_settings .
	 *
	 * @return array<string, string|array>
	 */
	public function prepare_settings( $option_settings ) {
		$categories = $this->get_product_categories( $option_settings );

		if ( ! $categories ) {
			return $option_settings;
		}

		$options = array();
		foreach ( $categories as $term ) {
			$options[] = $this->prepare_option( (string) $term->term_id, $this->get_term_formatted_name( $term ) );
		}

		$option_settings[ $this->option_id . '_options' ] = array_values( $options );

		return $option_settings;
	}

	/**
	 * @param array<string, string|array> $option_settings .
	 *
	 * @return WP_Term[]
	 */
	private function get_product_categories( $option_settings ) {
		if ( ! isset( $option_settings[ $this->option_id ] ) ) {
			return array();
		}

		$items = array();

		foreach ( wp_parse_id_list( $option_settings[ $this->option_id ] ) as $category_id ) {
			$term = get_term( $category_id, 'product_cat' );

			if ( $term instanceof WP_Term ) {
				$items[] = $term;
			}
		}

		return $items;
	}

	/**
	 * @return string
	 */
	private function get_ajax_url() {
		return admin_url( 'admin-ajax.php?action=' . AjaxHandler::AJAX_ACTION . '&security=' . wp_create_nonce( AjaxHandler::NONCE_ACTION ) );
	}

	/**
	 * @param array<string, string|array> $condition_settings .
	 * @param WC_Cart                     $cart               .
	 * @param array[]                     $package            .
	 * @param array[]                     $all_packages       .
	 *
	 * @return bool
	 */
	public function is_condition_matched( array $condition_settings, WC_Cart $cart, array $package, array $all_packages ) {
		$contents = 'package' === $condition_settings['source'] ? $package['contents'] : $cart->cart_contents;
		$matches  = is_string( $condition_settings['matches'] ) ? $condition_settings['matches'] : '';

		$product_categories = isset( $condition_settings[ $this->get_option_id() ] ) ? $condition_settings[ $this->get_option_id() ] : array();
		$product_categories = wp_parse_id_list( $product_categories );
		$product_categories = $this->get_all_categories( $product_categories );

		$contents_product_categories = $this->get_contents_products( $contents );

		return $this->is_operator_matched( $matches, $product_categories, $contents_product_categories );
	}

	/**
	 * @param int[] $category_ids .
	 *
	 * @return int[]
	 */
	private function get_all_categories( $category_ids ) {
		$categories = array();

		foreach ( $category_ids as $category_id ) {
			$categories[] = $category_id;

			$category_children = get_term_children( $category_id, 'product_cat' );

			if ( $category_children && is_array( $category_children ) ) {
				$categories = array_merge( $categories, $category_children );
			}
		}

		return array_filter( array_unique( wp_parse_id_list( $categories ) ) );
	}

	/**
	 * @param array[] $contents .
	 *
	 * @return int[]
	 */
	private function get_contents_products( array $contents ) {
		$contents_product_categories = array();
		foreach ( $contents as $item ) {
			$product = $this->get_product_from_item( $item['data'] );

			$contents_product_categories = array_merge( $contents_product_categories, $product->get_category_ids() );
		}

		return array_unique( array_filter( $contents_product_categories ) );
	}
}
