<?php
/**
 * Trait Option
 *
 * @package WPDesk\FS\ConditionalMethods\Conditions\Helper
 */

namespace WPDesk\FS\ConditionalMethods\Conditions\Helper;

trait Option {
	/**
	 * @param string $value .
	 * @param string $label .
	 *
	 * @return array<string, string>
	 */
	private function prepare_option( $value, $label ) {
		return array(
			'value' => $value,
			'label' => $label,
		);
	}
}
