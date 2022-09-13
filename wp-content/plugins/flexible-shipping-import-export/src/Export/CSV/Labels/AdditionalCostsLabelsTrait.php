<?php
/**
 * Trait AdditionalCostsLabelsTrait
 *
 * @package WPDesk\FS\TableRate\ImportExport\Labels
 */

namespace WPDesk\FS\TableRate\ImportExport\Export\CSV\Labels;

/**
 * Additional Costs Labels
 */
trait AdditionalCostsLabelsTrait {
	/**
	 * @return string
	 */
	private function get_label_additional_cost_number() {
		return 'Additional Cost Number';
	}

	/**
	 * @param string $field .
	 *
	 * @return string
	 */
	private function get_label_additional_cost_field( $field ) {
		return sprintf( 'Additional Cost Field: %s', $field );
	}
}
