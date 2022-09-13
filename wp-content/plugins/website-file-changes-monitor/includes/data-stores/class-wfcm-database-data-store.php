<?php
/**
 * WFCM Event Post Type Data Store.
 *
 * @package wfcm
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Event post type data store.
 */
class WFCM_Database_DB_Data_Store {

	/**
	 * Create and return an array reprasenting each column of our table.
	 *
	 * @return array
	 */
	public static function get_columns() {
		return [
			'event_id'           => '%d',
			'event_date'         => '%s',
			'event_file_path'    => '%s',
			'event_type'         => '%s',
			'event_context'      => '%s',
			'event_content'      => '%s',
			'event_content_type' => '%s',
			'event_origin'       => '%s',
			'event_read_status'  => '%s',
		];
	}

	/**
	 * Return default values for a row.
	 *
	 * @return array
	 */
	public static function get_column_defaults() {
		return [
			'event_date'         => '',
			'event_file_path'    => '',
			'event_type'         => '',
			'event_context'      => '',
			'event_content'      => '',
			'event_content_type' => 'file',
			'event_origin'       => 'local',
			'event_read_status'  => 'unread',
		];
	}

	/**
	 * Build new table.
	 *
	 */
	public static function create_database_table() {
		
		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	   
		$table_name = self::get_events_table_name();
		$sql = "CREATE TABLE IF NOT EXISTS $table_name (
		event_id INTEGER NOT NULL AUTO_INCREMENT,
		event_date DATETIME DEFAULT '0000-00-00 00:00:00' NOT NULL,
		event_file_path TEXT NOT NULL,
		event_type varchar(20) NOT NULL,
		event_context varchar(50) NOT NULL,
		event_content TEXT NOT NULL,
		event_content_type varchar(10) NOT NULL,
		event_origin TEXT NOT NULL,
		event_read_status varchar(10) NOT NULL,
		PRIMARY KEY (event_id)
		) $charset_collate;";

