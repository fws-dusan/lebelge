<?php
/**
 * Interface ImportData
 *
 * @package WPDesk\FS\TableRate\ImportExport
 */

namespace WPDesk\FS\TableRate\ImportExport\Conditions;

use WPDesk\FS\TableRate\ImportExport\Exception\InvalidImportDataException;

/**
 * Data Parser.
 */
interface ImportData {
	/**
	 * @return string
	 */
	public function get_condition_id();

	/**
	 * @param mixed  $value      .
	 * @param string $field_name .
	 *
	 * @return void
	 * @throws InvalidImportDataException .
	 */
	public function verify_data( $value, $field_name );

	/**
	 * @param mixed  $value      .
	 * @param string $field_name .
	 * @param array  $mapped     .
	 *
	 * @return mixed
	 */
	public function prepare_data( $value, $field_name, $mapped = array() );
}
