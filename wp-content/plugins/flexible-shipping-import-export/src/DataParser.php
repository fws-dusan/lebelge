<?php
/**
 * Interface DataParser
 *
 * @package WPDesk\FS\TableRate\ImportExport
 */

namespace WPDesk\FS\TableRate\ImportExport;

use WPDesk\FS\TableRate\ImportExport\Exception\InvalidImportFormatException;

/**
 * Data Parser.
 */
interface DataParser {

	/**
	 * @return ShippingMethodData[]
	 * @throws InvalidImportFormatException .
	 */
	public function parse();

}
