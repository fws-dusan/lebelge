<?php
/**
 * WFCM Default Hash Comparator class.
 *
 * @package wfcm
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Default hash comparator. Uses already calculated hashes and matches them as strings.
 *
 * @since 1.7.0
 */
class WFCM_Default_Hash_Comparator implements WFCM_Hash_Comparator_Interface {
	public function compare( $file1, $local_hash1, $file2, $local_hash2 ) {
		return $local_hash1 === $local_hash2;
	}
}
