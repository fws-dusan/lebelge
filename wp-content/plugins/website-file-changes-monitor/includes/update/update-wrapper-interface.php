<?php
/**
 * WFCM Update Wrapper Interface
 *
 * This is an interface to ensure all update routines follow the same format
 * and that they fit into the same system across the whole plugin.
 *
 * @package wfcm
 */

/**
 * Interface to define an updater system for WFCM.
 *
 * @since 1.4.0
 */
interface WFCM_UpdateWrapperInterface {

	/**
	 * Pass in the old and new version strings at instantiation.
	 *
	 * @method __construct
	 * @since  1.4.0
	 * @param  string $old_version the old/last know version string.
	 * @param  string $new_version the current/next version string.
	 */
	public function __construct( $old_version, $new_version );

	/**
	 * Should return a bool answer based on conditions if this update is to run.
	 *
	 * @method check
	 * @since  1.4.0
	 * @param  string $compare the compare operator to use.
	 * @param  string $old_version the old version string to check against.
	 * @param  string $new_version the new version string to check against.
	 * @return bool
	 */
	public function check( $compare = '<', $old_version = '', $new_version = '' );

	/**
	 * Register the routine or class to the updator system here.
	 *
	 * @method register
	 * @since  1.4.0
	 */
	public function register();

	/**
	 * Run the update task here.
	 *
	 * @method run
	 * @since  1.4.0
	 */
	public function run();

	/**
	 * Finish the run and provide some indicator of finished status.
	 *
	 * @method finish
	 * @since  1.4.0
	 * @return bool
	 */
	public function finish();
}
