<?php

/**
 * Backgroung task which scans a given path and updates the DB with its findings before sending
 * the path of for further processing (check for changes)
 *
 * @since 1.8.0
 */
class WFCM_Background_Scanner extends WP_Background_Process {

	/**
	 * @var string
	 */
	protected $action = 'wfcm_scanner_scan_paths';

	protected $check_for_changes = false;

	protected $current_path = false;

	/**
	 * @param string Current path we are checkign.
	 *
	 * @return bool
	 */
	protected function task( $directory ) {

		if ( 'root' === $directory ) {
			$directory = '';
		}

		// Setup vars for later.
		$files              = array();
		$wfcm               = wfcm_get_monitor();
		$this->current_path = $directory;

		// Create tidy name for the setting.
		$setting_name = $wfcm->create_tidy_name( $directory );

		// Gather currently stored data, if applicable.
		$current_files = wfcm_get_setting( $setting_name . '_0' );
		$current_files = ( ! empty( $current_files ) ) ? $current_files : [];

		// Logging, if enabled.
		if ( $wfcm->scan_settings['debug-logging'] ) {
			$msg  = wfcm_get_log_timestamp() . ' ';
			$msg .= __( 'WFCM started scanning:', 'website-file-changes-monitor' ) . ' ';
			$msg .= $directory ? $directory : 'root';
			$msg .= "\n";
			wfcm_write_to_log( $msg );
		}

		// Gather files within current directory.
		$files = array_filter( $wfcm->scan_path( $directory ) );

		// Split files into chunks.
		$file_chunks = array_chunk( $files, 1000, true );

		// If we dont already have something, this is the 1st run.
		if ( ! empty( $current_files ) ) {
			$setting_name = $setting_name . '_comparison';
			$this->check_for_changes = true;
		}

		foreach ( $file_chunks as $count => $files ) {
			wfcm_save_setting( $setting_name . '_' . $count , $files );
		}

		// Now lets check for changes.
		$bg_process = new WFCM_Background_Check_For_Changes();
		$bg_process->push_to_queue( $directory );
		$bg_process->save()->dispatch();

		return false;
	}

	protected function complete() {
		do_action( 'wfcm_bg_scanning_complete', $this->current_path );

		// Unschedule the cron healthcheck.
		$this->clear_scheduled_event();
	}
}
