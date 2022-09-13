<?php
/**
 * Interface AbstractImportData
 *
 * @package WPDesk\FS\TableRate\ImportExport
 */

namespace WPDesk\FS\TableRate\ImportExport\Conditions;

use WPDesk\FS\TableRate\ImportExport\Exception\InvalidImportDataException;

/**
 * Abstract Import Data.
 */
abstract class AbstractImportData implements ImportData {
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
	 * @return void
	 * @throws InvalidImportDataException .
	 */
	public function verify_data( $value, $field_name ) {
		// Currently do nothing. Throw InvalidImportDataException when data is invalid.
	}

	/**
	 * @param mixed  $value      .
	 * @param string $field_name .
	 * @param array  $mapped     .
	 *
	 * @return mixed
	 */
	public function prepare_data( $value, $field_name, $mapped = array() ) {
		return $value;
	}
}
