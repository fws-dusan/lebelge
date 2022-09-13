<?php
/**
 * Interface ExportData
 *
 * @package WPDesk\FS\TableRate\ImportExport
 */

namespace WPDesk\FS\TableRate\ImportExport\Conditions;

/**
 * Export Data.
 */
interface ExportData {
	/**
	 * @return string
	 */
	public function get_condition_id();

	/**
	 * @param mixed  $value      .
	 * @param string $field_name .
	 *
	 * @return mixed
	 */
	public function prepare_data( $value, $field_name );
}
