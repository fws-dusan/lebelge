<?php
/**
 * Class Price
 *
 * @package WPDesk\FS\ConditionalMethods\Conditions
 */

namespace WPDesk\FS\ConditionalMethods\Conditions;

use FSConditionalMethodsVendor\WPDesk\Forms\Field;
use FSConditionalMethodsVendor\WPDesk\Forms\Field\SelectField;
use WC_Cart;
use WPDesk\FS\ConditionalMethods\Conditions\Helper\ConditionSettings;

/**
 * Price condition.
 */
class Price extends AbstractCondition {
	use ConditionSettings;

	const MIN = 'min';
	const MAX = 'max';

	/**
	 * Price constructor.
	 */
	public function __construct() {
		parent::__construct(
			'price',
			__( 'Price', 'flexible-shipping-conditional-methods' ),
			__( 'The Actions defined further will be taken for the selected shipping methods if the Condition based on Price is met for the cart or package.', 'flexible-shipping-conditional-methods' ),
			__( 'General', 'flexible-shipping-conditional-methods' ),
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
			( new Field\InputNumberField() )
				->set_name( self::MIN )
				->add_class( 'wc_input_decimal' )
				->add_class( 'parameter_min' )
				->set_placeholder( __( 'is from', 'flexible-shipping-conditional-methods' ) )
				->set_label( __( 'from', 'flexible-shipping-conditional-methods' ) ),
			( new Field\InputNumberField() )
				->set_name( self::MAX )
				->add_class( 'wc_input_decimal' )
				->add_class( 'parameter_max' )
				->add_data( 'suffix', get_woocommerce_currency_symbol() )
				->set_placeholder( __( 'to', 'flexible-shipping-conditional-methods' ) )
				->set_label( __( 'to', 'flexible-shipping-conditional-methods' ) ),
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
						'label' => _x( 'cart', 'price', 'flexible-shipping-conditional-methods' ),
					),
					array(
						'value' => 'package',
						'label' => _x( 'package', 'price', 'flexible-shipping-conditional-methods' ),
					),
				)
			)
			->set_label( _x( 'of the', 'price', 'flexible-shipping-conditional-methods' ) );
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
						'value' => 'is',
						'label' => _x( 'is', 'price', 'flexible-shipping-conditional-methods' ),
					),
					array(
						'value' => 'is_not',
						'label' => _x( 'is not', 'price', 'flexible-shipping-conditional-methods' ),
					),
				)
			)
			->set_label( ' ' );
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
		$min     = $this->get_setting_option_min_max( $condition_settings, 'min', 0.0 );
		$max     = $this->get_setting_option_min_max( $condition_settings, 'max', INF );
		$matches = is_string( $condition_settings['matches'] ) ? $condition_settings['matches'] : '';

		if ( 'package' === $condition_settings['source'] ) {
			$contents_cost = (float) $package['contents_cost']; // @phpstan-ignore-line
		} else {
			$contents_cost = (float) $cart->get_cart_contents_total();
		}

		$condition_matched = $contents_cost >= $min && $contents_cost <= $max;

		return $this->apply_is_not_operator( $condition_matched, $matches );
	}
}
