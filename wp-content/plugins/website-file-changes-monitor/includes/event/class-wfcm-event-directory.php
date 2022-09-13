<?php
/**
 * WFCM Directory Event.
 *
 * @package wfcm
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * WFCM Directory Event Class.
 *
 * Handle the events of directories like when a plugin
 * is installed, updated, or removed, etc.
 */
class WFCM_Event_Directory extends WFCM_Event {

	/**
	 * Constructor.
	 *
	 * @param int|bool $event_id - (Optional) Event id.
	 */
	public function __construct( $event_id = false ) {
		$this->data['event_content_type']  = 'directory'; // Content type.
		parent::__construct( $event_id );
	}

	/**
	 * Set content type.
	 *
	 * @param string $content_type - Content type.
	 * @return string
	 */
	public function set_event_content_type( $content_type ) {
		return $this->set_meta( 'event_content_type', $content_type );
	}

	/**
	 * Returns content type.
	 *
	 * @return string
	 */
	public function get_event_content_type() {
		return $this->get_meta( 'event_content_type' );
	}
}
