<?php
/**
 * Interface CSVInterface
 *
 * @package WPDesk\FS\TableRate\ImportExport\Export\CSV
 */

namespace WPDesk\FS\TableRate\ImportExport\Export\CSV;

/**
 * Interface of exporters.
 */
interface CSVInterface {
	/**
	 * @param bool $with_header .
	 *
	 * @return array
	 */
	public function get_data( $with_header = true );

	/**
	 * @return array
	 */
	public function get_header_fields();
}
