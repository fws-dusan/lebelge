<?php
/**
 * Class DayOfTheWeek
 *
 * @package WPDesk\FS\ConditionalMethods\Conditions
 */

namespace WPDesk\FS\ConditionalMethods\Conditions;

use FSConditionalMethodsVendor\WPDesk\Forms\Field;
use FSConditionalMethodsVendor\WPDesk\Forms\Field\SelectField;
use WC_Cart;
use WPDesk\FS\ConditionalMethods\Conditions\Helper\Option;

/**
 * Day of the week condition.
 */
class DayOfTheWeek extends AbstractCondition {
	use Option;

	/**
	 * Price constructor.
	 */
	public function __construct() {
		parent::__construct(
			'day_of_the_week',
			__( 'Day of the week', 'flexible-shipping-conditional-methods' ),
			__( 'The Actions defined further will be taken for the selected shipping methods if the Condition based on Day of the week is met.', 'flexible-shipping-conditional-methods' ),
			__( 'Destination & Time', 'flexible-shipping-conditional-methods' ),
			10
		);
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
				->set_default_value( array( 1 ) )
				->set_placeholder( __( 'Select the days', 'flexible-shipping-conditional-methods' ) )
				->set_label( __( 'one of', 'flexible-shipping-conditional-methods' ) ),
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
						'label' => _x( 'is', 'day of the week', 'flexible-shipping-conditional-methods' ),
					),
					array(
						'value' => 'is_not',
						'label' => _x( 'is not', 'day of the week', 'flexible-shipping-conditional-methods' ),
					),
				)
			)
			->set_label( ' ' );
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
		$days = isset( $condition_settings[ $this->get_option_id() ] ) ? $condition_settings[ $this->get_option_id() ] : array();
		$days = wp_parse_id_list( $days );

		$matches = is_string( $condition_settings['matches'] ) ? $condition_settings['matches'] : '';

		$day_of_week = (int) date_i18n( 'N' );

		$condition_matched = in_array( $day_of_week, $days, true );

		return $this->apply_is_not_operator( $condition_matched, $matches );
	}

	/**
	 * @return array<int, array>
	 */
	private function get_select_options() {
		$options = array();

		foreach ( $this->get_days() as $day => $name ) {
			$options[] = $this->prepare_option( (string) $day, $name );
		}

		return $options;
	}

	/**
	 * @return array<int, string>
	 */
	private function get_days() {
		return array(
			1 => __( 'Monday', 'flexible-shipping-conditional-methods' ),
			2 => __( 'Tuesday', 'flexible-shipping-conditional-methods' ),
			3 => __( 'Wednesday', 'flexible-shipping-conditional-methods' ),
			4 => __( 'Thursday', 'flexible-shipping-conditional-methods' ),
			5 => __( 'Friday', 'flexible-shipping-conditional-methods' ),
			6 => __( 'Saturday', 'flexible-shipping-conditional-methods' ),
			7 => __( 'Sunday', 'flexible-shipping-conditional-methods' ),
		);
	}
}
