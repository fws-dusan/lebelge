<?php
/**
 * WFCM File Event.
 *
 * @package wfcm
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * WFCM File Event Class.
 *
 * Handle the event of a single file.
 */
class WFCM_Event_File extends WFCM_Event {

	/**
	 * Constructor.
	 *
	 * @param int|bool $event_id - (Optional) Event id.
	 */
	public function __construct( $event_id = false ) {
		$this->data['event_content_type'] = 'file'; // Content type.
		$this->data['event_origin']       = ''; // File event origin
		parent::__construct( $event_id );
	}

	/**
	 * Sets the origin of file event.
	 *
	 * @param string $origin File event origin.
	 *
	 * @return string
	 */
	public function set_event_origin( $origin ) {
		return $this->set_meta( 'event_origin', $origin );
	}

	/**
	 * Returns the origin of file event.
	 *
	 * @return string yes or no
	 */
	public function get_event_origin() {
		return ( ! empty( $this->get_meta( 'event_origin' ) ) ) ? $this->get_meta( 'event_origin' ) : 'local';
	}

}
