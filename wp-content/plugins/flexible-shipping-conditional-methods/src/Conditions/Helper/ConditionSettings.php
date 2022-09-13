<?php
/**
 * Trait ConditionSettings
 *
 * @package WPDesk\FS\ConditionalMethods\Conditions\Helper
 */

namespace WPDesk\FS\ConditionalMethods\Conditions\Helper;

trait ConditionSettings {
	/**
	 * @param array<string, string|array> $condition_settings .
	 * @param string                      $field              .
	 * @param float                       $default            .
	 *
	 * @return float
	 */
	private function get_setting_option_min_max( $condition_settings, $field, $default ) {
		return (float) $this->get_setting_option( $condition_settings, $field, $default );
	}

	/**
	 * @param array<string, string|array> $condition_settings .
	 * @param string                      $field              .
	 * @param float|string                $default            .
	 *
	 * @return float|string
	 */
	private function get_setting_option( $condition_settings, $field, $default ) {
		if ( isset( $condition_settings[ $field ] ) && ! is_array( $condition_settings[ $field ] ) && 0 !== strlen( $condition_settings[ $field ] ) ) {
			return $condition_settings[ $field ];
		}

		return $default;
	}
}
