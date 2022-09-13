<?php
/**
 * Class ProductTag
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
use WPDesk\FS\ConditionalMethods\Conditions\ProductTag\AjaxHandler;

/**
 * Product tag condition.
 */
class ProductTag extends AbstractCondition {
	use Option;
	use Product;

	/**
	 * ProductTag constructor.
	 */
	public function __construct() {
		parent::__construct(
			'product_tag',
			__( 'Product tag', 'flexible-shipping-conditional-methods' ),
			__( 'The Actions defined further will be taken for the selected shipping methods if the Condition based on Product tag is met for the cart or package.', 'flexible-shipping-conditional-methods' ),
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
				->add_class( 'product_tag' )
				->set_placeholder( __( 'search product tag', 'flexible-shipping-conditional-methods' ) )
				->add_data( 'ajax-url', $this->get_ajax_url() )
				->add_data( 'async', true )
				->add_data( 'autoload', true )
				->set_label( ' ' ),
		);

		return $fields;
	}

	/**
	 * @param string|null $default_value .
	 *
	 * @return SelectField
	 */
	private function prepare_source_select( $default_value = null ) {
		$source_select = ( new SelectField() )
			->set_name( 'source' )
			->set_options(
				array(
					array(
						'value' => 'cart',
						'label' => _x( 'cart', 'product tag', 'flexible-shipping-conditional-methods' ),
					),
					array(
						'value' => 'package',
						'label' => _x( 'package', 'product tag', 'flexible-shipping-conditional-methods' ),
					),
				)
			)
			->set_label( _x( 'in the', 'product tag', 'flexible-shipping-conditional-methods' ) );
		if ( $default_value ) {
			$source_select->set_default_value( $default_value );
		}

		return $source_select;
	}

	/**
	 * @param string|null $default_value .
	 *
	 * @return SelectField
	 */
	private function prepare_operator_matches( $default_value = null ) {
		$operator_matches = ( new SelectField() )
			->set_name( 'matches' )
			->set_options(
				array(
					array(
						'value' => 'any',
						'label' => _x( 'any of', 'product tag', 'flexible-shipping-conditional-methods' ),
					),
					array(
						'value' => 'all',
						'label' => _x( 'all of', 'product tag', 'flexible-shipping-conditional-methods' ),
					),
					array(
						'value' => 'none',
						'label' => _x( 'none of', 'product tag', 'flexible-shipping-conditional-methods' ),
					),
				)
			)
			->set_label( _x( 'matches', 'product tag', 'flexible-shipping-conditional-methods' ) );
		if ( $default_value ) {
			$operator_matches->set_default_value( $default_value );
		}

		return $operator_matches;
	}

	/**
	 * @param array<string, string|array> $option_settings .
	 *
	 * @return array<string, string|array>
	 */
	public function prepare_settings( $option_settings ) {
		$product_tags = $this->get_product_tags( $option_settings );

		if ( ! $product_tags ) {
			return $option_settings;
		}

		$options = array_map( array( $this, 'prepare_item_option' ), $product_tags );

		$option_settings[ $this->option_id . '_options' ] = array_values( $options );

		return $option_settings;
	}

	/**
	 * @param WP_Term $item .
	 *
	 * @return array<string, int|string>
	 */
	public function prepare_item_option( $item ) {
		return $this->prepare_option( (string) $item->term_id, $item->name );
	}

	/**
	 * @param array<string, string|array> $option_settings .
	 *
	 * @return WP_Term[]
	 */
	private function get_product_tags( $option_settings ) {
		if ( ! isset( $option_settings[ $this->option_id ] ) ) {
			return array();
		}

		$tags = array();

		foreach ( wp_parse_id_list( $option_settings[ $this->option_id ] ) as $tag_id ) {
			$term = get_term( $tag_id, 'product_tag' );

			if ( $term instanceof WP_Term ) {
				$tags[] = $term;
			}
		}

		return $tags;
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

		$product_tags = isset( $condition_settings[ $this->get_option_id() ] ) ? $condition_settings[ $this->get_option_id() ] : array();
		$product_tags = wp_parse_id_list( $product_tags );

		$contents_product_tags = $this->get_contents_products( $contents );

		return $this->is_operator_matched( $matches, $product_tags, $contents_product_tags );
	}

	/**
	 * @param array[] $contents .
	 *
	 * @return int[]
	 */
	private function get_contents_products( array $contents ) {
		$contents_product_tags = array();
		foreach ( $contents as $item ) {
			$product = $this->get_product_from_item( $item['data'] );

			$contents_product_tags = array_merge( $contents_product_tags, $product->get_tag_ids() );
		}

		return array_unique( array_filter( $contents_product_tags ) );
	}
}
