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
 * Hash comparator checking checksums calculated by wordpress.org repo.
 *
 * Uses checksums downloaded from wordpress.org repo.
 *
 * @since 1.7.0
 */
class WFCM_WordPressOrg_Hash_Comparator implements WFCM_Hash_Comparator_Interface {
	/**
	 * {@inheritDoc}
	 *
	 * First file is expected to be file with hash already calculate and loaded from wordpress.org repo. Second hash is
	 * the locally calculated one. This needs to be ignore and calculated using MD5.
	 */
	public function compare( $file1, $local_hash1, $file2, $local_hash2 ) {
		return $local_hash1 === @hash_file( 'md5', $file2 );
	}
}
