<?php
/**
 * WFCM Update Runner
 *
 * A runner for firing different update routines depending on version that the
 * plugin is coming from and going to.
 *
 * @package wfcm
 */

/**
 * Class for running various update routines based on the abstract class and
 * the interface that is defined.
 *
 * @since 1.4.0
 */
class WFCM_Update_Runner extends WFCM_AbstractUpdateWrapper implements WFCM_UpdateWrapperInterface {

	/**
	 * The key/ID of this update method.
	 *
	 * @var string
	 */
	const ID = 'update_runner';

	/**
	 * An array of classes containing individual update routines to run through.
	 *
	 * @var array
	 */
	private $update_routines;

	/**
	 * Sets up the new and old version properties, the update_routines array
	 * and then checks the conditions and if they pass sets up registration for
	 * the update routines.
	 *
	 * @method __construct
	 * @since  1.4.0
	 * @param  [type] $old_version A string with the old/last know version.
	 * @param  [type] $new_version A version string with the current/new version.
	 */
	public function __construct( $old_version, $new_version ) {
		$this->old_version     = $old_version;
		$this->new_version     = $new_version;
		$this->update_routines = array();
		// Check if the version shift matches with what we want before register.
		if ( $this->check() ) {
			$this->load_classes();
			$this->register();
		}
	}

	/**
	 * Registers this class to run on the given action/filter.
	 *
	 * @method register
	 * @since  1.4.0
	 */
	public function register() {
		$this->update_routines = apply_filters( 'wfcm_register_update_routine', $this->update_routines );
	}

	/**
	 * This is the main body of the update class and is the method that does
	 * the actual work to handle this update routine.
	 *
	 * @method run
	 * @since  1.4.0
	 */
	public function run() {
		foreach ( $this->update_routines as $update_routine ) {
			// bail early if the version check fails.
			if ( ! $update_routine->check() ) {
				continue;
			}
			// run each routines update and then call finish on it.
			$update_routine->run();
			$update_routine->finish();
		}
		$this->finished = true;
	}

	/**
	 * Loads all of the individual update routine classes here.
	 *
	 * @method load_classes
	 * @since  1.4.0
	 */
	private function load_classes() {
		// require the classes.
		require_once WFCM_BASE_DIR . 'includes/update/class-wfcm-remap-options.php';
		require_once WFCM_BASE_DIR . 'includes/update/class-wfcm-add-json-to-excluded-filetypes.php';
		require_once WFCM_BASE_DIR . 'includes/update/class-wfcm-reset-to-initial-scan.php';
		require_once WFCM_BASE_DIR . 'includes/update/class-wfcm-convert-monthly-scan-to-weekly.php';
		require_once WFCM_BASE_DIR . 'includes/update/class-wfcm-delete-file-event-posts.php';
		require_once WFCM_BASE_DIR . 'includes/update/class-wfcm-create-file-events-table.php';
	
		// Instantiate the classes.
		new WFCM_RemapOptions( $this->old_version, $this->new_version );
		new WFCM_AddJSONToExcludedFileTypes( $this->old_version, $this->new_version );
		new WFCM_ResetToInitialScan( $this->old_version, $this->new_version );
		new WFCM_ConvertMonthlyScanToWeekly( $this->old_version, $this->new_version );
		new WFCM_DeleteFileEventPosts( $this->old_version, $this->new_version );
		new WFCM_CreateFileEventsTable( $this->old_version, $this->new_version );
	}

}
