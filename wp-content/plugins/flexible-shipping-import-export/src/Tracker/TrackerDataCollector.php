<?php
/**
 * Class TrackerDataCollector
 *
 * @package WPDesk\FS\TableRate\ImportExport\Tracker
 */

namespace WPDesk\FS\TableRate\ImportExport\Tracker;

/**
 * Can update data for tracker.
 */
class TrackerDataCollector {

	const USED_IMPORT  = 'fs-ie-used-import';
	const USED_EXPORT  = 'fs-ie-used-export';
	const COUNT_IMPORT = 'fs-ie-count-import';
	const COUNT_EXPORT = 'fs-ie-count-export';
	const MAX_IMPORT   = 'fs-ie-max-import';
	const MAX_EXPORT   = 'fs-ie-max-export';

	/**
	 * Update export max.
	 *
	 * @param int $methods_count .
	 */
	public function update_export_data( $methods_count ) {
		$this->update_export_max( $methods_count );
		$this->update_export_count();
		$this->update_export_used();
	}

	/**
	 * Update export max.
	 *
	 * @param int $methods_count .
	 */
	public function update_import_data( $methods_count ) {
		$this->update_import_max( $methods_count );
		$this->update_import_count();
		$this->update_import_used();
	}

	/**
	 * Update export max.
	 *
	 * @param int $methods_count .
	 */
	private function update_export_max( $methods_count ) {
		update_option( self::MAX_EXPORT, max( (int) get_option( self::MAX_EXPORT, 0 ), $methods_count ) );
	}

	/**
	 * Update export max.
	 *
	 * @param int $methods_count .
	 */
	private function update_import_max( $methods_count ) {
		update_option( self::MAX_IMPORT, max( (int) get_option( self::MAX_IMPORT, 0 ), $methods_count ) );
	}

	/**
	 * Update import used option.
	 */
	private function update_import_used() {
		update_option( self::USED_IMPORT, 'yes' );
	}

	/**
	 * Update export used option.
	 */
	private function update_export_used() {
		update_option( self::USED_EXPORT, 'yes' );
	}

	/**
	 * Update import count.
	 */
	private function update_import_count() {
		update_option( self::COUNT_IMPORT, (int) get_option( self::COUNT_IMPORT, 0 ) + 1 );
	}

	/**
	 * Update export count.
	 */
	private function update_export_count() {
		update_option( self::COUNT_EXPORT, (int) get_option( self::COUNT_EXPORT, 0 ) + 1 );
	}

}
