<?php
/**
 * Class ConditionsMatcher
 *
 * @package WPDesk\FS\ConditionalMethods
 */

namespace WPDesk\FS\ConditionalMethods;

use WPDesk\FS\ConditionalMethods\Actions\Exception\UnknownActionException;
use WPDesk\FS\ConditionalMethods\Conditions\Condition;
use WPDesk\FS\ConditionalMethods\Conditions\Exception\UnknownConditionException;

/**
 * Can process conditions.
 */
class ConditionsMatcher {

	/**
	 * @var Condition[]
	 */
	private $available_conditions;

	/**
	 * ConditionsMatcher constructor.
	 *
	 * @param Condition[] $available_conditions .
	 */
	public function __construct( array $available_conditions ) {
		$this->available_conditions = $available_conditions;
	}

	/**
	 * @param array<string, array> $conditions_settings .
	 * @param \WC_Cart             $cart .
	 * @param array[]              $package .
	 * @param array[]              $all_packages .
	 *
	 * @return bool
	 */
	public function are_conditions_matched( array $conditions_settings, \WC_Cart $cart, array $package, array $all_packages ) {
		$conditions_matched = true;
		foreach ( $conditions_settings as $condition_settings ) {
			$type = $condition_settings['type'];
			if ( 'option' === $type ) {
				$condition = $this->get_condition_from_available_conditions( $condition_settings['option_id'] );
				$single_condition_matched = $condition->is_condition_matched( $condition_settings, $cart, $package, $all_packages );
				$conditions_matched = $conditions_matched && $single_condition_matched;
			} else if ( 'or' === $type ) {
				if ( $conditions_matched ) {
					return true;
				}

				$conditions_matched = true;
			}
		}

		return $conditions_matched;
	}

	/**
	 * @param int $condition_id .
	 *
	 * @return Condition
	 * @throws UnknownConditionException .
	 */
	private function get_condition_from_available_conditions( $condition_id ) {
		if ( isset( $this->available_conditions[ $condition_id ] ) ) {

			return $this->available_conditions[ $condition_id ];
		}
		throw new UnknownConditionException( $condition_id );
	}

}
