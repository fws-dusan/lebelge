<?php
/**
 * WFCM Event.
 *
 * @package wfcm
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * WFCM Event Abstract Class.
 *
 * This is the base class for the file change events.
 */
abstract class WFCM_Event {

	/**
	 * Event ID.
	 *
	 * @var int
	 */
	protected $event_id = 0;

	/**
	 * Event Title.
	 *
	 * @var string
	 */
	protected $event_file_path    = '';
	protected $event_type         = '';
	protected $event_content      = '';
	protected $event_content_type = '';
	protected $event_origin       = '';
	protected $event_context      = '';
	protected $event_date         = '';

	/**
	 * Event Data.
	 *
	 * @var array
	 */
	protected $data = array(
		'event_type'        => '',       // Event type: added, modified, or deleted.
		'event_read_status' => 'unread', // Event status.
		'event_content'     => '',       // Content.
		'event_context'     => '',       // Event context.
		'event_file_path'   => '',
		'event_origin'      => '',
		'event_date'        => '',
	);

	/**
	 * Event Post Object.
	 *
	 * @var WP_Post
	 */
	public $event_post = null;

	/**
	 * Constructor.
	 *
	 * @param int|WP_Post|bool $event - (Optional) Event id.
	 */
	public function __construct( $event = false ) {
		if ( is_numeric( $event ) ) {
			$this->event_id          = (int) $event;
			$this->event_post  = WFCM_Database_DB_Data_Store::get_file_event( $this->id );
			$this->load_event_data();
		}
	}

	/**
	 * Load event data.
	 */
	protected function load_event_data() {
		$this->reset_event_data();

		foreach ( $this->data as $key => $value ) {
			$get_meta = "get_$key";
			$this->$get_meta();
		}
	}

	/**
	 * Reset event data.
	 */
	protected function reset_event_data() {
		foreach ( $this->data as $key => $value ) {
			$set_meta = "set_$key";
			$this->$set_meta( '' );
		}
	}

	/**
	 * Save event.
	 *
	 * Event is saved in WordPress post table and
	 * event meta in the postmeta table.
	 */
	public function save() {
		$column_formats =  WFCM_Database_DB_Data_Store::get_columns();
		$data           = array_change_key_case( $this->data );
		$data           = array_intersect_key( $data, $column_formats );
		$insert_data    = WFCM_Database_DB_Data_Store::insert( $data );

		if ( ! $insert_data ) {
			return false;
		}

		$this->event_id = $insert_data;

		// delete the transient that holds this event types count.
		delete_transient( "wfcm_event_type_tabs_count_{$this->data['event_type']}" );

		// Let sensor know we have added an event.
		do_action( "wfcm_wsal_file_{$this->data['event_type']}" );
	}

	/*********************************************************
	 * Event Setters.
	 *********************************************************/

	/**
	 * Set event id.
	 *
	 * @param string $id - Event id.
	 */
	public function set_event_id( $id ) {
		$this->event_id = $id;
	}

	/**
	 * Set event file path.
	 *
	 * @param string $file - File path.
	 */
	public function set_event_file_path( $file ) {
		return $this->set_meta( 'event_file_path', $file );
	}

	/**
	 * Set Event Meta.
	 *
	 * @param string $key   - Meta key.
	 * @param mixed  $value - Meta value.
	 * @return mixed|WP_Error
	 */
	protected function set_meta( $key, $value ) {
		if ( isset( $this->data[ $key ] ) ) {
			$this->data[ $key ] = $value;
			return $value;
		}
		return new WP_Error( 'wfcm_invalid_event_data', __( 'Invalid event data.', 'website-file-changes-monitor' ) );
	}

	/**
	 * Set event type; added, modified, or deleted.
	 *
	 * @param string $event_type - Event type.
	 * @return string
	 */
	public function set_event_type( $event_type ) {
		return $this->set_meta( 'event_type', $event_type );
	}

	/**
	 * Set event status; unread or read.
	 *
	 * @param string $status - Event status.
	 * @return string
	 */
	public function set_event_read_status( $status ) {
		return $this->set_meta( 'event_read_status', $status );
	}

	/**
	 * Set content of event.
	 *
	 * @param stdClass $content - Event content.
	 * @return stdClass
	 */
	public function set_event_content( $content ) {
		return $this->set_meta( 'event_content', maybe_serialize( $content ) );
	}

	/**
	 * Set content type; file or directory.
	 *
	 * @param string $content_type - Content type.
	 * @return string
	 */
	public function set_event_content_type( $content_type ) {
		return $this->set_meta( 'event_content_type', $content_type );
	}

	/**
	 * Set content type.
	 *
	 * @param string $event_context - Content type.
	 *
	 * @return string
	 */
	public function set_event_context( $event_context ) {
		return $this->set_meta( 'event_context', $event_context );
	}

	public function set_event_origin( $origin ) {
		return $this->set_meta( 'event_origin', $origin );
	}

	public function set_event_date( $date ) {
		return $this->set_meta( 'event_date', $date );
	}

	/*********************************************************
	 * Event Getters.
	 *********************************************************/

	/**
	 * Get event id.
	 *
	 * @return int
	 */
	public function get_event_id() {
		return $this->event_id;
	}

	/**
	 * Get event title.
	 *
	 * @return string
	 */
	public function get_event_file_path() {
		return $this->get_meta( 'event_file_path' );
	}

	/**
	 * Get Event Meta.
	 *
	 * @param string $key - Meta key.
	 */
	protected function get_meta( $key ) {
		if ( empty( $this->data[ $key ] ) ) {
			$this->data[ $key ] = WFCM_Database_DB_Data_Store::get_file_event_data( $this->event_id, $key );
		}

		return $this->data[ $key ];
	}

	/**
	 * Returns event type.
	 *
	 * @return string
	 */
	public function get_event_type() {
		return $this->get_meta( 'event_type' );
	}

	/**
	 * Returns event status.
	 *
	 * @return string
	 */
	public function get_event_read_status() {
		return $this->get_meta( 'event_read_status' );
	}

	/**
	 * Returns content of event.
	 *
	 * @return string
	 */
	public function get_event_content() {
		return $this->get_meta( 'event_content' );
	}

	/**
	 * Returns content type.
	 *
	 * @return string
	 */
	public function get_event_content_type() {
		return $this->get_meta( 'event_content_type' );
	}
	/**
	 * Returns event context.
	 *
	 * @return string
	 */
	public function get_event_context() {
		return $this->get_meta( 'event_context' );
	}

	public function get_event_date() {
		return $this->get_meta( 'event_date' );
	}
}
