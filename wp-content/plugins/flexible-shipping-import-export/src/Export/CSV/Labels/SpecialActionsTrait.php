<?php
/**
 * Trait SpecialActionsTrait
 *
 * @package WPDesk\FS\TableRate\ImportExport\Labels
 */

namespace WPDesk\FS\TableRate\ImportExport\Export\CSV\Labels;

/**
 * Special Action Labels
 */
trait SpecialActionsTrait {
	/**
	 * @param string $field .
	 *
	 * @return string
	 */
	private function get_label_special_action_field( $field ) {
		return sprintf( 'Rule Special Action: %s', $field );
	}
}
