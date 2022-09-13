<?php
/**
 * Interface Condition
 *
 * @package WPDesk\FS\ConditionalMethods\Conditions
 */

namespace WPDesk\FS\ConditionalMethods\Conditions;

/**
 * Condition
 */
interface Condition {

	/**
	 * @return int
	 */
	public function get_condition_id();

	/**
	 * @param array<string, string|array> $condition_settings .
	 * @param \WC_Cart                    $cart .
	 * @param array[]                     $package .
	 * @param array[]                     $all_packages .
	 *
	 * @return bool
	 */
	public function is_condition_matched( array $condition_settings, \WC_Cart $cart, array $package, array $all_packages );

}
