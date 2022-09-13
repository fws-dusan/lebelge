<?php
/**
 * Interface ExportInterface
 *
 * @package WPDesk\FS\TableRate\ImportExport\Export
 */

namespace WPDesk\FS\TableRate\ImportExport\Export;

/**
 * Interface of exporters.
 */
interface ExportInterface {
	/**
	 * @return mixed
	 */
	public function download();

	/**
	 * @return string
	 */
	public function get_raw();

	/**
	 * @return int
	 */
	public function get_elements_count();

	/**
	 * @return string
	 */
	public function get_mime_type();

	/**
	 * @return string
	 */
	public function get_filename();
}
