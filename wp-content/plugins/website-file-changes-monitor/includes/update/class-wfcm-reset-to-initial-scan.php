<?php

/**
 * WFCM Reset to initial scan
 *
 * @package wfcm
 */

/**
 * Class to reset all necessary options to prompt the user to trigger the initial scan again.
 *
 * @since 1.7.0
 */
class WFCM_ResetToInitialScan extends WFCM_AbstractUpdateWrapper implements WFCM_UpdateWrapperInterface {

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
		$this->key         = 'reset_to_inital_scan_1_7';
		$this->max_version = '1.7.0';
		$this->min_version = '1.6.2';

		parent::__construct( $old_version, $new_version );
	}

	public function check( $compare = '<', $old_version = '', $new_version = '' ) {
		return parent::check( $compare, $old_version, $new_version ) && 'yes' !== get_option( 'wfcm_folder_list_change_1_7_reset_done' );
	}

	/**
	 * Do the work.
	 *
	 * @method run
	 * @since  1.7.0
	 */
	public function run() {

		//  remove all options related to previous scans
		$options_to_delete = [ 'wfcm_scan-directories', 'wfcm_dismiss-instant-scan-modal', 'wfcm_site-content' ];
		for ( $index = 0; $index <= 6; $index ++ ) {
			array_push( $options_to_delete, 'wfcm_is-initial-scan-' . $index );
			array_push( $options_to_delete, 'wfcm_local-files-' . $index );
		}
		foreach ( $options_to_delete as $option_name ) {
			delete_option( $option_name );
		}

		delete_transient( 'wfcm_options' );

		//  mark all existing file changes as read
		WFCM_Event_Data_Store::delete_events( WFCM_REST_API::get_event_types() );

		add_option( 'wfcm_folder_list_change_1_7_reset_done', 'yes', '', false );

		//  enable the wp.org checksum validation by default
		wfcm_save_setting( 'scan-wp-repo-core-checksum-validation-enabled', 'yes' );

		//  add  wp-config-sample.php to the list of ignored files
		$excluded_files = wfcm_get_setting( 'scan-exclude-files' );

		$file_to_exclude_by_default = 'wp-config-sample.php';
		if ( ! in_array( $file_to_exclude_by_default, $excluded_files ) ) {
			$excluded_files[] = $file_to_exclude_by_default;
		}

		// Update settings.
		wfcm_save_setting( 'scan-exclude-files', $excluded_files );

		//  add wp-config-sample.php and .htaccess to the list of files allowed in the site root and WP core folders by default
		$allowed_core_files = wfcm_get_setting( 'scan-allowed-in-core-files' );

		$files_to_allow_by_default = array(
			'wp-config.php',
			'.htaccess'
		);

		// Update settings.
		wfcm_save_setting( 'scan-allowed-in-core-files', array_unique( array_merge( $allowed_core_files, $files_to_allow_by_default ) ) );

		//  mark as finished
		$this->finished = true;
	}
}
