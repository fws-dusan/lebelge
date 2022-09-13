<?php

class WFCM_DeleteFileEventPosts extends WFCM_AbstractUpdateWrapper {

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
		$this->key         = 'delete_obselete_file_events_posts';
		$this->max_version = '1.8.0';
		$this->min_version = '1.6.0';

		parent::__construct( $old_version, $new_version );
	}

	/**
	 * Do the work.
	 *
	 * @method run
	 * @since  1.7.0
	 */
	public function run() {
		global $wpdb;

		// Delete wfcm_file_event posts + data.
		$wpdb->query( "DELETE FROM {$wpdb->posts} WHERE post_type = 'wfcm_file_event';" );
		$wpdb->query( "DELETE meta FROM {$wpdb->postmeta} meta LEFT JOIN {$wpdb->posts} posts ON posts.ID = meta.post_id WHERE posts.ID IS NULL;" );

		//  mark as finished
		$this->finished = true;
	}
}
