<?php
/**
 * Interface AbstractExportData
 *
 * @package WPDesk\FS\TableRate\ImportExport
 */

namespace WPDesk\FS\TableRate\ImportExport\Conditions;

use WPDesk\FS\TableRate\ImportExport\Conditions\ExportData;

/**
 * Abstract Import Data.
 */
abstract class AbstractExportData implements ExportData {
	/**
	 * @var string
	 */
	protected $condition_id;

	/**
	 * @return string
	 */
	public function get_condition_id() {
		return $this->condition_id;
	}

	/**
	 * @param mixed  $value      .
	 * @param string $field_name .
	 *
	 * @return mixed
	 */
	public function prepare_data( $value, $field_name ) {
		return $value;
	}
}
