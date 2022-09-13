<?php
/**
 * WFCM Scan results email handler.
 *
 * @package wfcm
 * @since 1.7.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Scan results email handling class.
 */
class WFCM_Scan_Results_Email {

	private $scan_changes_count;

	/**
	 * @var int Limit for number of events of each type.
	 */
	private $limit;

	/**
	 * @var array Local cache of non basic events such as plugin or theme updates etc. Stored using event type as key.
	 * @since 1.7.0
	 */
	private $non_basic_events = [];

	/**
	 * @var int Total number of changes reported in an email.
	 * @since 1.7.1
	 */
	private $changes_found_count = 0;

	/**
	 * WFCM_Scan_Results_Email constructor.
	 *
	 * @param array $scan_changes_count Array of changes count.
	 */
	public function __construct( $scan_changes_count ) {
		$this->scan_changes_count = $scan_changes_count;
		$this->limit              = wfcm_get_setting( 'email-changes-limit', 10 );
	}

	/**
	 * Send file changes email.
	 *
	 * @param
	 *
	 * @return bool
	 * @throws Exception
	 */
	public function send() {

		$body = '';

		$body .= $this->get_summary_block_for_event_type(
			'added',
			esc_html__( 'File additions detected on your website:', 'website-file-changes-monitor' ),
			/* Translators: %d: Number of file additions */
			esc_html__( 'Total number of additions: %d', 'website-file-changes-monitor' ),
			/* Translators: 1: Number of displayed file changes, 2: Total number of file changes */
			esc_html__( 'Showing %1$d out of %2$d additions.', 'website-file-changes-monitor' ),
			/* Translators: %d: Hyperlink to the plugin page in WordPress administration */
			esc_html__( 'Click %s to see all the file additions.', 'website-file-changes-monitor' )
		);

		$body .= $this->get_summary_block_for_event_type(
			'modified',
			esc_html__( 'Files modifications detected on your website:', 'website-file-changes-monitor' ),
			/* Translators: %d: Number of file modifications */
			esc_html__( 'Total number of modifications: %d', 'website-file-changes-monitor' ),
			/* Translators: 1: Number of displayed file changes, 2: Total number of file changes */
			esc_html__( 'Showing %1$d out of %2$d modifications.', 'website-file-changes-monitor' ),
			/* Translators: %d: Hyperlink to the plugin page in WordPress administration */
			esc_html__( 'Click %s to see all the file modifications.', 'website-file-changes-monitor' )
		);

		$body .= $this->get_summary_block_for_event_type(
			'deleted',
			esc_html__( 'File deletions detected on your website:', 'website-file-changes-monitor' ),
			/* Translators: %d: Number of file deletions */
			esc_html__( 'Total number of deletions: %d', 'website-file-changes-monitor' ),
			/* Translators: 1: Number of displayed file changes, 2: Total number of file changes */
			esc_html__( 'Showing %1$d out of %2$d deletions.', 'website-file-changes-monitor' ),
			/* Translators: %d: Hyperlink to the plugin page in WordPress administration */
			esc_html__( 'Click %s to see all the file deletions.', 'website-file-changes-monitor' )
		);

		$summary_data = $this->get_non_basic_events_summary_data();
		if ( ! empty( $summary_data ) ) {
			$body .= '<h2>' . esc_html__( 'File changes due to a plugin / theme install / update / uninstall:', 'website-file-changes-monitor' ) . '</h2>';

			foreach ( $summary_data as $filename => $summary_data_item ) {
				$body .= '<p>';
				$body .= '<strong>' . esc_html__( 'Path', 'website-file-changes-monitor' ) . ':</strong> ' . $filename . '<br />';
				$body .= '<strong>' . esc_html__( 'Change type', 'website-file-changes-monitor' ) . ':</strong> ' . $summary_data_item['context'] . '<br />';
				$body .= '<strong>' . esc_html__( 'File changes summary', 'website-file-changes-monitor' ) . ':</strong> ';

				$changes_parts = [];
				foreach ( WFCM_REST_API::get_event_types() as $event_type ) {
					if ( array_key_exists( $event_type, $summary_data_item['changes'] ) ) {
						$count                     = $summary_data_item['changes'][ $event_type ];
						$this->changes_found_count += $count;
						$changes_parts[]           = sprintf(
							_nx(
								'%d file %s',
								'%d files %s',
								$count,
								'Number of file changes of certain type',
								'website-file-changes-monitor'
							),
							$count,
							$this->translate_event_type( $event_type )
						);
					}
				}

				$body .= implode( ', ', $changes_parts ) . '.';
				$body .= '</p>';
			}
		}

		//  check if there are any changes to report
		if ( 0 === $this->changes_found_count && 'no' === wfcm_get_setting( 'empty-email-allowed', 'no' ) ) {
			//  don't send an email if there are no changes and an empty notification email is disabled
			return false;
		}
		
		if ( 'no' === wfcm_get_setting( 'send-email-upon-changes', 'yes' ) ) {
			//  don't send an email unless the option to do so in enabled.
			return false;
		}

		$body .= '<p>' . __( 'Visit the File Monitor in the WordPress dashboard to check the file changes.', 'website-file-changes-monitor' ) . '</p>';

		/* Translators: %s: Plugin WP Hyperlink */
		$body .= '<p>' . sprintf( __( 'This file integrity scan was done with the %s.', 'website-file-changes-monitor' ), '<a href="https://wordpress.org/plugins/website-file-changes-monitor/" target="_blank">' . __( 'Website File Changes Monitor plugin', 'website-file-changes-monitor' ) . '</a>' ) . '</p>';

		$home_url        = home_url();
		$safe_url        = str_replace( array( 'http://', 'https://' ), '', $home_url );
		$datetime_format = wfcm_get_datetime_format();
		$date_time       = str_replace(
			'$$$',
			substr( number_format( fmod( current_time( 'timestamp' ), 1 ), 3 ), 2 ),
			date( $datetime_format, current_time( 'timestamp' ) )
		);

		//	sum up the number of changes in the current scan run
		$number_of_changes_in_current_scan = 0;
		foreach ( $this->scan_changes_count as $type_changes_count ) {
			$number_of_changes_in_current_scan += $type_changes_count;
		}

		//	determine email intro message depending on number of changes in the current scan
		$email_intro = $number_of_changes_in_current_scan ?
			sprintf(
			/* Translators: Date and time */
				__( 'The below is a list of current file changes detected by the Website File Changes Monitor plugin. These include changes found in your most recent scan on %s.', 'website-file-changes-monitor' ),
				$date_time
			) :
			sprintf(
			/* Translators: Date and time */
				__( 'There were no file changes detected during the last file integrity scan that ran on %s.', 'website-file-changes-monitor' ),
				$date_time
			);


		/* Translators: %s: Home URL */
		$subject = sprintf( __( 'File changes from file integrity scan on %s', 'website-file-changes-monitor' ), $safe_url );

		$body = '<p>' . $email_intro . '</p>' . $body;

		// get the settings.
		$email_notice_type = wfcm_get_setting( WFCM_Settings::NOTIFY_TYPE, 'admin' );
		$email_custom_list = wfcm_get_setting( WFCM_Settings::NOTIFY_ADDRESSES, array() );

		// convert TO an array from a string.
		$email_custom_list = ( ! is_array( $email_custom_list ) ) ? explode( ',', $email_custom_list ) : $email_custom_list;

		/*
		 * Decide where to send email notifications. This uses a custom list of
		 * 1 or more addresses and falls back to admin address if a custom list
		 * is not used.
		 */
		if ( 'custom' === $email_notice_type && ! empty( $email_custom_list ) ) {
			// we have a custom list to use.
			foreach ( $email_custom_list as $email_address ) {
				if ( filter_var( $email_address, FILTER_VALIDATE_EMAIL ) ) {
					WFCM_Email::send( $email_address, $subject, $body );
				}
			}
		} else {
			// sending to admin address.
			WFCM_Email::send( get_bloginfo( 'admin_email' ), $subject, $body );
		}

		return $number_of_changes_in_current_scan > 0;
	}

