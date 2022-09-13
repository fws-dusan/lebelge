<?php
/**
 * Trait RuleLabelsTrait
 *
 * @package WPDesk\FS\TableRate\ImportExport\Labels
 */

namespace WPDesk\FS\TableRate\ImportExport\Export\CSV\Labels;

/**
 * Rule Labels
 */
trait RuleLabelsTrait {
	/**
	 * @return string
	 */
	private function get_label_rule_number() {
		return 'Rule Number';
	}

	/**
	 * @param string $field .
	 *
	 * @return string
	 */
	private function get_label_rule_costs_field( $field ) {
		return sprintf( 'Rule Costs: %s', $field );
	}
}
