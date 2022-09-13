<?php
/**
 * Class Product
 *
 * @package WPDesk\FS\ConditionalMethods\Conditions
 */

namespace WPDesk\FS\ConditionalMethods\Conditions;

use FSConditionalMethodsVendor\WPDesk\Forms\Field;
use FSConditionalMethodsVendor\WPDesk\Forms\Field\SelectField;
use FSConditionalMethodsVendor\WPDesk\Forms\Field\WooSelect;
use WC_Cart;
use WC_Product;
use WC_Product_Variation;
use WPDesk\FS\ConditionalMethods\Conditions\Product\AjaxHandler;

/**
 * Product condition.
 */
class Product extends AbstractCondition {

	/**
	 * Product constructor.
	 */
	public function __construct() {
		parent::__construct(
			'product',
			__( 'Product', 'flexible-shipping-conditional-methods' ),
			__( 'The Actions defined further will be taken for the selected shipping methods if the Condition based on Product is met for the cart or package.', 'flexible-shipping-conditional-methods' ),
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
				->add_class( 'product' )
				->set_placeholder( __( 'search product', 'flexible-shipping-conditional-methods' ) )
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
						'label' => _x( 'cart', 'product', 'flexible-shipping-conditional-methods' ),
					),
					array(
						'value' => 'package',
						'label' => _x( 'package', 'product', 'flexible-shipping-conditional-methods' ),
					),
				)
			)
			->set_label( _x( 'in the', 'product', 'flexible-shipping-conditional-methods' ) );
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
						'label' => _x( 'any of', 'product', 'flexible-shipping-conditional-methods' ),
					),
					array(
						'value' => 'all',
						'label' => _x( 'all of', 'product', 'flexible-shipping-conditional-methods' ),
					),
					array(
						'value' => 'none',
						'label' => _x( 'none of', 'product', 'flexible-shipping-conditional-methods' ),
					),
				)
			)
			->set_label( _x( 'matches', 'product', 'flexible-shipping-conditional-methods' ) );
	}

	/**
	 * @param array<string, string|array> $option_settings .
	 *
	 * @return array<string, string|array>
	 */
	public function prepare_settings( $option_settings ) {
		$products = $this->get_products( $option_settings );

		if ( ! $products ) {
			return $option_settings;
		}

		$options = array_map( array( $this, 'prepare_item_option' ), $products );

		$option_settings[ $this->option_id . '_options' ] = array_values( $options );

		return $option_settings;
	}

	/**
	 * @param WC_Product $item .
	 *
	 * @return array<string, int|string>
	 */
	public function prepare_item_option( $item ) {
		return $this->prepare_option( $item->get_id(), $item->get_name() );
	}

	/**
	 * @param int    $value .
	 * @param string $label .
	 *
	 * @return array<string, int|string>
	 */
	private function prepare_option( $value, $label ) {
		return array(
			'value' => $value,
			'label' => sprintf( '%s (#%d)', $label, $value ),
		);
	}

	/**
	 * @param array<string, string|array> $option_settings .
	 *
	 * @return WC_Product[]
	 */
	private function get_products( $option_settings ) {
		if ( ! isset( $option_settings[ $this->option_id ] ) ) {
			return array();
		}

		$items = wp_parse_id_list( $option_settings[ $this->option_id ] );

		return array_filter( array_map( 'wc_get_product', $items ) );
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

		$product = isset( $condition_settings[ $this->get_option_id() ] ) ? $condition_settings[ $this->get_option_id() ] : array();
		$product = wp_parse_id_list( $product );

		$contents_products = $this->get_contents_products( $contents );

		return $this->is_operator_matched( $matches, $product, $contents_products );
	}

	/**
	 * @param array[] $contents .
	 *
	 * @return int[]
	 */
	private function get_contents_products( array $contents ) {
		$contents_products = array();
		foreach ( $contents as $item ) {
			$product             = $item['data'];
			$contents_products[] = $product->get_id();
			if ( $product instanceof WC_Product_Variation ) {
				$contents_products[] = $product->get_parent_id();
			}
		}

		return $contents_products;
	}

}