	/**
	 * Builds summary block HTML for basic file events for given event type.
	 *
	 * @param string $event_type Event type.
	 * @param string $title Section title (translated).
	 * @param string $total_message_simple Translated message showing total number of events if they don't exceed a limit.
	 * @param string $total_message_partial Translated message showing what portion of total events is displayed.
	 * @param string $admin_link_message Translated message containing a link to admin page showing the changes of given type.
	 *
	 * @return string
	 * @throws Exception
	 * @since 1.7.0
	 */
	private function get_summary_block_for_event_type( $event_type, $title, $total_message_simple, $total_message_partial, $admin_link_message ) {
		$result = '';

		$last_scan_start = wfcm_get_setting( 'last-scan-start' );

		$args = [];
		$args[ 'event_type' ]            = $event_type;
		$args[ 'paged' ]                 = 1;
		$args[ 'posts_per_page' ]        = $this->limit;
		$args[ 'retrieve_events_since' ] = $last_scan_start;
		$events = WFCM_Database_DB_Data_Store::get_file_events( $args, true );
		$events = $events->get_events();

		if ( ! empty( $events ) ) {
			$result .= '<h2>' . $title . '</h2>';

			$result .= '<ul>';
			foreach ( $events as $event ) {
				$result .= '<li>' . addslashes( $event->get_event_file_path() ) . '</li>';
			}
			$result .= '</ul>';

			$event_count               = WFCM_Database_DB_Data_Store::get_total_events_count( $event_type, true );
			$this->changes_found_count += $event_count;
			if ( $event_count <= $this->limit ) {
				$total_count_label = sprintf( $total_message_simple, $event_count );
			} else {
				$admin_link        = WFCM_Admin_File_Changes::get_tab_url( $event_type );
				$total_count_label = sprintf( $total_message_partial, $this->limit, $event_count );
				$total_count_label .= ' ' . sprintf(
						$admin_link_message,
						'<a href="' . $admin_link . '">' . esc_html__( 'here', 'website-file-changes-monitor' ) . '</a>'
					);
			}

			$result .= '<p>' . $total_count_label . '</p>';
		}

		return $result;
	}

