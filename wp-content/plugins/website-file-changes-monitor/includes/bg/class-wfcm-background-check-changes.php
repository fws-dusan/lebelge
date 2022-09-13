<?php

/**
 * Backgroung task which triggers the processing of file changes (comparing stored to newly scanned files).
 *
 * @since 1.8.0
 */
class WFCM_Background_Check_For_Changes extends WP_Background_Process {

	/**
	 * @var string
	 */
	protected $action = 'wfcm_scanner_check_changes';

	protected $directory = false;

	/**
	 * @param string Current path we are checkign.
	 *
	 * @return bool
	 */
	protected function task( $directory ) {

		$wfcm = wfcm_get_monitor();

		$wfcm->check_for_changes( $directory );

		$this->directory = $directory;

		// Update list of scanned dirs.
		$directory              = ( empty( $directory ) ) ? 'root' : $directory;
		$currently_scanned_dirs = ( ! empty( wfcm_get_setting( 'scanned_directories' ) ) ) ? wfcm_get_setting( 'scanned_directories' ) : [];
		$currently_scanned      = array_merge( $currently_scanned_dirs, [ $directory ] );
		wfcm_save_setting( 'scanned_directories', $currently_scanned );

		return false;
	}

	protected function complete() {
		do_action( 'wfcm_scanner_check_changes_complete', $this->directory );
		// Unschedule the cron healthcheck.
		$this->clear_scheduled_event();
	}
}
