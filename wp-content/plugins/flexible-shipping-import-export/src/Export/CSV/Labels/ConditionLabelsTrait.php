<?php
/**
 * Trait ConditionLabelsTrait
 *
 * @package WPDesk\FS\TableRate\ImportExport\Labels
 */

namespace WPDesk\FS\TableRate\ImportExport\Export\CSV\Labels;

/**
 * Condition Labels
 */
trait ConditionLabelsTrait {
	/**
	 * @return string
	 */
	private function get_label_condition_id() {
		return 'Condition ID';
	}

	/**
	 * @return string
	 */
	private function get_label_condition_number() {
		return 'Condition Number';
	}

	/**
	 * @param string $field .
	 *
	 * @return string
	 */
	private function get_label_condition_field( $field ) {
		return sprintf( 'Condition Field: %s', $field );
	}
}