	/**
	 * Retrieves unread events of a certain event type with no event context (this is the way to exclude any plugin
	 * updates or changes spotted during WordPress.org checksum validation.
	 *
	 * @param string $event_type
	 *
	 * @return WFCM_Event[]
	 *
	 * @since 1.7.0
	 */
	private function get_events_by_event_type( $event_type ) {
		return WFCM_Database_DB_Data_Store::get_file_events( array_merge(
			$this->get_events_base_query_args( $event_type, true ), [
				'posts_per_page' => $this->limit
			],			
		),
		false );
	}

	/**
	 * @param string $event_type
	 * @param bool $basic_only
	 *
	 * @return array
	 * @since 1.7.0
	 */
	private function get_events_base_query_args( $event_type, $basic_only ) {
		return array(
			'event_type' => $event_type,
		);
	}

	/**
	 * Calculates the number of basic file changes for given event type.
	 *
	 * It also caches the list of non-basic events locally in the process to speed things up.
	 *
	 * @param string $event_type Event type.
	 *
	 * @return int
	 * @throws Exception
	 * @since 1.7.0
	 */
	private function get_basic_events_count_by_event_type( $event_type, $basic_only = true ) {

		//  get a number of non basic events such as plugin or theme updates etc.
		$args = array_merge(
			$this->get_events_base_query_args( $event_type, false ),
			[
				'nopaging' => true,
				'retrieve_events_since' => wfcm_get_setting( 'last-scan-start' )
			]
		);
		
		$non_basic_events =  WFCM_Database_DB_Data_Store::get_file_events( $args, $basic_only );

		$this->non_basic_events[ $event_type ] = $non_basic_events->events;

		$all_events_count = WFCM_Database_DB_Data_Store::get_total_events_count( $event_type );

		return count( $this->non_basic_events[ $event_type ] );
	}

	/**
	 * Builds data about each non basic file change such as plugin install, update etc. in format suitable for email generation.
	 *
	 * {path} => {
	 *  {context},
	 *  {changes} => {
	 *      {added}, {modified}, {deleted}
	 *  }
	 * }
	 *
	 * @return array
	 * @throws Exception
	 * @since 1.7.0
	 */
	private function get_non_basic_events_summary_data() {
		$result = [];

		//  getting the count for each event types causes the local non-basic events cache to be populated
		foreach ( WFCM_REST_API::get_event_types() as $event_type ) {
			self::get_basic_events_count_by_event_type( $event_type, false );

			if ( ! empty( $this->non_basic_events[ $event_type ] ) ) {
				/** @var WFCM_Event $event */
				foreach ( $this->non_basic_events[ $event_type ] as $event ) {
					$filename = $event->get_event_file_path();
					if ( ! array_key_exists( $filename, $result ) ) {
						$result[ $filename ] = [
							'context' => $event->get_event_context(),
							'changes' => []
						];
					}

					$content = (array) maybe_unserialize( $event->get_event_content() );

					$result[ $filename ]['changes'][ $event_type ] = count( $content );
				}
			}
		}

		return $result;
	}

	/**
	 * Translates an event type string for the UI.
	 *
	 * @param string $event_type Translated event type. Empty if invalid event type is used.
	 *
	 * @return string
	 * @since 1.7.0
	 */
	private function translate_event_type( $event_type ) {
		switch ( $event_type ) {
			case 'added':
				return __( 'added', 'website-file-changes-monitor' );
			case 'modified':
				return __( 'modified', 'website-file-changes-monitor' );
			case 'deleted':
				return __( 'deleted', 'website-file-changes-monitor' );
			default:
				return '';
		}
	}

}
