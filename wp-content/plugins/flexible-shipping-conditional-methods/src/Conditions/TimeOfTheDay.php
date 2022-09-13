<?php
/**
 * Class TimeOfTheDay
 *
 * @package WPDesk\FS\ConditionalMethods\Conditions
 */

namespace WPDesk\FS\ConditionalMethods\Conditions;

use DateInterval;
use DateTime;
use Exception;
use FSConditionalMethodsVendor\WPDesk\Forms\Field;
use FSConditionalMethodsVendor\WPDesk\Forms\Field\SelectField;
use WC_Cart;
use WPDesk\FS\ConditionalMethods\Conditions\Helper\ConditionSettings;

/**
 * Time of the day condition.
 */
class TimeOfTheDay extends AbstractCondition {
	use ConditionSettings;

	const FIELD_TIME_FROM = 'from';

	const FIELD_TIME_TO = 'to';

	/**
	 * TimeOfTheDay constructor.
	 */
	public function __construct() {
		parent::__construct(
			'time_of_the_day',
			__( 'Time of the day', 'flexible-shipping-conditional-methods' ),
			__( 'The Actions defined further will be taken for the selected shipping methods if the Condition based on Time of the day is met.', 'flexible-shipping-conditional-methods' ),
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
			( new Field\SelectField() )
				->set_name( self::FIELD_TIME_FROM )
				->add_class( 'parameter_min' )
				->add_class( 'hour_from' )
				->set_options( $this->get_hours() )
				->set_default_value( array( '00:00' ) )
				->set_label( __( 'between', 'flexible-shipping-conditional-methods' ) ),
			( new Field\SelectField() )
				->set_name( self::FIELD_TIME_TO )
				->add_class( 'parameter_max' )
				->add_class( 'hour_to' )
				->set_options( $this->get_hours() )
				->set_default_value( array( '23:00' ) )
				->set_label( _x( 'and', 'time of the day', 'flexible-shipping-conditional-methods' ) ),
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
						'label' => _x( 'is', 'time of the day', 'flexible-shipping-conditional-methods' ),
					),
					array(
						'value' => 'is_not',
						'label' => _x( 'is not', 'time of the day', 'flexible-shipping-conditional-methods' ),
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
		$hour_from = (string) $this->get_setting_option( $condition_settings, self::FIELD_TIME_FROM, '00:00' );
		$hour_to   = (string) $this->get_setting_option( $condition_settings, self::FIELD_TIME_TO, '23:00' );

		$matches = is_string( $condition_settings['matches'] ) ? $condition_settings['matches'] : '';

		$current_timestamp = (int) current_time( 'timestamp' );

		$now = new DateTime();
		$now->setTimestamp( $current_timestamp );

		$from = new DateTime();
		$from->setTimestamp( $now->getTimestamp() );
		$this->set_time( $from, $hour_from );

		$to = new DateTime();
		$to->setTimestamp( $now->getTimestamp() );
		$this->set_time( $to, $hour_to );

		$condition_matched = $this->check_hours( $from, $now, $to );

		return $this->apply_is_not_operator( $condition_matched, $matches );
	}

	/**
	 * @param DateTime $time .
	 * @param string   $hour .
	 *
	 * @return DateTime
	 */
	private function set_time( $time, $hour ) {
		list( $h, $m ) = explode( ':', $hour );

		$time->setTime( (int) $h, (int) $m );

		return $time;
	}

	/**
	 * @param DateTime $from .
	 * @param DateTime $now  .
	 * @param DateTime $to   .
	 *
	 * @return bool
	 */
	private function check_hours( $from, $now, $to ) {
		if ( $from > $to || $from->format( 'H:i' ) === $to->format( 'H:i' ) ) {
			$to->add( new DateInterval( 'P1D' ) );
		}

		return $now >= $from && $now <= $to;
	}

	/**
	 * @return array[]
	 */
	private function get_hours() {
		return array(
			array(
				'value' => '00:00',
				'label' => __( '12:00 AM', 'flexible-shipping-conditional-methods' ),
			),
			array(
				'value' => '01:00',
				'label' => __( '1:00 AM', 'flexible-shipping-conditional-methods' ),
			),
			array(
				'value' => '02:00',
				'label' => __( '2:00 AM', 'flexible-shipping-conditional-methods' ),
			),
			array(
				'value' => '03:00',
				'label' => __( '3:00 AM', 'flexible-shipping-conditional-methods' ),
			),
			array(
				'value' => '04:00',
				'label' => __( '4:00 AM', 'flexible-shipping-conditional-methods' ),
			),
			array(
				'value' => '05:00',
				'label' => __( '5:00 AM', 'flexible-shipping-conditional-methods' ),
			),
			array(
				'value' => '06:00',
				'label' => __( '6:00 AM', 'flexible-shipping-conditional-methods' ),
			),
			array(
				'value' => '07:00',
				'label' => __( '7:00 AM', 'flexible-shipping-conditional-methods' ),
			),
			array(
				'value' => '08:00',
				'label' => __( '8:00 AM', 'flexible-shipping-conditional-methods' ),
			),
			array(
				'value' => '09:00',
				'label' => __( '9:00 AM', 'flexible-shipping-conditional-methods' ),
			),
			array(
				'value' => '10:00',
				'label' => __( '10:00 AM', 'flexible-shipping-conditional-methods' ),
			),
			array(
				'value' => '11:00',
				'label' => __( '11:00 AM', 'flexible-shipping-conditional-methods' ),
			),
			array(
				'value' => '12:00',
				'label' => __( '12:00 PM', 'flexible-shipping-conditional-methods' ),
			),
			array(
				'value' => '13:00',
				'label' => __( '1:00 PM', 'flexible-shipping-conditional-methods' ),
			),
			array(
				'value' => '14:00',
				'label' => __( '2:00 PM', 'flexible-shipping-conditional-methods' ),
			),
			array(
				'value' => '15:00',
				'label' => __( '3:00 PM', 'flexible-shipping-conditional-methods' ),
			),
			array(
				'value' => '16:00',
				'label' => __( '4:00 PM', 'flexible-shipping-conditional-methods' ),
			),
			array(
				'value' => '17:00',
				'label' => __( '5:00 PM', 'flexible-shipping-conditional-methods' ),
			),
			array(
				'value' => '18:00',
				'label' => __( '6:00 PM', 'flexible-shipping-conditional-methods' ),
			),
			array(
				'value' => '19:00',
				'label' => __( '7:00 PM', 'flexible-shipping-conditional-methods' ),
			),
			array(
				'value' => '20:00',
				'label' => __( '8:00 PM', 'flexible-shipping-conditional-methods' ),
			),
			array(
				'value' => '21:00',
				'label' => __( '9:00 PM', 'flexible-shipping-conditional-methods' ),
			),
			array(
				'value' => '22:00',
				'label' => __( '10:00 PM', 'flexible-shipping-conditional-methods' ),
			),
			array(
				'value' => '23:00',
				'label' => __( '11:00 PM', 'flexible-shipping-conditional-methods' ),
			),
		);
	}
}
