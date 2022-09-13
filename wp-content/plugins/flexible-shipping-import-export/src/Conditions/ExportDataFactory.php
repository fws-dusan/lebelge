<?php
/**
 * Interface ExportDataFactory
 *
 * @package WPDesk\FS\TableRate\ImportExport
 */

namespace WPDesk\FS\TableRate\ImportExport\Conditions;

use WPDesk\FS\TableRate\ImportExport\Conditions\ExportData;

/**
 * Export Data Factory.
 */
class ExportDataFactory {
	/**
	 * @return ExportData[]
	 */
	public function create_prepares() {
		$prepares = apply_filters( 'flexible-shipping/export/preparing', array() );

		return array_filter(
			$prepares,
			function ( $prepare ) {
				return $prepare instanceof ExportData;
			}
		);
	}
}
