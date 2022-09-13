<?php
/**
 * Interface ImportDataFactory
 *
 * @package WPDesk\FS\TableRate\ImportExport
 */

namespace WPDesk\FS\TableRate\ImportExport\Conditions;

/**
 * Import Data Factory.
 */
class ImportDataFactory {
	/**
	 * @return ImportData[]
	 */
	public function create_prepares() {
		$prepares = apply_filters( 'flexible-shipping/import/preparing', array() );

		return array_filter(
			$prepares,
			function ( $prepare ) {
				return $prepare instanceof ImportData;
			}
		);
	}
}
