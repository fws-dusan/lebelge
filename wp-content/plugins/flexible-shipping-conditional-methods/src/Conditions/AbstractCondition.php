<?php
/**
 * Class AbstractCondition
 *
 * @package WPDesk\FS\ConditionalMethods\Conditions
 */

namespace WPDesk\FS\ConditionalMethods\Conditions;

use WPDesk\FS\ConditionalMethods\ConditionalForm\AbstractOptionField;
use WPDesk\FS\ConditionalMethods\Conditions\Exception\InvalidConditionSettings;

/**
 * Abstract Condition
 */
abstract class AbstractCondition extends AbstractOptionField implements Condition {

	/**
	 * @return int|string
	 */
	public function get_condition_id() {
		return $this->get_option_id();
	}

	/**
	 * @param array<string, string|array> $condition_settings .
	 * @param \WC_Cart                    $cart               .
	 * @param array[]                     $package            .
	 * @param array[]                     $all_packages       .
	 *
	 * @return bool
	 */
	abstract public function is_condition_matched( array $condition_settings, \WC_Cart $cart, array $package, array $all_packages );

	/**
	 * @param string         $matches_operator .
	 * @param int[]|string[] $settings_values  .
	 * @param int[]|string[] $contents_values  .
	 *
	 * @return bool
	 * @throws InvalidConditionSettings .
	 */
	protected function is_operator_matched( $matches_operator, $settings_values, $contents_values ) {
		switch ( $matches_operator ) {
			case 'any':
				$matched = false;
				foreach ( $settings_values as $item_id ) {
					if ( in_array( $item_id, $contents_values, true ) ) {
						return true;
					}
				}
				break;
			case 'all':
				$matched = true;
				foreach ( $settings_values as $item_id ) {
					if ( ! in_array( $item_id, $contents_values, true ) ) {
						return false;
					}
				}
				break;
			case 'none':
				$matched = true;
				foreach ( $settings_values as $item_id ) {
					if ( in_array( $item_id, $contents_values, true ) ) {
						return false;
					}
				}
				break;
			default:
				throw new InvalidConditionSettings( 'Invalid condition settings - matches: ' . $matches_operator );
		}

		return $matched;
	}

	/**
	 * @param bool   $matches  .
	 * @param string $operator .
	 *
	 * @return bool
	 */
	protected function apply_is_not_operator( $matches, $operator = 'is' ) {
		return 'is_not' === $operator ? ! $matches : $matches;
	}
}
