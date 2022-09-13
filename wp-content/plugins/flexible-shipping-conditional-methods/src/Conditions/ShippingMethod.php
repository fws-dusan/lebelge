<?php
/**
 * Class ShippingMethod
 *
 * @package WPDesk\FS\ConditionalMethods\Conditions
 */

namespace WPDesk\FS\ConditionalMethods\Conditions;

use FSConditionalMethodsVendor\WPDesk\Forms\Field;
use FSConditionalMethodsVendor\WPDesk\Forms\Field\SelectField;
use FSConditionalMethodsVendor\WPDesk\Forms\Field\WooSelect;
use WC_Cart;
use WC_Shipping_Rate;
use WPDesk\FS\ConditionalMethods\WooCommerceShippingMethods;

/**
 * Shipping method condition.
 */
class ShippingMethod extends AbstractCondition {

	/**
	 * ShippingMethod constructor.
	 */
	public function __construct() {
		parent::__construct(
			'shipping_method',
			__( 'Shipping method', 'flexible-shipping-conditional-methods' ),
			__( 'The Actions defined further will be taken for the selected shipping methods if the previously chosen Shipping methods are available to pick in the cart or for the package.', 'flexible-shipping-conditional-methods' ),
			__( 'Shipping method', 'flexible-shipping-conditional-methods' ),
			10
		);
	}

	/**
	 * @return Field[]
	 */
	public function get_fields() {
		$options = ( new WooCommerceShippingMethods() )->prepare_shipping_method_options();

		return array(
			$this->prepare_source_select(),
			$this->prepare_operator_matches(),
			( new WooSelect() )
				->set_name( $this->get_option_id() )
				->set_multiple()
				->set_options( $options )
				->add_class( 'shipping-method' )
				->set_placeholder( __( 'search shipping method', 'flexible-shipping-conditional-methods' ) )
				->set_label( ' ' ),
		);
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
						'label' => _x( 'cart', 'shipping method', 'flexible-shipping-conditional-methods' ),
					),
					array(
						'value' => 'package',
						'label' => _x( 'package', 'shipping method', 'flexible-shipping-conditional-methods' ),
					),
				)
			)
			->set_label( _x( 'in the', 'shipping method', 'flexible-shipping-conditional-methods' ) );
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
						'label' => _x( 'any', 'shipping method', 'flexible-shipping-conditional-methods' ),
					),
					array(
						'value' => 'all',
						'label' => _x( 'all', 'shipping method', 'flexible-shipping-conditional-methods' ),
					),
					array(
						'value' => 'none',
						'label' => _x( 'none', 'shipping method', 'flexible-shipping-conditional-methods' ),
					),
				)
			)
			->set_label( _x( 'matches', 'shipping method', 'flexible-shipping-conditional-methods' ) );
		if ( $default_value ) {
			$operator_matches->set_default_value( $default_value );
		}

		return $operator_matches;
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
		$contents = 'package' === $condition_settings['source'] ? array( $package ) : $all_packages;
		$matches  = is_string( $condition_settings['matches'] ) ? $condition_settings['matches'] : '';
		/** @var string[] $shipping_method */
		$shipping_method = isset( $condition_settings[ $this->get_option_id() ] ) ? $condition_settings[ $this->get_option_id() ] : array();

		$contents_shipping_methods = $this->get_contents_shipping_methods( $contents );

		return $this->is_operator_matched( $matches, $shipping_method, $contents_shipping_methods );
	}

	/**
	 * @param array[] $contents .
	 *
	 * @return string[]
	 */
	private function get_contents_shipping_methods( $contents ) {
		$shipping_methods = array();

		foreach ( $contents as $package ) {
			foreach ( $package['rates'] as $rate ) {
				$shipping_methods = $this->append_shipping_methods( $rate, $shipping_methods );
			}
		}

		return $shipping_methods;
	}

	/**
	 * @param WC_Shipping_Rate $rate             .
	 * @param string[]         $shipping_methods .
	 *
	 * @return string[]
	 */
	private function append_shipping_methods( $rate, array $shipping_methods ) {
		$rate_elements   = explode( ':', $rate->get_id() );
		$shipping_method = '';
		foreach ( $rate_elements as $rate_element ) {
			$shipping_method    .= ':' . $rate_element;
			$shipping_methods[] = trim( $shipping_method, ':' );
		}

		return $shipping_methods;
	}

}
