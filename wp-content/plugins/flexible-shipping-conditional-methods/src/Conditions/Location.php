<?php
/**
 * Class Location
 *
 * @package WPDesk\FS\ConditionalMethods\Conditions
 */

namespace WPDesk\FS\ConditionalMethods\Conditions;

use FSConditionalMethodsVendor\WPDesk\Forms\Field;
use FSConditionalMethodsVendor\WPDesk\Forms\Field\SelectField;
use WC_Cart;
use WPDesk\FS\ConditionalMethods\Conditions\Helper\Option;
use WPDesk\FS\ConditionalMethods\Conditions\Location\Country;

/**
 * Day of the week condition.
 */
class Location extends AbstractCondition {
	use Option;

	/** @var array<string, string> */
	private $countries;

	/**
	 * Location constructor.
	 *
	 * @param Country $country .
	 */
	public function __construct( $country ) {
		parent::__construct(
			'location',
			__( 'Location', 'flexible-shipping-conditional-methods' ),
			__( 'The Actions defined further will be taken for the selected shipping methods if the Condition based on Location is met.', 'flexible-shipping-conditional-methods' ),
			__( 'Destination & Time', 'flexible-shipping-conditional-methods' ),
			10
		);

		$this->countries = $country->get_allowed_countries();
	}

	/**
	 * @return Field[]
	 */
	public function get_fields() {
		$fields = array(
			$this->prepare_operator_matches(),
			( new Field\WooSelect() )
				->set_name( $this->get_option_id() )
				->set_options( $this->get_select_options() )
				->set_placeholder( __( 'Select the country', 'flexible-shipping-conditional-methods' ) )
				->set_label( _x( 'one of', 'location', 'flexible-shipping-conditional-methods' ) ),
		);

		return $fields;
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
						'value' => 'is',
						'label' => _x( 'is', 'location', 'flexible-shipping-conditional-methods' ),
					),
					array(
						'value' => 'is_not',
						'label' => _x( 'is not', 'location', 'flexible-shipping-conditional-methods' ),
					),
				)
			);
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
		$countries = isset( $condition_settings[ $this->get_option_id() ] ) ? $condition_settings[ $this->get_option_id() ] : array();
		$countries = wp_parse_list( $countries );

		$matches = is_string( $condition_settings['matches'] ) ? $condition_settings['matches'] : '';

		$shipping_country = $cart->get_customer()->get_shipping_country();

		$condition_matched = in_array( $shipping_country, $countries, true );

		return $this->apply_is_not_operator( $condition_matched, $matches );
	}

	/**
	 * @return array<int, array>
	 */
	private function get_select_options() {
		$options = array();

		foreach ( $this->countries as $country_code => $name ) {
			$options[] = $this->prepare_option( $country_code, $name );
		}

		return $options;
	}
}
