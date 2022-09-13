<?php
/**
 * WFCM REST API.
 *
 * @package wfcm
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * WFCM REST API Class.
 *
 * This class registers and handles the REST API requests of the plugin.
 */
class WFCM_REST_API {

	/**
	 * Monitor events base.
	 *
	 * @var string
	 */
	public static $monitor_base = '/monitor';

	/**
	 * Events base.
	 *
	 * @var string
	 */
	public static $events_base = '/monitor-events';

	/**
	 * Base to use in REST requests for marking all as read.
	 *
	 * @var string
	 */
	public static $mark_all_read_base = '/mark-all-read';

	/**
	 * Base to use in REST requests for marking events within folder as read.
	 *
	 * @var string
	 */
	public static $mark_read_dir = '/mark-read-dir';

	/**
	 * Admin notices base.
	 *
	 * @var string
	 */
	public static $admin_notices = '/admin-notices';

	/**
	 * Base to use in REST requests for allowing files or directories in the site root or WP core area.
	 *
	 * @var string
	 * @since 1.7.0
	 */
	public static $allow_in_core = '/allow-in-core';

	/**
	 * How many batch events are processed in one go?
	 *
	 * @var int
	 */
	private static $batch_size = 50;

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'rest_api_init', array( $this, 'register_monitor_rest_routes' ) );
		add_action( 'rest_api_init', array( $this, 'register_events_rest_routes' ) );
		add_action( 'rest_api_init', array( $this, 'register_admin_notices_rest_routes' ) );
	}

	/**
	 * Register Rest Route for Scanning.
	 */
	public function register_monitor_rest_routes() {
		// Start scan route.
		register_rest_route(
			WFCM_REST_NAMESPACE,
			self::$monitor_base . '/start',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'scan_start' ),
				'permission_callback' => function () {
					return current_user_can( 'manage_options' );
				},
			)
		);

		// Stop scan route.
		register_rest_route(
			WFCM_REST_NAMESPACE,
			self::$monitor_base . '/stop',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'scan_stop' ),
				'permission_callback' => function () {
					return current_user_can( 'manage_options' );
				},
			)
		);
	}

	/**
	 * Register Rest Route for Scanning.
	 */
	public function register_events_rest_routes() {
		// Register rest route for getting events.
		register_rest_route(
			WFCM_REST_NAMESPACE,
			self::$events_base . '/(?P<event_type>[\S]+)',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_events' ),
				'permission_callback' => function () {
					return current_user_can( 'manage_options' );
				},
				'args'                => [
					'event_type' => [
						'validate_callback' => function ( $param, $request, $key ) {
							return $this->validate_event_type( $param );
						}
					]
				]
			)
		);

		// Register rest route for removing an event.
		register_rest_route(
			WFCM_REST_NAMESPACE,
			self::$events_base . '/(?P<event_id>[\d]+)',
			array(
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => array( $this, 'delete_event' ),
				'permission_callback' => function () {
					return current_user_can( 'manage_options' );
				},
				'args'                => array(
					'event_id'    => [
						'validate_callback' => function ( $param, $request, $key ) {
							return is_numeric( $param );
						},
					],
					'exclude'     => array(
						'type'        => 'boolean',
						'default'     => false,
						'description' => __( 'Whether to exclude the content in future scans or not', 'website-file-changes-monitor' ),
					),
					'excludeType' => array(
						'type'        => 'string',
						'default'     => 'file',
						'description' => __( 'The type of exclusion, i.e., file or directory.', 'website-file-changes-monitor' ),
					),
				),
			)
		);

		// Register rest route for removing events.
		register_rest_route(
			WFCM_REST_NAMESPACE,
			self::$mark_all_read_base . '/(?P<event_type>[\S]+)',
			array(
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => array( $this, 'delete_events' ),
				'permission_callback' => function () {
					return current_user_can( 'manage_options' );
				},
				'args'                => [
					'event_type' => [
						'validate_callback' => function ( $param, $request, $key ) {
							return $this->validate_event_type( $param );
						}
					]
				]
			)
		);

		//  register rest route for adding files and folders to the list of allowed in site root and/or WordPress core
		//  folders
		register_rest_route(
			WFCM_REST_NAMESPACE,
			self::$allow_in_core . '/(?P<event_id>[\d]+)',
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'allow_event_in_core' ),
				'permission_callback' => function () {
					return current_user_can( 'manage_options' );
				},
				'args'                => array(
					'event_id'   => [
						'validate_callback' => function ( $param, $request, $key ) {
							return is_numeric( $param );
						},
					],
					'targetType' => array(
						'type'              => 'string',
						'default'           => 'file',
						'description'       => __( 'The type of target, i.e., file or directory.', 'website-file-changes-monitor' ),
						'validate_callback' => function ( $param, $request, $key ) {
							return in_array( $param, [ 'dir', 'file' ] );
						},
					),
				),
			)
		);

		// Register rest route for deleting events within a specific folder.
		register_rest_route(
			WFCM_REST_NAMESPACE,
			self::$mark_read_dir,
			array(
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => array( $this, 'delete_events_within_folder' ),
				'permission_callback' => function () {
					return current_user_can( 'manage_options' );
				}
			)
		);

	}

	public function validate_event_type( $value ) {
		if ( 'all' === $value ) {
			return true;
		}

		return in_array( preg_replace( '/-files/', '', $value ), WFCM_REST_API::get_event_types(), true );
	}

	/**
	 * Get a list of supported event types.
	 *
	 * @method get_event_types
	 * @return array
	 * @since  1.5.0
	 */
	public static function get_event_types() {
		return array(
			'added',
			'modified',
			'deleted',
		);
	}

	/**
	 * Register rest route for admin notices.
	 */
	public function register_admin_notices_rest_routes() {
		// Register rest route dismissing admin notice.
		register_rest_route(
			WFCM_REST_NAMESPACE,
			self::$admin_notices . '/(?P<noticeKey>[\S]+)',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'dismiss_admin_notice' ),
				'permission_callback' => function () {
					return current_user_can( 'manage_options' );
				},
				'args'                => [
					'noticeKey' => [
						'validate_callback' => function ( $param ) {
							return filter_var( $param, FILTER_SANITIZE_STRING );
						}
					]
				]
			)
		);
	}

	/**
	 * REST API callback for start scan request.
	 *
	 * @return boolean
	 */
	public function scan_start() {

		// Run a manual scan of all directories.
		wfcm_get_monitor()->scan_file_changes();

		wfcm_delete_setting( 'scan-stop' );
		$datetime_format = get_option( 'date_format' ) . ' ' . get_option( 'time_format' );
		$last_scan_time  = wfcm_get_setting( 'last-scan-timestamp', false );
		$last_scan_time  = $last_scan_time + ( get_option( 'gmt_offset' ) * 60 * 60 );
		$last_scan_time  = date( $datetime_format, $last_scan_time );

		return $last_scan_time;
	}

	/**
	 * REST API callback for stop scan request.
	 *
	 * @return boolean
	 */
	public function scan_stop() {
		wfcm_save_setting( 'scan-stop', true );

		return true;
	}

	/**
	 * REST API callback for fetching created file events.
	 *
	 * @param WP_REST_Request $rest_request - REST request object.
	 *
	 * @return WP_Error|string - JSON string of events.
	 */
	public function get_events( $rest_request ) {
		// Get event params from request object.
		$event_type = $rest_request->get_param( 'event_type' );
		$paged      = $rest_request->get_param( 'paged' );
		$per_page   = $rest_request->get_param( 'per-page' );

		// Validate request variables.
		$paged    = is_int( $paged ) ? $paged : (int) $paged;
		$per_page = 'false' === $per_page ? false : (int) $per_page;

		if ( ! $event_type ) {
			return new WP_Error( 'wfcm_empty_event_type', __( 'No event type specified for the request.', 'website-file-changes-monitor' ), array( 'status' => 404 ) );
		}

		// Get event type stored per page option.
		$per_page_opt_name = $event_type . '-per-page';
		$stored_per_page   = wfcm_get_setting( $per_page_opt_name );

		if ( false === $per_page ) {
			if ( ! $stored_per_page ) {
				$per_page = 10;
			} else {
				$per_page = $stored_per_page;
			}
		} elseif ( $per_page !== $stored_per_page ) {
			wfcm_save_setting( $per_page_opt_name, $per_page );
		}

		// Set events query arguments.
		$event_args = array(
			'event_type'     => $event_type,
			'posts_per_page' => $per_page,
			'paginate'       => true,
			'paged'          => $paged,
		);

		// Query events.
		$events_query = wfcm_get_events( $event_args );

		$events_found = $events_query->get_events();

		// Transform events so they are ready for display.
		$formatted_events = wfcm_get_events_for_js( $events_found );

		// Update the query with the newly formatted goodness.
		$events_query->set_events( $formatted_events );

		$response = new WP_REST_Response( $events_query );
		$response->set_status( 200 );

		return $response;
	}

	/**
	 * REST API callback for marking events as read.
	 *
	 * @param WP_REST_Request $rest_request - REST request object.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function delete_event( $rest_request ) {
		// Get event id from request.
		$event_id = $rest_request->get_param( 'event_id' );

		if ( ! $event_id ) {
			return new WP_Error( 'wfcm_empty_event_id', __( 'No event id specified for the request.', 'website-file-changes-monitor' ), array( 'status' => 404 ) );
		}

		// Get request body to check if event is excluded.
		$request_body  = $rest_request->get_body();
		$request_body  = json_decode( $request_body );
		$is_excluded   = isset( $request_body->exclude ) ? $request_body->exclude : false;
		$excluded_type = isset( $request_body->excludeType ) ? $request_body->excludeType : false;

		$event = null;
		if ( $is_excluded ) {
			$event = wfcm_get_event( $event_id );
			//  update the lists of files and folders allowed in core as needed
			$this->updateSetOfFileAndDirOptions( $event_id, 'scan-exclude-files', 'scan-exclude-dirs', $excluded_type );
		}

		//  delete the event in both case (with and without the exclusion)
		$result = [
			'success' => WFCM_REST_API::processEventDeletion( $event_id )
		];

		if ( true === $result['success'] && $is_excluded && 'dir' === $excluded_type && $event instanceof WFCM_Event_File ) {
			//  add data necessary to show a popup about batch event deletion
			$dirpath           = dirname( $event->get_event_file_path() );
			$result['path']    = $dirpath;
			$result['title']   = esc_html__( 'Directory excluded', 'website-file-changes-monitor' );
			$result['message'] = sprintf(
				                     esc_html__( 'You have excluded directory %s from the file scans.', 'website-file-changes-monitor' ),
				                     $dirpath
			                     ) . ' ' . esc_html__( 'Do you want to delete the existing file changes notifications about files in this directory? If you do, the process will run in the background. Please revisit the file changes results later for an updated log.', 'website-file-changes-monitor' );
		}

		return $result;
	}

	/**
	 * Function uses event with given event ID to update a set of plugin options representing some sort of files and
	 * folders. It is used for example to add a folder of file to a list of exluded files or folders.
	 *
	 * @param int $event_id Event ID.
	 * @param string $files_option_name Option name of the option containing a list of files.
	 * @param string $dirs_option_name Option name of the option containing a list of files.
	 * @param string $target_type Target type - dir or file.
	 *
	 * @throws Exception
	 * @since 1.7.0
	 */
	private function updateSetOfFileAndDirOptions( $event_id, $files_option_name, $dirs_option_name, $target_type ) {
		$event = wfcm_get_event( $event_id );
		
		if ( false === $event ) {
			return;
		}

		$get_content_type = $event->get_event_content_type();

		$content_type = ( isset( $get_content_type ) ) ? $get_content_type : false;

		if ( ! in_array( $content_type, [ 'file', 'directory' ] ) ||  false === $event ) {
			return;
		}

		$content_to_add    = null;
		$setting_to_update = $dirs_option_name;
		$event_title       = $event->get_event_file_path();

		if ( 'file' === $content_type ) {
			if ( 'file' === $target_type ) {
				$setting_to_update = $files_option_name;
			}

			if ( 'dir' === $target_type ) {
				$content_to_add = dirname( $event_title );
			} else {
				$content_to_add = basename( $event_title );
			}
		} elseif ( 'directory' === $content_type ) {
			$content_to_add = $event_title;
		}

		if ( null === $content_to_add ) {
			return;
		}

		//  get current list
		$content_to_update = wfcm_get_setting( $setting_to_update, array() );
		//  add a new item to the list
		$content_to_update[] = $content_to_add;
		//  ensure no duplicated entries
		$content_to_update = array_unique( $content_to_update );
		//  save back to db
		wfcm_save_setting( $setting_to_update, $content_to_update );
	}

	/**
	 * Deletes an event and deletes necessary counters in the db as well.
	 *
	 * @param int $event_id Event ID.
	 *
	 * @return bool True if the event was deleted. False otherwise.
	 * @since 1.7.0
	 */
	public static function processEventDeletion( $event_id ) {
		$event_type = wfcm_get_event( $event_id );
		$event_type = $event_type->get_event_type( $event_type );

		$event_deleted = WFCM_Database_DB_Data_Store::delete_event( $event_id );
		if ( $event_deleted ) {
			if ( $event_type ) {
				delete_transient( "wfcm_event_type_tabs_count_{$event_type}" );
			}

			return true;
		}

		return false;
	}

	/**
	 * Rest endpoint to delete all of a given type (or all types) of event.
	 *
	 * Note: any files categorized as "unexpected WordPress core file" will be added to the list of allowed core files
	 * before deletion.
	 *
	 * @method delete_events
	 * @param WP_REST_Request $rest_request - REST request object.
	 *
	 * @return WP_Error|WP_REST_Response
	 * @throws Exception
	 * @since  1.5.0
	 */
	public function delete_events( $rest_request ) {
		// Get event event from request.
		$event_type = $rest_request->get_param( 'event_type' );
		$event_type = ( 'all' === $event_type ) ? 'all' : rtrim( $event_type, '-files' );

		if ( ! $event_type || ! in_array( $event_type, array_merge( WFCM_REST_API::get_event_types(), array( 'all' ) ), true ) ) {
			return new WP_Error( 'wfcm_empty_event_type', __( 'No event type specified for the request.', 'website-file-changes-monitor' ), array( 'status' => 404 ) );
		}

		//  determine event types
		$event_types = ( 'all' !== $event_type ) ? array( $event_type ) : WFCM_REST_API::get_event_types();

		//  check if there are any unexpected WordPress core files to be added to the allowed list
		if ( in_array( 'added', $event_types ) ) {
			$event_args = array(
				'nopaging'   => true,
				'event_type' => 'added',
				'origin'     => 'wp.org'
			);

			$unexpected_core_files = wfcm_get_events( $event_args );

			//  keep a list of already allowed files for performance boost with duplicates
			$already_allowed_files = [];
			if ( ! empty( $unexpected_core_files ) ) {
				/** @var WFCM_Event $unexpected_core_file */
				foreach ( $unexpected_core_files->events as $unexpected_core_file ) {
					//  update the lists of files and folders allowed in core as needed
					$filename = basename( $unexpected_core_file->get_event_file_path() );
					if ( ! in_array( $filename, $already_allowed_files ) ) {
						$this->updateSetOfFileAndDirOptions( $unexpected_core_file->get_event_id(), 'scan-allowed-in-core-files', 'scan-allowed-in-core-dirs', 'file' );
						array_push( $already_allowed_files, $filename );
					}
				}
			}

		}

		//  delete events
		$deleted_posts = WFCM_Database_DB_Data_Store::delete_events( $event_types );

		// return a successful response along with ids query was run with.
		$response = new WP_REST_Response(
			array(
				'success'        => true,
				'deleted_events' => $deleted_posts
			)
		);
		$response->set_status( 200 );

		return $response;
	}

	/**
	 * REST API callback for dismissing admin notice.
	 *
	 * @param WP_REST_Request $rest_request - REST request object.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function dismiss_admin_notice( $rest_request ) {
		// Get admin notice id.
		$notice_key = $rest_request->get_param( 'noticeKey' );

		if ( ! $notice_key ) {
			return new WP_Error( 'wfcm_empty_admin_notice_id', __( 'No admin notice key specified for the request.', 'website-file-changes-monitor' ), array( 'status' => 404 ) );
		}

		$admin_notices = wfcm_get_setting( 'admin-notices', array() );

		if ( isset( $admin_notices[ $notice_key ] ) ) {
			// Unset the notice.
			unset( $admin_notices[ $notice_key ] );

			// Save notice option.
			wfcm_save_setting( 'admin-notices', $admin_notices );

			// Prepare response.
			$response = array( 'success' => true );
		} else {
			$response = array( 'success' => false );
		}

		return new WP_REST_Response( $response, 200 );
	}

	/**
	 * REST API callback for allowing events file or folder in the site root or WordPress core area.
	 *
	 * @param WP_REST_Request $rest_request - REST request object.
	 *
	 * @return WP_Error|WP_REST_Response
	 * @throws Exception
	 */
	public function allow_event_in_core( $rest_request ) {
		// Get event id from request.
		$event_id = $rest_request->get_param( 'event_id' );

		if ( ! $event_id ) {
			return new WP_Error( 'wfcm_empty_event_id', __( 'No event id specified for the request.', 'website-file-changes-monitor' ), array( 'status' => 404 ) );
		}

		// Get request body to check if event is excluded.
		$request_body = $rest_request->get_body();
		$request_body = json_decode( $request_body );
		$target_type  = isset( $request_body->targetType ) ? $request_body->targetType : false;

		//  update the lists of files and folders allowed in core as needed
		$this->updateSetOfFileAndDirOptions( $event_id, 'scan-allowed-in-core-files', 'scan-allowed-in-core-dirs', $target_type );

		//  read event data before deletion just in case we're processing a folder
		$event = wfcm_get_event( $event_id );

		//  delete the event after adding it to the allowed list
		$event_deleted = WFCM_REST_API::processEventDeletion( $event_id );

		$result = [
			'success' => $event_deleted
		];

		if ( true === $result['success'] && 'dir' === $target_type && $event instanceof WFCM_Event_File ) {
			//  add data necessary to show a popup about batch event deletion
			$dirpath           = dirname( $event->get_event_file_path() );
			$result['path']    = $dirpath;
			$result['title']   = esc_html__( 'Directory added to allowed list', 'website-file-changes-monitor' );
			$result['message'] = sprintf(
				                     esc_html__( 'You have added directory %s to the list of allowed directories in the WordPress core.', 'website-file-changes-monitor' ),
				                     $dirpath
			                     ) . ' ' . esc_html__( 'Do you want to delete the existing file changes notifications about files in this directory? If you do, the process will run in the background. Please revisit the file changes results later for an updated log.', 'website-file-changes-monitor' );
		}

		return $result;
	}

	/**
	 * Rest endpoint to delete all events within a specified folder.
	 *
	 * @param WP_REST_Request $rest_request - REST request object.
	 *
	 * @return WP_Error|WP_REST_Response
	 * @throws Exception
	 * @since  1.7.1
	 */
	public function delete_events_within_folder( $rest_request ) {
		//  check the path param presence
		$request_data = json_decode( $rest_request->get_body(), true );
		if ( ! array_key_exists( 'path', $request_data ) ) {
			return new WP_Error( 'wfcm_empty_path', __( 'No directory path for the request.', 'website-file-changes-monitor' ), array( 'status' => 404 ) );
		}

		//  clean the path
		$folder_path = stripslashes( $request_data['path'] );

		//  load all relevant events
		$events_to_delete = wfcm_get_events( [
			'nopaging'    => true,
			'starts_with' => $folder_path
		] );

		if ( ! empty( $events_to_delete ) ) {

			//  schedule the deletion in the background
			$deletion_process = new WFCM_Background_Event_Deletion();

			$batch_offset       = 0;
			$total_events_count = count( $events_to_delete );
			do {
				$batch_events = array_slice( $events_to_delete, $batch_offset, self::$batch_size );
				$event_ids    = array_map( function ( $event ) {
					return $event->get_event_id();
				}, $batch_events );

				$deletion_process->push_to_queue( $event_ids );
				$batch_offset += self::$batch_size;
			} while ( $batch_offset < $total_events_count );

			$deletion_process->save();
			$deletion_process->dispatch();
		}

		return array(
			'success' => true
		);
	}
}

new WFCM_REST_API();
