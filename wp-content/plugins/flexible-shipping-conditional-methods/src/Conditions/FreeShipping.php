<?php
/**
 * Class FreeShipping
 *
 * @package WPDesk\FS\ConditionalMethods\Conditions
 */

namespace WPDesk\FS\ConditionalMethods\Conditions;

use FSConditionalMethodsVendor\WPDesk\Forms\Field;
use FSConditionalMethodsVendor\WPDesk\Forms\Field\SelectField;
use WC_Cart;

/**
 * Free Shipping condition.
 */
class FreeShipping extends AbstractCondition {

	/**
	 * FreeShipping constructor.
	 */
	public function __construct() {
		parent::__construct(
			'free_shipping',
			__( 'Zero-cost shipping method', 'flexible-shipping-conditional-methods' ),
			__( 'The Actions defined further will be taken for the selected shipping methods if any zero-cost shipping method is available to choose in the cart or for the package.', 'flexible-shipping-conditional-methods' ),
			__( 'Shipping method', 'flexible-shipping-conditional-methods' ),
			10
		);
	}

	/**
	 * @return Field[]
	 */
	public function get_fields() {
		return array(
			$this->prepare_source_select(),
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
						'label' => _x( 'cart', 'free shipping', 'flexible-shipping-conditional-methods' ),
					),
					array(
						'value' => 'package',
						'label' => _x( 'package', 'free shipping', 'flexible-shipping-conditional-methods' ),
					),
				)
			)
			->set_label( _x( 'is in the', 'shipping method', 'flexible-shipping-conditional-methods' ) );
		if ( $default_value ) {
			$source_select->set_default_value( $default_value );
		}

		return $source_select;
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

		foreach ( $contents as $single_package ) {
			foreach ( $single_package['rates'] as $rate ) {
				if ( ( (float) $rate->get_cost() ) === 0.0 ) {
					return true;
				}
			}
		}

		return false;
	}
}
