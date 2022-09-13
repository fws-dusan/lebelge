<?php
/**
 * Class CSVAbstract
 *
 * @package WPDesk\FS\TableRate\ImportExport\Export\CSV
 */

namespace WPDesk\FS\TableRate\ImportExport\Export\CSV;

/**
 * Common export fields.
 */
abstract class CSVAbstract implements CSVInterface {
	/**
	 * @param array $fields .
	 *
	 * @return array
	 */
	protected function get_row_default_fields( $fields ) {
		return array_fill_keys( $fields, '' );
	}

	/**
	 * @return array
	 */
	public function get_header_fields() {
		return array();
	}

	/**
	 * @param bool $with_header .
	 *
	 * @return array
	 */
	public function get_data( $with_header = true ) {
		return array();
	}
}
