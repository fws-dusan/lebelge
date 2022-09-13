<?php

class WFCM_CreateFileEventsTable extends WFCM_AbstractUpdateWrapper {

	/**
	 * Setup old and new version properties and register this routine if the
	 * conditions pass checks.
	 *
	 * @method __construct
	 * @param string $old_version the old version string.
	 * @param string $new_version the new version string.
	 *
	 * @since  1.7.0
	 */
	public function __construct( $old_version, $new_version ) {
		$this->key         = 'create_file_events_table';
		$this->max_version = '1.8.1';
		$this->min_version = '1.0.0';

		parent::__construct( $old_version, $new_version );
	}

	/**
	 * Do the work.
	 *
	 * @method run
	 * @since  1.7.0
	 */
	public function run() {
		WFCM_Database_DB_Data_Store::create_database_table();
	}
}