		dbDelta( $sql );
	}

	/**
	 * Create and insert data into the events table.
	 *
	 * @param [type] $data
	 * @return void
	 */
	public static function insert( $data ) {

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		$column_formats = self::get_columns();
		$data = array_change_key_case( $data );
		$data = array_intersect_key( $data, $column_formats );

		// Apply current time.
		$data[ 'event_date' ] = current_time( 'mysql' );

		global $wpdb;
		$table_name = self::get_events_table_name();

		// Check if item already exists to avoid repeating the notice
		$already_exists      = self::check_if_exists( 'event_file_path', $data[ 'event_file_path' ] );
		$already_exists_type = self::check_if_exists( 'event_type', $data[ 'event_type' ] );

		if ( $already_exists && $already_exists_type && 'directory' !== $data[ 'event_content_type' ]) {
			return false;
		}

		// Insert the new item.
		$wpdb->insert( $table_name, $data );
		$wpdb_insert_id = $wpdb->insert_id;
		
		return $wpdb_insert_id;
	}

	/**
	 * Delete an individial event.
	 *
	 * @param int $event_id
	 *
	 * @return bool
	 */
	public static function delete_event( $event_id ) {
		global $wpdb;

		$table_name = self::get_events_table_name();
		$wpdb->delete( $table_name, array( 'event_id' => $event_id ) );

		return true;
	}

	/**
	 * Delete all events by type..
	 *
	 * @param array Types to delete.
	 *
	 * @return bool
	 */
	public static function delete_events( $event_types ) {
		global $wpdb;

		$table_name = self::get_events_table_name();
		
		foreach ( $event_types as $event_type ) {
			$wpdb->delete( $table_name, array( 'event_type' => $event_type ) );
		}

		return true;
	}

	/** 
	 * Get total number of events either per event_type or for ALL possible events.
	 */
	public static function get_total_events_count( $event_type, $unread_only = false ) {
		global $wpdb;

		$table_name = self::get_events_table_name();

		if ( 'all' === $event_type ) {
			$sql = "SELECT * FROM $table_name";
		} else {
			$sql = $wpdb->prepare( "SELECT * FROM $table_name WHERE `event_type` = %s", $event_type );
		}

		// Only return new items if needed.
		if ( $unread_only  ) {
			$since = date( 'Y-m-d H:i:s', wfcm_get_setting( 'last-scan-start' ) );
			$sql  .= $wpdb->prepare(
				" AND `event_date` >= %s",
				$since
			);
		}

		$rowcount = $wpdb->get_results( $sql, ARRAY_A );
	
		return count( $rowcount );
	}

	/**
	 * Retrieve file events from the database. If paged arg is present, we also add pagination data, otherwise we just return the events data. 
	 * Uses similar args to regular post query.
	 *
	 * @param array   $args
	 * @param boolean $basic_only
	 * @return object
	 */
	public static function get_file_events( $args, $basic_only = false ) {
		
		$limit         = ( isset( $args['posts_per_page'] ) ) ? $args['posts_per_page'] : false;
		$event_type    = ( isset( $args['event_type'] ) ) ? $args['event_type'] : false;
		$status        = ( isset( $args['status'] ) ) ? $args['status'] : 'unread';
		$paged         = ( isset( $args['paged'] ) ) ? $args['paged'] : false;
		$since         = ( isset( $args['retrieve_events_since'] ) && ! empty( $args['retrieve_events_since'] ) ) ? date( 'Y-m-d H:i:s', $args['retrieve_events_since'] ) : false;
		global $wpdb;


		// Return early this is not set.
		if ( ! $event_type  ) {
			return;
		}

		$table_name = self::get_events_table_name();

		// Start to build the query.
		$sql = $wpdb->prepare( "SELECT * FROM $table_name WHERE `event_type` = '%s' AND `event_read_status` = '%s'", [ $event_type, $status ] );

		// If we only want stuff more recent than $since, lets do the magic.
		if ( $since ) {
			$sql .= $wpdb->prepare( " AND `event_date` >= '%s'", $since );
			$basics = [ 'Core Update', 'Theme Update', 'Plugin Update', 'Plugin Install', 'Theme Install', 'Plugin Uninstall', 'Theme Uninstall' ];
			$basics_string = "'" . implode( "', '", $basics ) . "'";

			if ( $basic_only ) {
				$sql .= ' AND `event_context` NOT IN ( '. $basics_string  .' )';
			} else {
				$sql .= ' AND `event_context` IN ( '. $basics_string  .' )';
			}
		}

		// Do I need prepare?
		$sql .= " ORDER BY `event_date` DESC";

		if ( $paged ) {
			$offset = ( $paged * $limit ) - $limit;
			$sql .= $wpdb->prepare( " LIMIT %d, %d", [ $offset, $limit ] );
		}

		// Fire off our lovely prepared query.
		$events = $wpdb->get_results( $sql );

		foreach ( $events as $event_key => $event_data ) {
			$event_obj = new WFCM_Event_File();
			$event_obj->set_event_id( $event_data->event_id );
			$event_obj->set_event_date( $event_data->event_date );
			$event_obj->set_event_file_path( $event_data->event_file_path );
			$event_obj->set_event_type( $event_data->event_type );
			$event_obj->set_event_context( $event_data->event_context );
			$event_obj->set_event_content( maybe_unserialize( $event_data->event_content ) );
			$event_obj->set_event_content_type( $event_data->event_content_type );
			$event_obj->set_event_origin( $event_data->event_origin );
			$event_obj->set_event_read_status( $event_data->event_read_status );

			$events[ $event_key ] = $event_obj;
		}

		$events_object = new WFCM_Database_Events_Data;
		$events_object->set_events( $events );

		// Only add page/count data if needed.
		if ( $paged ) {
			$total = self::get_total_events_count( $event_type );
			$events_object->set_total( $total );
			$max_num_pages = ( $paged ) ? round( $total / $limit ) : false;
			$events_object->set_max_num_pages( $max_num_pages );
		}

		return $events_object;
	}

	/** 
	 * Get an events details from its uniuqe ID.
	 */
	public static function get_file_event( $event_id ) {

		$table_name = self::get_events_table_name();

		global $wpdb;

		$event = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM $table_name
				 WHERE `event_id` = %d
				 LIMIT 1",
				$event_id
			)
		);

		$event_data = ( isset( $event[0] ) ) ? $event[0] : false;

		if ( $event_data ) {
			$event = new WFCM_Event_File();
			$event->set_event_id( $event_data->event_id );
			$event->set_event_date( $event_data->event_date );
			$event->set_event_file_path( $event_data->event_file_path );
			$event->set_event_type( $event_data->event_type );
			$event->set_event_context( $event_data->event_context );
			$event->set_event_content( $event_data->event_content );
			$event->set_event_content_type( $event_data->event_content_type );
			$event->set_event_origin( $event_data->event_origin );
			$event->set_event_read_status( $event_data->event_read_status );

			return $event;
		}

		return $event_data;
	}

	/**
	 * Simple helper to check the existence of a specific value "value_to_check" in a specific column in the table "column_to_lookup" 
	 *
	 * @param [type] $args
	 * @return void
	 */
	public static function check_if_exists( $column_to_lookup, $value_to_check ) {
		
		$table_name       = self::get_events_table_name();
		
		global $wpdb;
		$event_id = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT event_id FROM $table_name 
				 WHERE {$column_to_lookup} = %s
				 LIMIT 1",
				$value_to_check
			)
		);

		return $event_id;
	}

	public static function get_file_event_data( $event_id, $key ) {
		
		$table_name = self::get_events_table_name();

		global $wpdb;

		$event = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT $key FROM $table_name
				 WHERE `event_id` = %d
				 LIMIT 1",
				$event_id
			)
		);
		
		return ( $event ) ? $event : '';
	}

	/**
	 * Creates a tidy, useable table name for quries.
	 *
	 * @return void
	 */
	private static function get_events_table_name() {
		global $wpdb;
		return $wpdb->prefix . WFCM_FILE_EVENTS_TABLE;
	}
}
