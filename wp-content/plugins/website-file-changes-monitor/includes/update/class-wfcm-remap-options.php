<?php
/**
 * WFCM Remap Options
 *
 * Class to remap options from the old 'wfcm-` prefix to the new prefix with
 * an underscore instead of a dash.
 *
 * @package wfcm
 */

/**
 * Remap a certain set of options defined by the plugin to change their prefix.
 *
 * @since 1.4.0
 */
class WFCM_RemapOptions extends WFCM_AbstractUpdateWrapper implements WFCM_UpdateWrapperInterface {
	
	/**
	 * Setup old and new version properties and register this routine if the
	 * conditions pass checks.
	 *
	 * @method __construct
	 * @param string $old_version the old version string.
	 * @param string $new_version the new version string.
	 *
	 * @since  1.4.0
	 */
	public function __construct( $old_version, $new_version ) {
		$this->key         = 'remap_options_prefix';
		$this->max_version = '1.7.0';
		$this->min_version = '2.0.0';

		parent::__construct( $old_version, $new_version );
	}

	/**
	 * Do the work.
	 *
	 * @method run
	 * @since  1.4.0
	 */
	public function run() {
		/*
		 * Setup the old prefixes and new prefixes, make a sql query for old
		 * options and create some holders.
		 */
		global $wpdb;
		$old_prefix                 = 'wfcm-';
		$new_prefix                 = WFCM_OPT_PREFIX;
		$old_options_prefixed_query = "select * from $wpdb->options where option_name like '{$old_prefix}%'";
		$old_options                = $wpdb->get_results( $old_options_prefixed_query );
		$items_to_map               = array();
		// Loop through all old settings and, if allowed to remap, update them
		// and delete the old options.
		foreach ( $old_options as $old_option ) {
			$items_to_map[]   = $old_option;
			$allowed_to_remap = $this->get_allowed_options_to_remap();
			if ( in_array( $old_option->option_name, $allowed_to_remap, true ) ) {
				$new_option_name = str_replace( 'wfcm-', 'wfcm_', $old_option->option_name );
				update_option( $new_option_name, maybe_unserialize( $old_option->option_value ) );
				delete_option( $old_option->option_name );
			}
		}
		// create a backup of the old settings as they were just in case we need a rollback.
		if ( ! empty( $items_to_map ) ) {
			update_option( $new_prefix . 'old_settings_before_remap', $items_to_map );
		}
		// mark as finished.
		$this->finished = true;
	}

	/**
	 * Get an array of allowed options to remap incase there are any prefix
	 * conflicts on users' sites.
	 *
	 * @method get_allowed_options_to_remap
	 * @return array
	 * @since  1.4.0
	 */
	private function get_allowed_options_to_remap() {
		return array(
			'wfcm-admin-notices',
			'wfcm-debug-logging',
			'wfcm-delete-data',
			'wfcm-deleted-per-page',
			'wfcm-dismiss-instant-scan-modal',
			'wfcm-is-initial-scan-0',
			'wfcm-is-initial-scan-1',
			'wfcm-is-initial-scan-2',
			'wfcm-is-initial-scan-3',
			'wfcm-is-initial-scan-4',
			'wfcm-is-initial-scan-5',
			'wfcm-is-initial-scan-6',
			'wfcm-keep-log',
			'wfcm-last-scan-timestamp',
			'wfcm-local-files-0',
			'wfcm-local-files-1',
			'wfcm-local-files-2',
			'wfcm-local-files-3',
			'wfcm-local-files-4',
			'wfcm-local-files-5',
			'wfcm-local-files-6',
			'wfcm-modified-per-page',
			'wfcm-scan-date',
			'wfcm-scan-day',
			'wfcm-scan-directories',
			'wfcm-scan-exclude-dirs',
			'wfcm-scan-exclude-exts',
			'wfcm-scan-exclude-files',
			'wfcm-scan-file-size',
			'wfcm-scan-frequency',
			'wfcm-scan-hour',
			'wfcm-scan-in-progress',
			'wfcm-scan-type',
			'wfcm-site-content',
			'wfcm-version',
		);
	}

}
