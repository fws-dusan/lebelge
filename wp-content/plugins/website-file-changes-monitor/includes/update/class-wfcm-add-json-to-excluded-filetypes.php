<?php
/**
 * WFCM Add JSON To Excluded FileTypes
 *
 * Class to add `.json` files to the list of excluded file types.
 *
 * @package wfcm
 */

/**
 * Add `.json` to excluded file types.
 *
 * @since 1.5.0
 */
class WFCM_AddJSONToExcludedFileTypes extends WFCM_AbstractUpdateWrapper implements WFCM_UpdateWrapperInterface {

	/**
	 * Setup old and new version properties and register this routine if the
	 * conditions pass checks.
	 *
	 * @method __construct
	 * @param string $old_version the old version string.
	 * @param string $new_version the new version string.
	 *
	 * @since  1.5.0
	 */
	public function __construct( $old_version, $new_version ) {
		$this->key         = 'add_json_to_excluded_extensions';
		$this->max_version = '1.5.1';
		$this->min_version = '1.4.1';

		parent::__construct( $old_version, $new_version );
	}

	/**
	 * Do the work.
	 *
	 * @method run
	 * @since  1.5.0
	 */
	public function run() {
		// track if we have made updates to the data that need saved.
		$extensions_added    = array();
		// get the current array of excluded extensions.
		$excluded_extensions = WFCM_Settings::get_setting( 'scan-exclude-exts' );
		if ( is_array( $excluded_extensions ) ) {
			// loop through extensions we want added to the current list.
			$extensions_to_add = $this->get_extensions_to_add();
			foreach ( $extensions_to_add as $extension ) {
				// if the extension we want added isn't in current list add it.
				if ( ! in_array( $extension, $excluded_extensions, true ) ) {
					$excluded_extensions[] = $extension;
					$extensions_added[]    = $extension;
				}
			}
		}
		// if we modified the list...
		if ( ! empty( $extensions_added ) ) {
			// then update the option.
			WFCM_Settings::save_setting( 'scan-exclude-exts', $excluded_extensions );
			// remove json files from list of known files so that it does not
			// trigger lots of 'deleted' file events for users.
			foreach ( $this->get_file_list_numbers() as $end_fragment ) {
				// assume not modified until determined otherwise.
				$file_list_modified = false;
				// get a file list to work with.
				$current_file_list = WFCM_Settings::get_setting( "local-files-{$end_fragment}" );
				foreach ( $current_file_list as $file => $hash ) {
					// check the file against list of extensions we are adding.
					foreach ( $extensions_added as $extension ) {
						// if the end of the filename matches the extension...
						if ( 0 === substr_compare( $file, $extension, - strlen( $extension ) ) ) {
							// unset this file from array, flag it as modified.
							unset( $current_file_list[ $file ] );
							$file_list_modified = true;
						}
					}
				}
				// if this list was modified then save it.
				if ( $file_list_modified ) {
					WFCM_Settings::save_setting( "local-files-{$end_fragment}", $current_file_list );
				}
			}
		}

		// mark as finished.
		$this->finished = true;
	}

	/**
	 * Array of extensions to be added to the list of excluded extensions.
	 *
	 * @method get_extensions_to_add
	 * @return array of strings
	 * @since  1.5.0
	 */
	private function get_extensions_to_add() {
		return array( 'json' );
	}

	/**
	 * Get an array of the numbers used as end fragments on file list options.
	 *
	 * @method file_list_numbers
	 * @return array of ints
	 * @since  1.5.0
	 */
	private function get_file_list_numbers() {
		return array( 0, 1, 2, 3, 4, 5, 6 );
	}

}
