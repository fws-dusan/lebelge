<?php
/**
 * WFCM Hash Comparator interface.
 *
 * @package wfcm
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Hash comparator interface.
 *
 * The interface is meant to allow alternative file hash comparison.
 *
 * @since 1.7.0
 */
interface WFCM_Hash_Comparator_Interface {

	/**
	 * Compares the hashes of two given files. Locally calculated hashes are provided for performance reasons since they
	 * are already available when the comparator is used. These are used in the default comparator.
	 *
	 * @param string $file1 Full path to the first file.
	 * @param string $local_hash1 Local hash for the first file.
	 * @param string $file2 Full path to the second file.
	 * @param string $local_hash2 Local hash for the second file.
	 *
	 * @return bool True if the hashes match. False otherwise.
	 */
	public function compare( $file1, $local_hash1, $file2, $local_hash2 );
}
