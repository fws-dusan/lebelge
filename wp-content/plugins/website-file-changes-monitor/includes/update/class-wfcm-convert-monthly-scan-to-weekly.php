<?php

/**
 * WFCM Conversion of monthly scan to weekly scan.
 *
 * @package wfcm
 */

/**
 * Class to convert monthly scan to a weekly scan if applicable. This involves changing some settings
 * and changing the cron schedule.
 *
 * @since 1.7.0
 */
class WFCM_ConvertMonthlyScanToWeekly extends WFCM_AbstractUpdateWrapper implements WFCM_UpdateWrapperInterface {

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
		$this->key         = 'convert_monthly_scan_to_weekly';
		$this->max_version = '1.7.0';
		$this->min_version = '1.6.2';

		parent::__construct( $old_version, $new_version );
	}

	public function check( $compare = '<', $old_version = '', $new_version = '' ) {
		return parent::check( $compare, $old_version, $new_version ) && 'monthly' === get_option( 'wfcm_scan-frequency' );
	}

	/**
	 * Do the work.
	 *
	 * @since  1.7.0
	 */
	public function run() {

		update_option( 'wfcm_scan-frequency', 'weekly' );
		delete_option( 'wfcm_scan-date' );
		delete_transient( 'wfcm_options' );

		wp_clear_scheduled_hook( WFCM_Monitor::$schedule_hook );

		//  mark as finished
		$this->finished = true;
	}
}
