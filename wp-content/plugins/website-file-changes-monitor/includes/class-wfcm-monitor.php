<?php
/**
 * File Changes Monitor.
 *
 * @package wfcm
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * File Changes Monitor Class.
 *
 * This class is responsible for monitoring
 * the file changes on the server.
 */
class WFCM_Monitor {

	/**
	 * @var int Number of high level directories that are available for scanning.
	 */
	const SCAN_DIRS_COUNT = 5;

	/**
	 * Sensor Instance.
	 *
	 * @var WFCM_Monitor
	 */
	protected static $instance = null;

	/**
	 * WP Root Path.
	 *
	 * @var string
	 */
	public $root_path = '';

	/**
	 * Paths to exclude during scan.
	 *
	 * @var array
	 */
	private $excludes = array();

	/**
	 * View settings.
	 *
	 * @var array
	 */
	public $scan_settings = array();

	/**
	 * Frequency daily hour.
	 *
	 * For testing change hour here [01 to 23]
	 *
	 * @var array
	 */
	private static $daily_hour = array( '04' );

	/**
	 * Frequency weekly date.
	 *
	 * For testing change date here [1 (for Monday) through 7 (for Sunday)]
	 *
	 * @var string
	 */
	private static $weekly_day = '1';

	/**
	 * Schedule hook name.
	 *
	 * @var string
	 */
	public static $schedule_hook = 'wfcm_monitor_file_changes';

	/**
	 * Scan files counter during a scan.
	 *
	 * @var int
	 */
	private $scan_file_count = 0;

	/**
	 * Scan files limit reached.
	 *
	 * @var bool
	 */
	private $scan_limit_file = false;

	/**
	 * Stored files to exclude.
	 *
	 * @var array
	 */
	private $files_to_exclude = array();

	/**
	 * WP uploads directory.
	 *
	 * @var array
	 */
	private $uploads_dir = array();

	/**
	 * Scan changes count.
	 *
	 * @var array
	 */
	private $scan_changes_count = array();

	/**
	 * Keep track of this scan run time so we can break early before a timeout.
	 *
	 * @var int
	 */
	private $scan_start_time = 0;

	/**
	 * Used to hold the max length we are willing to run a scan part for in
	 * seconds.
	 *
	 * This will be set to 4 minutes is there is no time saved in database.
	 *
	 * @var int
	 */
	private $scan_max_execution_time;

	/**
	 * Class constants.
	 */
	const SCAN_HOURLY       = 'hourly';
	const SCAN_DAILY        = 'daily';
	const SCAN_WEEKLY       = 'weekly';
	const SCAN_FILE_LIMIT   = 200000;
	const HASHING_ALGORITHM = 'sha256';

	/**
	 * Return WFCM_Monitor Instance.
	 *
	 * Ensures only one instance of monitor is loaded or can be loaded.
	 *
	 * @return WFCM_Monitor
	 */
	public static function get_instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->root_path = self::get_root_path();
		$this->register_hooks();
		$this->load_settings();
		$this->schedule_file_changes_monitor();

		// try get a max scan length from database otherwise default to 4 mins.
		// NOTE: this code could be adjusted to allow user configuration.
		$this->scan_max_execution_time = (int) get_option( 'wfcm_max_scan_time', 4 * MINUTE_IN_SECONDS );
	}

	/**
	 * To be used outside of this class (apart from the constructor) to determine the absolute path to the site root.
	 *
	 * This class should use local variable $root_path that is set during object instantiation.
	 *
	 * @return string Absolute site root path.
	 * @since 1.7.0
	 */
	public static function get_root_path() {
		return trailingslashit( ABSPATH );
	}

	/**
	 * Register Hooks.
	 */
	public function register_hooks() {
		add_filter( 'cron_schedules', array( $this, 'add_recurring_schedules' ) ); // phpcs:ignore
		add_filter( 'wfcm_file_scan_stored_files', array( $this, 'filter_scan_files' ), 10, 2 );
		add_filter( 'wfcm_file_scan_scanned_files', array( $this, 'filter_scan_files' ), 10, 2 );
		add_action( 'wfcm_after_file_scan', array( $this, 'empty_skip_file_alerts' ), 10, 1 );
		add_action( 'wfcm_last_scanned_directory', array( $this, 'reset_core_updates_flag' ), 10, 1 );

		add_action( 'wfcm_bg_scanning_complete', array( $this, 'bg_scanning_complete' ), 10, 1 );
		add_action( 'wfcm_scanner_check_changes_complete', array( $this, 'check_changes_complete' ), 10, 1 );

	}

	/**
	 * Function which runs at the end of the file scanning process.
	 *
	 * @param string $path_to_scan - most recently completed path.
	 * @return void
	 */
	public function bg_scanning_complete( $path_to_scan ) {
		// Check if we have hit a file limit.
		if ( $this->scan_limit_file ) {
			$admin_notices = wfcm_get_setting( 'admin-notices', array() );
			if ( ! isset( $admin_notices['files-limit'] ) || ! is_array( $admin_notices['files-limit'] ) ) {
				$admin_notices['files-limit'] = array();
			}
			if ( ! in_array( $path_to_scan, $admin_notices['files-limit'], true ) ) {
				array_push( $admin_notices['files-limit'], $path_to_scan );
			}
			wfcm_save_setting( 'admin-notices', $admin_notices );

			// Trigger WSAL Event 6032.
			do_action( 'wfcm_wsal_file_limit_exceeded', $path_to_scan );
		}
	}

	/**
	 * Checks if the process of gathering and comparing the scanned/stored files has completed for all the directories needed.
	 *
	 * @param string $directory
	 * @return void
	 */
	public function check_changes_complete( $directory ) {

		// Get currently scanned dirs, and dirs we want to scan for comparison.
		$currently_scanned = wfcm_get_setting( 'scanned_directories' );
		$directories       = $this->scan_settings['directories'];

		if ( $this->scan_settings['debug-logging'] ) {
			$msg = wfcm_get_log_timestamp() . ' WFCM is firing check_changes_complete for the directory: '. $directory ." \n";
			$msg .= 'Currently scanned: '. implode( ', ', $currently_scanned ) ." \n";
			$msg .= 'All dirs that need to be scanned: '. implode( ', ', $directories ) ." \n";
			wfcm_write_to_log( $msg );
		}

		// If we have not finished scanning everything, we are not quite ready yet.
		if ( $currently_scanned !== $directories ) {
			return;
		}

		if ( $this->scan_settings['debug-logging'] ) {
			$msg = wfcm_get_log_timestamp() . ' check_changes_complete is able to run, all dirs are scanned. '. $directory ." \n";
			wfcm_write_to_log( $msg );
		}

		// Update stored files based on results of latest scan.
		foreach ( $directories as $directory ) {
			$tidy_name = ( 'root' === $directory ) ? 'wfcm_root' : $this->create_tidy_name( $directory, true );

			// Determine all of the relevent option names which hold data applicable to this directory.
			global $wpdb;
			$option_name       = $tidy_name . '_comparison' . '#_%';
			$options_to_update = $wpdb->get_results(
				"SELECT option_name FROM $wpdb->options
				WHERE option_name LIKE '$option_name' ESCAPE '#'",
				ARRAY_A
			);

			$options = array_map( function ( $option ) {
				return $option['option_name'];
			}, $options_to_update );

			// Now we know all of the options, lets process.
			foreach ( $options as $option ) {
				$master_setting_name = str_replace( '_comparison', '', $option );
				$freshest_files = get_site_option( $option );
				
				// If we have fresher data than we once did, lets update what we have stored.
				if ( ! empty( $freshest_files ) ) {

					if ( $this->scan_settings['debug-logging'] ) {
						$msg = wfcm_get_log_timestamp() . ' Updating '. $master_setting_name . ' with current list of files.'." \n";
						$msg .= wfcm_get_log_timestamp() . ' Deleting '. $option . ' ready for next scan.'." \n";
						wfcm_write_to_log( $msg );
					}

					update_option( $master_setting_name, $freshest_files, false );
					// Now we have no use for the comparison data, so lets clean it ready for the next run.
					delete_site_option( $option );
				}
			}
		}

		// save the last scan timestamp to display frontend.
		wfcm_save_setting( 'last-scan-timestamp', time() );

		// Trigger WSAL Event 6033.
		do_action( 'wfcm_wsal_file_scan_stopped' );

		// Send email notification.
		$changes = wfcm_send_changes_email( $this->scan_changes_count );

		// If mail should have been sent log the time when WFCM sends the scan email.
		if ( $changes && $this->scan_settings['debug-logging'] ) {
			$msg = wfcm_get_log_timestamp() . ' ' . __( 'WFCM sent an email', 'website-file-changes-monitor' ) . " \n";
			wfcm_write_to_log( $msg );
		}

		// Delete changes count for this scan.
		$this->scan_changes_count( 'delete' );

		// Get admin notices.
		$admin_notices = wfcm_get_setting( 'admin-notices', array() );

		if ( $this->scan_changes_count > 0 ) {
			$admin_notices['empty-scan'] = false; // Set scan empty notice to false because there are file changes in the latest scan.
		} else {
			$admin_notices['empty-scan'] = true; // Set scan empty notice to true because there are no file changes in the latest scan.
		}

		// Save admin notices.
		wfcm_save_setting( 'admin-notices', $admin_notices );

		/**
		 * `Action`: Last scanned directory.
		 */
		do_action( 'wfcm_last_scanned_directory', $changes );
	}

	/**
	 * Load File Change Monitor Settings.
	 */
	public function load_settings() {
		$this->scan_settings = wfcm_get_monitor_settings();

		// Set the scan hours.
		if ( ! empty( $this->scan_settings['hour'] ) ) {
			$saved_hour = (int) $this->scan_settings['hour'];
			$next_hour  = $saved_hour + 1;
			$hours      = array( $saved_hour, $next_hour );
			foreach ( $hours as $hour ) {
				$daily_hour[] = str_pad( $hour, 2, '0', STR_PAD_LEFT );
			}
			self::$daily_hour = $daily_hour;
		}

		// Set weekly day.
		if ( ! empty( $this->scan_settings['day'] ) ) {
			self::$weekly_day = $this->scan_settings['day'];
		}
	}

	/**
	 * Schedule file changes monitor cron.
	 */
	public function schedule_file_changes_monitor() {
		// Schedule file changes if the feature is enabled.
		if ( is_multisite() && ! is_main_site() ) {
			// Clear the scheduled hook if feature is disabled.
			wp_clear_scheduled_hook( self::$schedule_hook );
		} elseif ( 'yes' === $this->scan_settings['enabled'] ) {
			// Hook scheduled method.
			add_action( self::$schedule_hook, array( $this, 'scan_file_changes' ) );
			// Schedule event if there isn't any already.
			if ( ! wp_next_scheduled( self::$schedule_hook ) ) {
				$frequency_option = wfcm_get_setting( 'scan-frequency', 'daily' );
				// figure out the NEXT schedule time to recur from.
				$time = $this->get_next_cron_schedule_time( $frequency_option );
				wp_schedule_event(
					$time,               // Timestamp.
					$frequency_option,   // Frequency.
					self::$schedule_hook // Scheduled event.
				);
			}
		} else {
			// Clear the scheduled hook if feature is disabled.
			wp_clear_scheduled_hook( self::$schedule_hook );
		}
	}

	/**
	 * Given a frequency formulates the next time that occurs and returns a
	 * timestamp for that time to use when scheduling initial cron jobs.
	 *
	 * @method get_next_cron_schedule_time
	 * @since  1.5.0
	 * @param  string $frequency_option an option of hourly/daily/weekly.
	 * @return int
	 */
	private function get_next_cron_schedule_time( $frequency_option ) {
		$time = current_time( 'timestamp' );

		// Allow for local timezones.
		$local_timezone = wp_timezone_string();

		switch ( $frequency_option ) {
			case self::SCAN_HOURLY:
				// hourly scans start at the beginning of the next hour.
				$date = new DateTime();

				// Adjust for timezone.
				$date->setTimezone( wp_timezone() );

				$minutes = $date->format( 'i' );

				$date->modify( '+1 hour' );
				// if we had any minutes then remove them.
				if ( $minutes > 0 ) {
					$date->modify( '-' . $minutes . ' minutes' );
				}

				$time = $date->getTimestamp();
				break;
			case self::SCAN_DAILY:
				// daily starts on a given hour of the first day it occurs.
				$hour      = (int) wfcm_get_setting( 'scan-hour' );
				$next_time = strtotime( 'today ' . $hour . ':00 ' . $local_timezone );

				// if already passed today then add 1 day to timestamp.
				if ( $next_time < $time ) {
					$next_time = strtotime( '+1 day', $next_time );
				}

				$time = $next_time;
				break;
			case self::SCAN_WEEKLY:
				// weekly runs on a given day each week at a given hour.
				$hour      = (int) wfcm_get_setting( 'scan-hour' );
				$day_num   = (int) wfcm_get_setting( 'scan-day' );
				$day       = $this->convert_to_day_string( $day_num );

				$next_time = strtotime( $day . ' ' . $hour . ':00 ' . ' ' . $local_timezone );
				// if that day has passed this week already then add 1 week.
				if ( $next_time < $time ) {
					$next_time = strtotime( '+1 week', $next_time );
				}

				$time = $next_time;
				break;
			default:
				//  no other scan frequencies supported
		}
		return ( false === $time ) ? time() : $time;
	}

	/**
	 * Converts a number reporesenting a day of the week into a string for it.
	 *
	 * NOTE: 1 = Monday, 7 = Sunday but is zero corrected by subtracting 1.
	 *
	 * @method convert_to_day_string
	 * @since  1.5.0
	 * @param  int $day_num a day number.
	 * @return string
	 */
	private function convert_to_day_string( $day_num ) {
		// Scan days option.
		$day_key   = (int) $day_num - 1;
		$scan_days = array(
			'Monday',
			'Tuesday',
			'Wednesday',
			'Thursday',
			'Friday',
			'Saturday',
			'Sunday',
		);
		// Return a day string - uses day 1 = Monday by default.
		return ( isset( $scan_days[ $day_key ] ) ) ? $scan_days[ $day_key ] : $scan_days[1];
	}

	/**
	 * Add time intervals for scheduling.
	 *
	 * @param  array $schedules - Array of schedules.
	 * @return array
	 */
	public function add_recurring_schedules( $schedules ) {
		$schedules['tenminutes'] = array(
			'interval' => 600,
			'display'  => __( 'Every 10 minutes', 'website-file-changes-monitor' ),
		);
		$schedules['weekly']     = array(
			'interval' => 7 * DAY_IN_SECONDS,
			'display'  => __( 'Once a week', 'website-file-changes-monitor' ),
		);
		return $schedules;
	}

	/**
	 * Scan File Changes.
	 */
	public function scan_file_changes() {

		// has done the migration to sha256?
		if ( 'no' === wfcm_get_setting( 'is-initial-scan-0', 'yes' ) && ! wfcm_get_setting( 'sha256-hashing', false ) ) {
			// user not migrated to sha256 yet.

			// We want to use sha256 for hashing but it may not be available on all
			// systems - some php 5.6 may not have it.
			$admin_notices = wfcm_get_setting( 'admin-notices', array() ); // Get admin notices.

			// Add a notice telling user they need to upgrade hashing for their
			// files.
			if ( ! isset( $admin_notices ) || ! is_array( $admin_notices ) ) {
				$admin_notices = array();
			}
			$admin_notices['hashing-upgrade']['upgrade-needed'] = true;
			// save notice.
			wfcm_save_setting( 'admin-notices', $admin_notices );
			// Returns early so we DO NOT hash with old hashes.
			return;
		}

		// We want to use sha256 for hashing but it may not be available on all
		// systems - some php 5.6 may not have it.
		$admin_notices = wfcm_get_setting( 'admin-notices', array() ); // Get admin notices.
		if ( ! in_array( self::HASHING_ALGORITHM, hash_algos(), true ) ) {
			// Add a notice informing user they do not have the necessary hash
			// algorithm on their site.
			if ( ! isset( $admin_notices ) || ! is_array( $admin_notices ) ) {
				$admin_notices = array();
			}
			$admin_notices['hashing-algorith']['sha256-unavailable'] = true;
			// save notice and return early.
			wfcm_save_setting( 'admin-notices', $admin_notices );
			// DO NOT hash with old algorithm.
			return;
		} elseif ( isset( $admin_notices['hashing-algorith'] ) ) {
			unset( $admin_notices['hashing-algortith'] );
			wfcm_save_setting( 'admin-notices', $admin_notices );
		}

		// Check if a scan is already in progress. Bail early if it is - there
		// should never be 2 scans running at the same time.
		if ( wfcm_get_setting( 'scan-in-progress', false ) ) {
			return;
		}

		// check if the previous scan left any 'last-scanned' around.
		$last_scanned_option = wfcm_get_setting( 'last-scanned', false );

		if ( false !== $last_scanned_option && ! empty( $last_scanned_option ) ) {
			// previous scan never completed for some reason...
			$admin_notices = wfcm_get_setting( 'admin-notices', array() );
		}

		// Cancel any current scans.
		$this->cancel_bg_processes();

		wfcm_save_setting( 'scanned_directories', array() );

		// Ensure we have the site content setup.
		$site_content = wfcm_get_setting( WFCM_Settings::$site_content );
		if ( empty( $site_content ) ) {
			wfcm_set_site_content();
		}

		// save the last start timestamp.
		wfcm_save_setting( 'last-scan-start', time() );

		// Trigger WSAL Event 6033.
		do_action( 'wfcm_wsal_file_scan_started' );

		// Get directories to be scanned.
		$directories = $this->scan_settings['directories'];

		foreach ( $directories as $directory ) {
			$bg_process = new WFCM_Background_Scanner();
			$bg_process->push_to_queue( $directory );
			$bg_process->save()->dispatch();
		}

		// We have completed a scan, so lets clear this failure notice.
		$admin_notices['previous-scan-fail-generic'] = false;

		// Save admin notices.
		wfcm_save_setting( 'admin-notices', $admin_notices );

	}

	/**
	 * Given lists of files this arranges them into different arrays and cretes
	 * the posts for each event of the given types.
	 *
	 * @method compute_differences_and_create_change_events
	 *
	 * @param array $filtered_stored_files [description]
	 * @param array $filtered_scanned_files [description]
	 * @param WFCM_Hash_Comparator_Interface $hash_comparator Hash comparator.
	 * @param $path_to_scan
	 * @param string $origin Checksum origin. Could be local, wp.org or possibly another source in the future.
	 *
	 * @throws Exception
	 * @since 1.7.0 Added hash comparator parameter to make hash comparison more flexible. Also added origin parameter.
	 * @since 1.5.0
	 */
	private function compute_differences_and_create_change_events( $filtered_stored_files, $filtered_scanned_files, $hash_comparator, $path_to_scan, $origin ) {
		// Compare the results to find out about file added and removed.
		$files_added   = array_diff_key( $filtered_scanned_files, $filtered_stored_files );
		$files_removed = array_diff_key( $filtered_stored_files, $filtered_scanned_files );

		$this->scan_changes_count();

		/**
		 * File changes.
		 *
		 * To scan the files with changes, we need to
		 *
		 *  1. Remove the newly added files from scanned files – no need to add them to changed files array.
		 *  2. Remove the deleted files from already logged files – no need to compare them since they are removed.
		 *  3. Then start scanning for differences – check the difference in hash.
		 */
		$scanned_files_minus_added  = array_diff_key( $filtered_scanned_files, $files_added );
		$stored_files_minus_deleted = array_diff_key( $filtered_stored_files, $files_removed );

		$folders_to_allow_in_core = [];
		$files_to_allow_in_core = [];

		$origin = ( empty( $path_to_scan ) && $hash_comparator instanceof WFCM_WordPressOrg_Hash_Comparator ) ? 'wp.org' : 'local';
		$wp_core_scan_in_progress = 'wp.org' === $origin && '' === $path_to_scan;

		if ( $wp_core_scan_in_progress ) {
			//  we load the list of allowed files and folders here because we will use it further down
			$files_to_allow_in_core = wfcm_get_setting('scan-allowed-in-core-files');
			$folders_to_allow_in_core = wfcm_get_setting('scan-allowed-in-core-dirs');
		}

		// Changed files array.
		$files_changed = array();

		// Go through each newly scanned file.
		foreach ( $scanned_files_minus_added as $file => $file_hash ) {
			// Check if it exists in already stored array of files, ignore if the key does not exists.
			if ( array_key_exists( $file, $stored_files_minus_deleted ) ) {
				// If key exists, then check if the file hash is set and compare it to already stored hash.
				if (
					! empty( $file_hash ) && ! empty( $stored_files_minus_deleted[ $file ] )
					&& ! $hash_comparator->compare( $file, $stored_files_minus_deleted[ $file ], $file, $file_hash )
				) {
					// If the file hashes don't match then store the file in changed files array.
					$files_changed[ $file ] = $file_hash;
				}
			}
		}

		// Files added alert.
		if ( in_array( 'added', $this->scan_settings['type'], true ) && count( $files_added ) > 0 ) {
			// Get excluded site content.
			$site_content = wfcm_get_setting( WFCM_Settings::$site_content );

			// Log the alert.
			$folders_to_skip = property_exists($site_content, 'skip_dirs') ? $site_content->skip_dirs : [];
			foreach ( $files_added as $file => $file_hash ) {
				// Get directory name.
				$directory_name = dirname( $file );

				// Check if the directory is in excluded directories list.
				if ( $this->is_dir_part_of_dir_list( $directory_name, $folders_to_skip ) ) {
					continue;
				}

				if ( $wp_core_scan_in_progress && $this->is_dir_part_of_dir_list( $directory_name, $folders_to_allow_in_core ) ) {
					//  skip file because the folder it lives in allowed in site root or WP core and we don't want to log an added event
					continue;
				}

				// Get filename from file path.
				$filename = basename( $file );

				// Check if the filename is in excluded files list.
				if ( ! empty( $site_content->skip_files ) && in_array( $filename, $site_content->skip_files, true ) ) {
					continue; // If true, then skip the loop.
				}

				if ( $wp_core_scan_in_progress && ! empty ( $files_to_allow_in_core ) && in_array( $filename, $files_to_allow_in_core, true ) ) {
					//  skip file because it is allowed in site root or WP core and we don't want to log an added event
					continue;
				}

				// Check for allowed extensions.
				if ( in_array( pathinfo( $filename, PATHINFO_EXTENSION ), $this->scan_settings['exclude-exts'], true ) ) {
					continue; // If true, then skip the loop.
				}

				$created = $this->create_file_event_if_allowed( 'added', $file, $file_hash, $origin );
				$current_scan_changes_count = ( isset( $this->scan_changes_count['files_added'] ) ) ? $this->scan_changes_count['files_added'] : 0;
				if ( $created ) {
					$this->scan_changes_count['files_added'] = $current_scan_changes_count++;
				}
			}
		}

		// Files removed alert.
		if ( in_array( 'deleted', $this->scan_settings['type'], true ) && count( $files_removed ) > 0 ) {
			
			// Log the alert.
			foreach ( $files_removed as $file => $file_hash ) {
				// Get directory name.
				$directory_name = dirname( $file );

				// Check if directory is in excluded directories list.
				if ( $this->is_dir_part_of_dir_list( $directory_name, $this->scan_settings['exclude-dirs'] ) ) {
					continue;
				}

				if ( $wp_core_scan_in_progress && $this->is_dir_part_of_dir_list( $directory_name, $folders_to_allow_in_core ) ) {
					//  skip file because the folder it lives in is allowed in site root or WP core and we don't want to log a deleted event
					continue;
				}

				// Get filename from file path.
				$filename = basename( $file );

				// Check if the filename is in excluded files list.
				if ( in_array( $filename, $this->scan_settings['exclude-files'], true ) ) {
					continue; // If true, then skip the loop.
				}

				if ( $wp_core_scan_in_progress && ! empty ( $files_to_allow_in_core ) && in_array( $filename, $files_to_allow_in_core, true ) ) {
					//  skip file because it is allowed in site root or WP core and we don't want to log a deleted event
					continue;
				}

				// Check for allowed extensions.
				if ( in_array( pathinfo( $filename, PATHINFO_EXTENSION ), $this->scan_settings['exclude-exts'], true ) ) {
					continue; // If true, then skip the loop.
				}

				$created = $this->create_file_event_if_allowed( 'deleted', $file, $file_hash, $origin );				
				$current_scan_changes_count = ( isset( $this->scan_changes_count['files_deleted'] ) ) ? $this->scan_changes_count['files_deleted'] : 0;
				if ($created) {
					$this->scan_changes_count['files_deleted'] = $current_scan_changes_count++;
				}
			}
		}

		// Files edited alert.
		if ( ! empty( $files_changed ) && in_array( 'modified', $this->scan_settings['type'], true ) ) {
			foreach ( $files_changed as $file => $file_hash ) {
				$created = $this->create_file_event_if_allowed( 'modified', $file, $file_hash, $origin );
				$current_scan_changes_count = ( isset( $this->scan_changes_count['files_modified'] ) ) ? $this->scan_changes_count['files_modified'] : 0;
				if ($created) {
					$this->scan_changes_count['files_modified'] = $current_scan_changes_count++;
				}
			}
		}
	}

	/**
	 * @param string $event_type
	 * @param string $file
	 * @param string $file_hash
	 * @param string $origin
	 *
	 * @return bool True if the event was created. False otherwise.
	 * @throws Exception
	 */
	private function create_file_event_if_allowed( $event_type, $file, $file_hash, $origin ) {

		$create_event = wfcm_create_event( $event_type, $file, $file_hash, $origin );

		if ( $create_event ) {
			// Log the added files.
			if ( $this->scan_settings['debug-logging'] ) {
				$msg = wfcm_get_log_timestamp() . ' ';
				if ( 'added' == $event_type ) {
					$msg .= __( 'Added file:', 'website-file-changes-monitor' );
				} else if ( 'deleted' == $event_type ) {
					$msg .= __( 'Deleted file:', 'website-file-changes-monitor' );
				} else if ( 'modified' == $event_type ) {
					$msg .= __( 'Modified file:', 'website-file-changes-monitor' );
				}

				$msg .= " {$file}\n";
				wfcm_write_to_log( $msg );
			}

			return true;
		}

		return false;
	}

	/**
	 * Starts the counter for our runtime for breaking early and increase php
	 * max_execution_time as well to account for long scans.
	 *
	 * @method start_tracking_php_runtime
	 * @since  1.5.0
	 */
	private function start_tracking_php_runtime() {

		// hold the scan start time so we can bail before max execution time.
		$this->scan_start_time = ( 0 !== $this->scan_start_time ) ? $this->scan_start_time : time();

		/**
		 * Try increase the php max execution time.
		 */
		set_time_limit( $this->scan_max_execution_time );
		$current_max = ini_get( 'max_execution_time' );
		if ( (int) $current_max !== (int) $this->scan_max_execution_time ) {
			// note: when xDebug is watching max_execution_time from ini_get is always string "0" causing this to always fire in develop.
			if ( $this->scan_settings['debug-logging'] ) {
				$msg  = wfcm_get_log_timestamp() . ' ';
				$msg .= __( 'Unable to increase max execution time, PHP safe_mode may be enabled.', 'website-file-changes-monitor' );
				$msg .= "\n";
				wfcm_write_to_log( $msg );
			}
		}

	}

	/**
	 * Check scan frequency.
	 *
	 * Scan start checks:
	 *   1. Check frequency is not empty.
	 *   2. Check if there is any directory left to scan.
	 *     2a. If there is a directory left, then proceed to check frequency.
	 *     2b. Else check if 24 hrs limit is passed or not.
	 *   3. Check frequency of the scan set by user and decide to start the scan or not.
	 *
	 * @param string $frequency - Frequency of the scan.
	 * @return bool True if scan is a go, false if not.
	 */
	public function check_start_scan( $frequency ) {
		// If empty then return false.
		if ( empty( $frequency ) ) {
			return false;
		}

		/**
		 * When there are no directories left to scan then:
		 *
		 * 1. Get the last scan start time.
		 * 2. Check for 24 hrs limit.
		 * 3a. If the limit has passed then remove options related to last scan.
		 * 3b. Else return false.
		 */
		if ( ! $this->dir_left_to_scan( $this->scan_settings['directories'] ) ) {
			// Get last scan time.
			$last_scan_start = wfcm_get_setting( 'last-scan-start', false );

			if ( ! empty( $last_scan_start ) ) {
				// Check for minimum 24 hours.
				$scan_hrs = $this->hours_since_last_scan( $last_scan_start );

				// If scan hours difference has passed 24 hrs limit then remove the options.
				if ( $scan_hrs > 23 ) {
					wfcm_delete_setting( 'scanned-dirs' ); // Delete already scanned directories option.
					wfcm_delete_setting( 'last-scan-start' ); // Delete last scan complete timestamp option.
				} else {
					// Else if they have not passed their limit, then return false.
					return false;
				}
			}
		}

		// Scan check.
		$scan = false;

		// Frequency set by user on the settings page.
		switch ( $frequency ) {
			case self::SCAN_DAILY: // Daily scan.
				if ( in_array( $this->calculate_daily_hour(), self::$daily_hour, true ) ) {
					$scan = true;
				}
				break;
			case self::SCAN_WEEKLY: // Weekly scan.
				$weekly_day = $this->calculate_weekly_day();
				$scan       = ( self::$weekly_day === $weekly_day ) ? true : false;
				break;
			default:
				//  no other scan frequencies are supported
		}
		return $scan;
	}

	/**
	 * Check to determine if there is any directory left to scan.
	 *
	 * @param array $scan_directories - Array of directories to scan set by user.
	 * @return bool
	 */
	public function dir_left_to_scan( $scan_directories ) {
		// False if $scan_directories is empty.
		if ( empty( $scan_directories ) ) {
			return false;
		}

		// If multisite then remove all the subsites uploads of multisite from scan directories.
		if ( is_multisite() ) {
			$uploads_dir         = wfcm_get_server_directory( $this->get_uploads_dir_path() );
			$mu_uploads_site_dir = $uploads_dir . '/sites'; // Multsite uploads directory.

			foreach ( $scan_directories as $index => $dir ) {
				if ( false !== strpos( $dir, $mu_uploads_site_dir ) ) {
					unset( $scan_directories[ $index ] );
				}
			}
		}

		// Get array of already directories scanned from DB.
		$already_scanned_dirs = wfcm_get_setting( 'scanned-dirs', array() );

		// Check if already scanned directories has `root` directory.
		if ( in_array( '', $already_scanned_dirs, true ) ) {
			// If found then search for `root` in the directories to be scanned.
			$key = array_search( 'root', $scan_directories, true );
			if ( false !== $key ) {
				// If key is found then remove it from directories to be scanned array.
				unset( $scan_directories[ $key ] );
			}
		}

		// Check the difference in directories.
		$diff = array_diff( $scan_directories, $already_scanned_dirs );

		// If the diff array has 1 or more value then scan needs to run.
		if ( is_array( $diff ) && count( $diff ) > 0 ) {
			return true;
		} elseif ( empty( $diff ) ) {
			return false;
		}
		return false;
	}

	/**
	 * Get number of hours since last file changes scan.
	 *
	 * @param float $created_on - Timestamp of last scan.
	 * @return bool|int         - False if $created_on is empty | Number of hours otherwise.
	 */
	public function hours_since_last_scan( $created_on ) {
		// If $created_on is empty, then return.
		if ( ! $created_on ) {
			return false;
		}

		// Last alert date.
		$created_date = new DateTime( date( 'Y-m-d H:i:s', $created_on ) );

		// Current date.
		$current_date = new DateTime( 'NOW' );

		// Calculate time difference.
		$time_diff = $current_date->diff( $created_date );
		$diff_days = $time_diff->d; // Difference in number of days.
		$diff_hrs  = $time_diff->h; // Difference in number of hours.
		$total_hrs = ( $diff_days * 24 ) + $diff_hrs; // Total number of hours.

		// Return difference in hours.
		return $total_hrs;
	}

	/**
	 * Calculate and return hour of the day based on WordPress timezone.
	 *
	 * @return string - Hour of the day.
	 */
	private function calculate_daily_hour() {
		return date( 'H', time() + ( get_option( 'gmt_offset' ) * ( 60 * 60 ) ) );
	}

	/**
	 * Calculate and return day of the week based on WordPress timezone.
	 *
	 * @return string|bool - Day of the week or false.
	 */
	private function calculate_weekly_day() {
		if ( in_array( $this->calculate_daily_hour(), self::$daily_hour, true ) ) {
			return date( 'w' );
		}
		return false;
	}

	/**
	 * Reset file and directory counter for scan.
	 */
	public function reset_scan_counter() {
		$this->scan_file_count = 0;
		$this->scan_limit_file = false;
	}

	/**
	 * Scan path for files.
	 *
	 * @param string $path - Directory path to scan.
	 * @return array       - Array of files present in $path.
	 */
	public function scan_path( $path = '' ) {

		// Check excluded paths.
		if ( in_array( $path, $this->excludes ) ) {
			return array();
		}

		// Set the directory path.
		$dir_path = $this->root_path . $path;
		$files    = array(); // Array of files to return.

		// Open directory.
		$dir_handle = @opendir( $dir_path );

		if ( false === $dir_handle ) {
			return $files; // Return if directory fails to open.
		}

		$is_multisite     = is_multisite();                               // Multsite checks.
		$directories      = $this->scan_settings['directories'];          // Get directories to be scanned.
		$file_size_limit  = $this->scan_settings['file-size'];            // Get file size limit.
		$file_size_limit  = $file_size_limit * 1048576;                   // Calculate file size limit in bytes; 1MB = 1024 KB = 1024 * 1024 bytes = 1048576 bytes.
		$files_over_limit = array();                                      // Array of files which are over their file size limit.
		$admin_notices    = wfcm_get_setting( 'admin-notices', array() ); // Get admin notices.

		$uploads_dir         = wfcm_get_server_directory( $this->get_uploads_dir_path() );
		$mu_uploads_site_dir = $uploads_dir . '/sites'; // Multsite uploads directory.
		// A list of development folders we may want to skip.
		$dev_folders = (array) apply_filters( 'wfcm_excluded_dev_folders', array( '.git', '.github', '.svn', 'node_modules' ) );

		// Scan the directory for files.
		while ( false !== ( $item = @readdir( $dir_handle ) ) ) {
			// Ignore `.` and `..` from directory.
			if ( '.' === $item || '..' === $item ) {
				continue;
			}

			// Filter scannable filenames, some special characters are allowed.
			if ( preg_match( '/[^A-Za-z0-9 _.@-]/', $item ) > 0 ) {
				// File name contains a special character of some type...
				if ( preg_match( '/[\|\&\$\^]/', $item ) > 0 ) {
					if ( $this->scan_settings['debug-logging'] ) {
						// log this unusual file discovered.
						$location = sanitize_text_field( $dir_path . '/' . (string) $item );
						$message  = esc_html__( 'Encountered unusual filename at: ', 'website-file-changes-monitor' ) . $location;
						wfcm_write_to_log( $message );
					}
					// before version 1.5 we would skip over this file here
					// now we hash it as well.
				}
			}

			// Check if the option to scan dev folders is NOT enabled.
			// By default we don't scan them.
			if ( ! $this->scan_settings['scan-dev-folders'] ) {
				foreach ( $dev_folders as $dev_folder ) {
					// If the current item is a folder which is set to skip...
					if ( false !== strpos( $item, $dev_folder ) ) {
						// Skip this item and continue to next.
						continue 2;
					}
				}
			}

			// Set item paths.
			$file_paths = $this->get_file_paths($item, $path, $dir_path);
			$relative_name = $file_paths['rel'];
			$absolute_name = $file_paths['abs'];

			// Check for directory.
			if ( is_dir( $absolute_name ) && 'wp-content' !== $path ) {
				/**
				 * `Filter`: Directory name filter before opening it for scan.
				 *
				 * @param string $item - Directory name.
				 */
				$item = apply_filters( 'wcfm_directory_before_file_scan', $item );
				if ( ! $item ) {
					continue;
				}

				// Check if the directory is in excluded directories list.
				if ( $this->is_dir_part_of_dir_list( $absolute_name, $this->scan_settings['exclude-dirs'] ) ) {
					continue; // Skip the directory.
				}

				// If not multisite then simply scan.
				if ( ! $is_multisite ) {
					$files = array_merge( $files, $this->scan_path( $relative_name ) );
				} else {
					/**
					 * Check if `wp-content/uploads/sites` is present in the
					 * relative name of the directory & it is allowed to scan.
					 */
					if ( false !== strpos( $relative_name, $mu_uploads_site_dir ) && in_array( $mu_uploads_site_dir, $directories, true ) ) {
						$files = array_merge( $files, $this->scan_path( $relative_name ) );
					} elseif ( false !== strpos( $relative_name, $mu_uploads_site_dir ) && ! in_array( $mu_uploads_site_dir, $directories, true ) ) {
						// If `wp-content/uploads/sites` is not allowed to scan then skip the loop.
						continue;
					} else {
						$files = array_merge( $files, $this->scan_path( $relative_name ) );
					}
				}
			} else {
				/**
				 * `Filter`: File name filter before scan.
				 *
				 * @param string $item – File name.
				 */
				$item = apply_filters( 'wfcm_filename_before_file_scan', $item );
				if ( ! $item || is_dir( $absolute_name ) ) {
					continue;
				}

				//  check if the file should be excluded
				if ( $this->is_file_excluded( $item, false ) ) {
					continue;
				}

				// Check files count.
				if ( $this->scan_file_count > self::SCAN_FILE_LIMIT ) { // If file limit is reached.
					$this->scan_limit_file = true; // Then set the limit flag.
					break; // And break the loop.
				}

				// Check file size limit.
				if ( ! is_link( $absolute_name ) && filesize( $absolute_name ) < $file_size_limit ) {
					$this->scan_file_count++;

					// File data.
					$files[ $absolute_name ] = @hash_file( self::HASHING_ALGORITHM, $absolute_name ); // File hash.
				} elseif ( is_link( $absolute_name ) ) {
					$files[ $absolute_name ] = '';
				} else {
					if ( ! isset( $admin_notices['filesize-limit'] ) || ! in_array( $absolute_name, $admin_notices['filesize-limit'], true ) ) {
						// File size is more than the limit.
						array_push( $files_over_limit, $absolute_name );
					}

					// File data.
					$files[ $absolute_name ] = '';
				}
			}
		}

		// Close the directory.
		@closedir( $dir_handle );

		if ( ! empty( $files_over_limit ) ) {
			if ( ! isset( $admin_notices['filesize-limit'] ) || ! is_array( $admin_notices['filesize-limit'] ) ) {
				$admin_notices['filesize-limit'] = array();
			}

			$admin_notices['filesize-limit'] = array_merge( $admin_notices['filesize-limit'], $files_over_limit );

			wfcm_save_setting( 'admin-notices', $admin_notices );

			// Trigger WSAL Event 6031.
			do_action( 'wfcm_wsal_file_size_exceeded', $files_over_limit );
		}

		// Return files data.
		return $files;
	}

	/**
	 * Filter scan files before file changes comparison. This
	 * function filters both stored & scanned files.
	 *
	 * Filters:
	 *     1. root folder, wp-admin and wp-include (WP Core).
	 *     2. wp-content/plugins (Plugins).
	 *     3. wp-content/themes (Themes).
	 *
	 * Hooks using this function:
	 *     1. wfcm_file_scan_stored_files.
	 *     2. wfcm_file_scan_scanned_files.
	 *
	 * @param array  $scan_files   - Scan files array.
	 * @param string $path_to_scan - Path currently being scanned.
	 * @return array
	 */
	public function filter_scan_files( $scan_files, $path_to_scan ) {

		// If the path to scan is of plugins.
		if ( false !== strpos( $path_to_scan, wfcm_get_server_directory( WP_PLUGIN_DIR ) ) ) {
			// Filter plugin files.
			$scan_files = $this->filter_excluded_scan_files( $scan_files, 'plugins' );
		} elseif ( false !== strpos( $path_to_scan, wfcm_get_server_directory( get_theme_root() ) ) ) { // And if the path to scan is of themes then.
			// Filter theme files.
			$scan_files = $this->filter_excluded_scan_files( $scan_files, 'themes' );
		} elseif (
			empty( $path_to_scan )                           // Root path.
			|| false !== strpos( $path_to_scan, 'wp-admin' ) // WP Admin.
			|| false !== strpos( $path_to_scan, WPINC )      // WP Includes.
		) {
			// Get `site_content` option.
			$site_content = wfcm_get_setting( WFCM_Settings::$site_content );

			// If the `skip_core` is set and its value is equal to true then.
			if ( isset( $site_content->skip_core ) && true === $site_content->skip_core ) {
				// Check the create events for wp-core file updates.
				$scan_files = $this->filter_excluded_scan_files( $scan_files, $path_to_scan );
			}
		}

		// Return the filtered scan files.
		return $scan_files;
	}

	/**
	 * Filter different types of content from scan files.
	 *
	 * Excluded types:
	 *  1. Plugins.
	 *  2. Themes.
	 *
	 * @param array  $scan_files    - Array of scan files.
	 * @param string $excluded_type - Type to be excluded.
	 * @return array
	 */
	private function filter_excluded_scan_files( $scan_files, $excluded_type ) {
		if ( empty( $scan_files ) ) {
			return $scan_files;
		}

		// Get list of excluded plugins/themes.
		$excluded_contents = wfcm_get_setting( WFCM_Settings::$site_content );

		// If excluded files exists then.
		if ( ! empty( $excluded_contents ) ) {
			// Get the array of scan files.
			$files = array_keys( $scan_files );

			// An array of files to exclude from scan files array.
			$files_to_exclude = array();

			// Type of content to skip.
			$skip_type = 'skip_' . $excluded_type; // Possitble values: `plugins` or `themes`.

			// Get current filter.
			$current_filter = current_filter();

			if (
				in_array( $excluded_type, array( 'plugins', 'themes' ), true ) // Only two skip types are allowed.
				&& isset( $excluded_contents->$skip_type )                     // Skip type array exists.
				&& is_array( $excluded_contents->$skip_type )                  // Skip type is array.
				&& ! empty( $excluded_contents->$skip_type )                   // And is not empty.
			) {
				// Go through each plugin to be skipped.
				foreach ( $excluded_contents->$skip_type as $content => $context ) {
					// Path of plugin to search in stored files.
					$search_path = '/' . $excluded_type . '/' . $content;

					// An array of content to be stored as meta for event.
					$event_content = array();

					// Get array of files to exclude of plugins from scan files array.
					foreach ( $files as $file ) {
						if ( false !== strpos( $file, $search_path ) ) {
							$files_to_exclude[] = $file;

							$event_content[ $file ] = (object) array(
								'file' => $file,
								'hash' => isset( $scan_files[ $file ] ) ? $scan_files[ $file ] : false,
							);
						}
					}

					if ( 'update' === $context ) {
						if ( 'wfcm_file_scan_stored_files' === $current_filter ) {
							$this->files_to_exclude[ $search_path ] = $event_content;
						} elseif ( 'wfcm_file_scan_scanned_files' === $current_filter ) {
							$this->check_directory_for_updates( $event_content, $search_path );
						}
					}

					if ( ! empty( $event_content ) ) {
						$dir_path = untrailingslashit( WP_CONTENT_DIR ) . $search_path;

						if ( in_array( 'added', $this->scan_settings['type'], true ) && 'wfcm_file_scan_scanned_files' === $current_filter && 'install' === $context ) {
							$event_context = '';
							if ( 'plugins' === $excluded_type ) {
								// Set context.
								$event_context = __( 'Plugin Install', 'website-file-changes-monitor' );

								// Set the count.
								$current_scan_changes_count = ( isset( $this->scan_changes_count['plugin_installs'] ) ) ? $this->scan_changes_count['plugin_installs'] : 0;
								$this->scan_changes_count['plugin_installs'] = $current_scan_changes_count + 1;

								// Log the installed plugin files.
								if ( $this->scan_settings['debug-logging'] ) {
									$msg  = wfcm_get_log_timestamp() . ' ';
									$msg .= __( 'Installed plugin:', 'website-file-changes-monitor' ) . " {$dir_path}\n";
									$msg .= __( 'Added files:', 'website-file-changes-monitor' ) . "\n";
									$msg .= implode( "\n", array_keys( $event_content ) );
									$msg .= "\n";
									wfcm_write_to_log( $msg );
								}
							} elseif ( 'themes' === $excluded_type ) {
								// Set context.
								$event_context = __( 'Theme Install', 'website-file-changes-monitor' );

								// Set the count.
								$current_scan_changes_count = ( isset( $this->scan_changes_count['theme_installs'] ) ) ? $this->scan_changes_count['theme_installs'] : 0;
								$this->scan_changes_count['theme_installs'] = $current_scan_changes_count + 1;

								// Log the installed theme files.
								if ( $this->scan_settings['debug-logging'] ) {
									$msg  = wfcm_get_log_timestamp() . ' ';
									$msg .= __( 'Installed theme:', 'website-file-changes-monitor' ) . " {$dir_path}\n";
									$msg .= __( 'Added files:', 'website-file-changes-monitor' ) . "\n";
									$msg .= implode( "\n", array_keys( $event_content ) );
									$msg .= "\n";
									wfcm_write_to_log( $msg );
								}
							}

							wfcm_create_directory_event( 'added', $dir_path, array_values( $event_content ), $event_context );
						} elseif ( in_array( 'deleted', $this->scan_settings['type'], true ) && 'wfcm_file_scan_stored_files' === $current_filter && 'uninstall' === $context ) {
							$event_context = '';
							if ( 'plugins' === $excluded_type ) {
								// Set context.
								$event_context = __( 'Plugin Uninstall', 'website-file-changes-monitor' );

								// Set the count.
								$current_scan_changes_count = ( isset( $this->scan_changes_count['plugin_uninstalls'] ) ) ? $this->scan_changes_count['plugin_uninstalls'] : 0;
								$this->scan_changes_count['plugin_uninstalls'] = $current_scan_changes_count + 1;

								// Log the uninstalled plugin files.
								if ( $this->scan_settings['debug-logging'] ) {
									$msg  = wfcm_get_log_timestamp() . ' ';
									$msg .= __( 'Uninstalled plugin:', 'website-file-changes-monitor' ) . " {$dir_path}\n";
									$msg .= __( 'Deleted files:', 'website-file-changes-monitor' ) . "\n";
									$msg .= implode( "\n", array_keys( $event_content ) );
									$msg .= "\n";
									wfcm_write_to_log( $msg );
								}
							} elseif ( 'themes' === $excluded_type ) {
								// Set context.
								$event_context = __( 'Theme Uninstall', 'website-file-changes-monitor' );

								// Set the count.
								$current_scan_changes_count = ( isset( $this->scan_changes_count['theme_uninstalls'] ) ) ? $this->scan_changes_count['theme_uninstalls'] : 0;
								$this->scan_changes_count['theme_uninstalls'] = $current_scan_changes_count + 1;

								// Log the uninstalled theme files.
								if ( $this->scan_settings['debug-logging'] ) {
									$msg  = wfcm_get_log_timestamp() . ' ';
									$msg .= __( 'Uninstalled theme:', 'website-file-changes-monitor' ) . " {$dir_path}\n";
									$msg .= __( 'Deleted files:', 'website-file-changes-monitor' ) . "\n";
									$msg .= implode( "\n", array_keys( $event_content ) );
									$msg .= "\n";
									wfcm_write_to_log( $msg );
								}
							}

							wfcm_create_directory_event( 'deleted', $dir_path, array_values( $event_content ), $event_context );
						}
					}
				}
			} elseif ( ! $excluded_type || in_array( $excluded_type, array( 'wp-admin', WPINC ), true ) ) {
				// An array of content to be stored as meta for event.
				$event_content = array();

				$directory = trailingslashit( ABSPATH ) . $excluded_type;

				foreach ( $scan_files as $file => $file_hash ) {
					$files_to_exclude[] = $file;
					$event_content[ $file ] = (object) array(
						'file' => $file,
						'hash' => $file_hash,
					);
				}

				if ( ! empty( $event_content ) ) {
					if ( 'wfcm_file_scan_stored_files' === $current_filter ) {
						$this->files_to_exclude[ $directory ] = $event_content;
					} elseif ( 'wfcm_file_scan_scanned_files' === $current_filter ) {
						$this->check_directory_for_updates( $event_content, $directory );
					}
				}
			}

			// If there are files to be excluded then.
			if ( ! empty( $files_to_exclude ) ) {
				// Go through each file to be excluded and unset it from scan files array.
				foreach ( $files_to_exclude as $file_to_exclude ) {
					if ( array_key_exists( $file_to_exclude, $scan_files ) ) {
						unset( $scan_files[ $file_to_exclude ] );
					}
				}
			}
		}

		return $scan_files;
	}

	/**
	 * Empty skip file alerts array after scanning the path.
	 *
	 * @param string $path_to_scan - Path currently being scanned.
	 * @return void
	 */
	public function empty_skip_file_alerts( $path_to_scan ) {
		// Check path to scan is not empty.
		if ( empty( $path_to_scan ) ) {
			return;
		}

		// If path to scan is of plugins then empty the skip plugins array.
		if ( false !== strpos( $path_to_scan, wfcm_get_server_directory( WP_PLUGIN_DIR ) ) ) {
			// Get contents list.
			$site_content = wfcm_get_setting( WFCM_Settings::$site_content, false );

			// if we don't have an object make this one.
			if ( ! $site_content ) {
				$site_content = new stdClass();
			}
			// Empty skip plugins array.
			$site_content->skip_plugins = array();

			// Save it.
			wfcm_save_setting( WFCM_Settings::$site_content, $site_content );

			// If path to scan is of themes then empty the skip themes array.
		} elseif ( false !== strpos( $path_to_scan, wfcm_get_server_directory( get_theme_root() ) ) ) {
			// Get contents list.
			$site_content = wfcm_get_setting( WFCM_Settings::$site_content, false );

			// if we don't have an object make this one.
			if ( ! $site_content ) {
				$site_content = new stdClass();
			}
			// Empty skip themes array.
			$site_content->skip_themes = array();

			// Save it.
			wfcm_save_setting( WFCM_Settings::$site_content, $site_content );
		}
	}

	/**
	 * Reset core file changes flag.
	 */
	public function reset_core_updates_flag( $changes ) {
		// Get `site_content` option.
		$site_content = wfcm_get_setting( WFCM_Settings::$site_content, false );

		// Check WP core update.
		if ( isset( $site_content->skip_core ) && $site_content->skip_core ) {
			$this->scan_changes_count['wp_core_update'] = 1;
		}

		// Check if the option is instance of stdClass.
		if ( false !== $site_content && $site_content instanceof stdClass ) {
			$site_content->skip_core  = false;   // Reset skip core after the scan is complete.
			$site_content->skip_files = array(); // Empty the skip files at the end of the scan.
			$site_content->skip_exts  = array(); // Empty the skip extensions at the end of the scan.
			$site_content->skip_dirs  = array(); // Empty the skip directories at the end of the scan.
			wfcm_save_setting( WFCM_Settings::$site_content, $site_content ); // Save the option.
		}
	}

	/**
	 * Check directory for file change events after updates.
	 *
	 * @param array  $scanned_files - Array of excluded scanned files.
	 * @param string $directory     - Name of the directory.
	 */
	public function check_directory_for_updates( $scanned_files, $directory ) {
		// Get the files previously stored in the directory.
		$stored_files = $this->files_to_exclude[ $directory ];

		// Compare the results to find out about file added and removed.
		$files_added   = array_diff_key( $scanned_files, $stored_files );
		$files_removed = array_diff_key( $stored_files, $scanned_files );

		/**
		 * File changes.
		 *
		 * To scan the files with changes, we need to
		 *
		 *  1. Remove the newly added files from scanned files – no need to add them to changed files array.
		 *  2. Remove the deleted files from already logged files – no need to compare them since they are removed.
		 *  3. Then start scanning for differences – check the difference in hash.
		 */
		$scanned_files_minus_added  = array_diff_key( $scanned_files, $files_added );
		$stored_files_minus_deleted = array_diff_key( $stored_files, $files_removed );

		// Changed files array.
		$files_changed = array();

		// Go through each newly scanned file.
		foreach ( $scanned_files_minus_added as $file => $file_obj ) {
			// Check if it exists in already stored array of files, ignore if the key does not exists.
			if ( array_key_exists( $file, $stored_files_minus_deleted ) ) {
				// If key exists, then check if the file hash is set and compare it to already stored hash.
				if (
					! empty( $file_obj->hash ) && ! empty( $stored_files_minus_deleted[ $file ] )
					&& 0 !== strcmp( $file_obj->hash, $stored_files_minus_deleted[ $file ]->hash )
				) {
					// If the file hashes don't match then store the file in changed files array.
					$files_changed[ $file ] = $file_obj;
				}
			}
		}

		$dirname       = ABSPATH !== $directory ? dirname( $directory ) : $directory;
		$dir_path      = '';
		$event_context = '';
		$log_type      = '';

		if ( '/plugins' === $dirname ) {
			$dir_path      = untrailingslashit( WP_CONTENT_DIR ) . $directory;
			$event_context = __( 'Plugin Update', 'website-file-changes-monitor' );
			$log_type      = 'plugin';

			// Set the count.
			$this->scan_changes_count['plugin_updates'] += 1;
		} elseif ( '/themes' === $dirname ) {
			$dir_path      = untrailingslashit( WP_CONTENT_DIR ) . $directory;
			$event_context = __( 'Theme Update', 'website-file-changes-monitor' );
			$log_type      = 'theme';

			// Set the count.
			$this->scan_changes_count['theme_updates'] += 1;
		} elseif ( ABSPATH === $directory || false !== strpos( $directory, 'wp-admin' ) || false !== strpos( $directory, WPINC ) ) {
			$dir_path      = $directory;
			$event_context = __( 'Core Update', 'website-file-changes-monitor' );
			$log_type      = 'core';
		}

		if ( in_array( 'added', $this->scan_settings['type'], true ) && count( $files_added ) > 0 ) {
			wfcm_create_directory_event( 'added', $dir_path, array_values( $files_added ), $event_context );

			// Log the added update files.
			if ( $this->scan_settings['debug-logging'] ) {
				$msg  = wfcm_get_log_timestamp() . ' ';
				$msg .= __( 'Updated', 'website-file-changes-monitor' ) . " {$log_type}: " . $dir_path . "\n";
				$msg .= __( 'Added files:', 'website-file-changes-monitor' ) . "\n";
				$msg .= implode( "\n", array_keys( $files_added ) );
				$msg .= "\n";
				wfcm_write_to_log( $msg );
			}
		}

		if ( in_array( 'deleted', $this->scan_settings['type'], true ) && count( $files_removed ) > 0 ) {
			wfcm_create_directory_event( 'deleted', $dir_path, array_values( $files_removed ), $event_context );

			// Log the deleted update files.
			if ( $this->scan_settings['debug-logging'] ) {
				$msg  = wfcm_get_log_timestamp() . ' ';
				$msg .= __( 'Updated', 'website-file-changes-monitor' ) . " {$log_type}: " . $dir_path . "\n";
				$msg .= __( 'Deleted files:', 'website-file-changes-monitor' ) . "\n";
				$msg .= implode( "\n", array_keys( $files_removed ) );
				$msg .= "\n";
				wfcm_write_to_log( $msg );
			}
		}

		if ( ! empty( $files_changed ) && in_array( 'modified', $this->scan_settings['type'], true ) ) {
			wfcm_create_directory_event( 'modified', $dir_path, array_values( $files_changed ), $event_context );

			// Log the modified update files.
			if ( $this->scan_settings['debug-logging'] ) {
				$msg  = wfcm_get_log_timestamp() . ' ';
				$msg .= __( 'Updated', 'website-file-changes-monitor' ) . " {$log_type}: " . $dir_path . "\n";
				$msg .= __( 'Modified files:', 'website-file-changes-monitor' ) . "\n";
				$msg .= implode( "\n", array_keys( $files_changed ) );
				$msg .= "\n";
				wfcm_write_to_log( $msg );
			}
		}
	}

	/**
	 * Returns the path of WP uploads directory.
	 *
	 * @return string
	 */
	private function get_uploads_dir_path() {
		if ( empty( $this->uploads_dir ) ) {
			$this->uploads_dir = wp_upload_dir(); // Get WP uploads directory.
		}
		return $this->uploads_dir['basedir'];
	}

	/**
	 * Scan changes count; get, save, or delete.
	 *
	 * @param string $action - Count action; get, save, or delete.
	 */
	private function scan_changes_count( $action = 'get' ) {
		if ( 'get' === $action ) {
			$this->scan_changes_count = get_transient( 'wfcm-scan-changes-count' );

			if ( false === $this->scan_changes_count ) {
				$this->scan_changes_count = array(
					'files_added'       => 0,
					'files_deleted'     => 0,
					'files_modified'    => 0,
					'plugin_installs'   => 0,
					'plugin_updates'    => 0,
					'plugin_uninstalls' => 0,
					'theme_installs'    => 0,
					'theme_updates'     => 0,
					'theme_uninstalls'  => 0,
					'wp_core_update'    => 0,
				);
			}
		} elseif ( 'save' === $action ) {
			set_transient( 'wfcm-scan-changes-count', $this->scan_changes_count, DAY_IN_SECONDS );
		} elseif ( 'delete' === $action ) {
			delete_transient( 'wfcm-scan-changes-count' );
		}
	}

	/**
	 * Get checksums of files in WordPress core from the WordPress.org API.
	 *
	 * Uses WordPress transient to cache the results for a week.
	 *
	 * @return array
	 *
	 * @since 1.7.0
	 */
	private function get_core_files_hashes( ) {
		$version = $GLOBALS['wp_version'];
		$locale  = get_locale();

		//  try to load checksum from transient cache
		$cache_key = 'wfcm_wp_org_checksums_' . $version . '_' . $locale;
		if ( false === ( $cached_checksums = get_transient( $cache_key ) ) ) {
			$endpoint_url = add_query_arg( [
				'version' => $version,
				'locale'  => $locale
			], 'https://api.wordpress.org/core/checksums/1.0/' );
			$response     = wp_remote_get( $endpoint_url );
			if ( is_wp_error( $response ) ) {
				return [];
			}

			$body = json_decode( $response['body'], true );
			if ( empty( $body['checksums'] ) || ! is_array( $body['checksums'] ) ) {
				return [];
			}

			$checksums = $body['checksums'];
			set_transient( $cache_key, json_encode( $body['checksums'] ), WEEK_IN_SECONDS );
		} else {
			//  cached value need to be decoded first
			$checksums = json_decode( $cached_checksums, true );
			if ( ! is_array( $checksums ) ) {
				//  empty array is returned if the data is malformed in any way and cannot be decoded as JSON
				return [];
			}
		}

		return $checksums;
	}

	/**
	 * Checks if the file should be excluded from the scan by:
	 * - filename
	 * - file extension
	 * - optionally by a directory in which it resides
	 *
	 * @param string $item Absolute path to a file.
	 * @param bool $check_path_for_dir_exclusion If true, the excluded directories are checked as well.
	 *
	 * @return bool If true, the file should be excluded.
	 *
	 * @since 1.7.0
	 */
	private function is_file_excluded($item, $check_path_for_dir_exclusion = false) {
		// Check if the item is in excluded files list.
		if ( in_array( $item, $this->scan_settings['exclude-files'], true ) ) {
			return true;
		}

		if ( $check_path_for_dir_exclusion ) {
			// Check if the directory the file is in is in excluded directories list.
			if ( $this->is_dir_part_of_dir_list( dirname( $item ), $this->scan_settings['exclude-dirs'] ) ) {
				return true;
			}
		}

		// Check for allowed extensions.
		if ( in_array( pathinfo( $item, PATHINFO_EXTENSION ), $this->scan_settings['exclude-exts'], true ) ) {
			return true;
		}

		return false;
	}

	public function get_file_paths($item, $path, $dir_path) {
		if ( ! empty( $path ) ) {
			$relative_name = $path . '/' . $item;     // Relative file path w.r.t. the location in 7 major folders.
			$absolute_name = $dir_path . '/' . $item; // Complete file path w.r.t. ABSPATH.
		} else {
			// If path is empty then it is root.
			$relative_name = $path . $item;     // Relative file path w.r.t. the location in 7 major folders.
			$absolute_name = $dir_path . $item; // Complete file path w.r.t. ABSPATH.
		}

		return [
			'rel' => $relative_name,
			'abs' => $absolute_name
		];
	}

	/**
	 * Transforms the list of core files retrieved from WordPress API into a list suitable for comparison. It also
	 * filters out any excluded files, "core" themes and plugins and takes recent WordPress upgrade into account.
	 *
	 * It also managed admin notices related to a possible WordPress APi comms failure.
	 *
	 * @param array $release_files_list
	 * @param string $current_path_to_scan
	 *
	 * @return array
	 * @since 1.7.0
	 */
	private function get_core_files_to_verify( $release_files_list, $current_path_to_scan ) {
		if ( ! empty( $release_files_list ) ) {

			$release_files_to_check = [];

			//  only do this if we are not supposed to skip core for some reason (most likely a recent update)
			$site_content = wfcm_get_setting( WFCM_Settings::$site_content );
			if ( ! isset( $site_content->skip_core ) || true !== $site_content->skip_core ) {
				$dir_path = $this->root_path . $current_path_to_scan;
				foreach ( $release_files_list as $relative_file_path => $hash ) {
					//  convert relative paths to absolute paths
					$paths              = $this->get_file_paths( $relative_file_path, $current_path_to_scan, $dir_path );
					$absolute_file_path = $paths['abs'];

					//  remove excluded files from the list
					if ( $this->is_file_excluded( $absolute_file_path, true ) ) {
						continue;
					}

					//  exclude any plugin and themes in the file list from wp.org repo, we don't want to
					//  treat the preinstalled twenty* themes and akismet and hello dolly plugins as part of
					//  WP Core
					if ( preg_match( '/wp\-content\/(plugins|themes|)/', $absolute_file_path ) ) {
						continue;
					}

					$release_files_to_check[ $absolute_file_path ] = $hash;
				}
			}

			//  remove admin notice showing WP API comms failure if it still exists
			$admin_notices = wfcm_get_setting( 'admin-notices', array() );
			if ( isset( $admin_notices['wp-repo-core-comms-failed'] ) ) {
				unset( $admin_notices['wp-repo-core-comms-failed'] );
				wfcm_save_setting( 'admin-notices', $admin_notices );
			}

		} else {
			//  create admin notice to report failed communication with WP API
			$admin_notices = wfcm_get_setting( 'admin-notices', array() );
			if ( ! isset( $admin_notices['wp-repo-core-comms-failed'] ) ) {
				$admin_notices['wp-repo-core-comms-failed'] = true;
				wfcm_save_setting( 'admin-notices', $admin_notices );
			}
		}
		return $release_files_to_check;
	}

	/**
	 * Checks if a folder is in the list of given target folders or anywhere in side them.
	 *
	 * @param string $directory_name Absolute path to a directory that needs to be checked.
	 * @param string[] $target_dirs Absolute paths of directories to be searched.
	 *
	 * @return bool True if the dir is part of any of given target folders.
	 * @since 1.7.1
	 */
	private function is_dir_part_of_dir_list( $directory_name, $target_dirs) {
		if ( ! empty ( $target_dirs ) ) {
			if ( in_array( $directory_name, $target_dirs, true ) ) {
				//  directory is present in the list of target dirs
				return true;
			}

			//  let's check if the directory is nested deeper in one of the target dirs
			$path_to_check = $directory_name;
			do {
				$path_to_check = substr( $path_to_check, 0, strripos( $path_to_check, DIRECTORY_SEPARATOR ) );
				if ( in_array( $path_to_check, $target_dirs, true ) ) {
					//  skip file because the folder it lives in is allowed in site root or WP core and we don't want to log an added event
					return true;
				}
			} while ( ! empty( $path_to_check ) );
		}

		return false;
	}

	public function check_for_changes( $path_to_scan ) {

		// Start tracking total runtime and increase php max_execution_time.
		$this->start_tracking_php_runtime();

		// Are we checking the root?
		$scanning_wp_core_files = empty( $path_to_scan );

		// Grab files found during last scan of this path.
		$setting_name  = $this->create_tidy_name( $path_to_scan );
		$scanned_files = wfcm_get_all_stored_files( $setting_name . '_comparison' );

		// If we have no files for comparison stored yet, this is the 1st scan, so to see if any core files have been modified we need to use
		// what we have already have stored.
		if ( empty( $scanned_files ) ) {
			if ( $scanning_wp_core_files ) {
				$scanned_files = wfcm_get_all_stored_files( $setting_name );
			} else {
				return;
			}
		}
		
		$stored_files = wfcm_get_all_stored_files( $setting_name );

		if ( $stored_files === $scanned_files ) {
			if ( $this->scan_settings['debug-logging'] ) {
				$msg = wfcm_get_log_timestamp() . ' ' . __( 'No changes found in this path: ' . $path_to_scan, 'website-file-changes-monitor' ) . " \n";
				wfcm_write_to_log( $msg );
			}
			return;
		}

		// Files not found.
		$modified_files = array_diff_assoc( $stored_files, $scanned_files );

		// Modified since last scan.
		if ( ! empty( $modified_files ) ) {
			$scanned_files = array_diff_assoc( $scanned_files, $stored_files );
			$stored_files  = $modified_files;
		}

		$filtered_stored_files  = apply_filters( 'wfcm_file_scan_stored_files', $stored_files, $path_to_scan );
		$filtered_scanned_files = apply_filters( 'wfcm_file_scan_scanned_files', $scanned_files, $path_to_scan );

		/**
		 * After file scan action hook.
		 *
		 * @param string $path_to_scan - Directory path to scan.
		 */
		do_action( 'wfcm_after_file_scan', $path_to_scan );

		//  check if we're supposed to run the checksum validation against wp.org repo
		$run_wp_org_checksum_validation = $scanning_wp_core_files && 'yes' === $this->scan_settings['wp-repo-core-checksum-validation-enabled'];

		if ( $run_wp_org_checksum_validation ) {
			//  get the checksums list from wp.org API (implement caching)
			$release_files_list     = $this->get_core_files_hashes();
			//  filter the list and convert to the correct shape
			$release_files_to_check = $this->get_core_files_to_verify( $release_files_list, $path_to_scan );

			$release_files_to_check = array_intersect_key( $release_files_to_check, $filtered_scanned_files );

			//  run an additional check of MD5 file checksums against wordpress.org repo if we're scanning
			//  WordPress core file and this check is enabled in the plugin settings
			$this->compute_differences_and_create_change_events( $release_files_to_check, $filtered_scanned_files, new WFCM_WordPressOrg_Hash_Comparator(), $path_to_scan, 'wp.org' );

			// generate regular file change events with given file lists
			$this->compute_differences_and_create_change_events( $filtered_stored_files, $filtered_scanned_files, new WFCM_Default_Hash_Comparator(), $path_to_scan, 'local' );
		} else {
			// generate regular file change events with given file lists
			$this->compute_differences_and_create_change_events( $filtered_stored_files, $filtered_scanned_files, new WFCM_Default_Hash_Comparator(), $path_to_scan, 'local' );
		}
	}

	/**
	 * Create a tidy simple name from directories.
	 *
	 * @param string $directory
	 * @param boolean $prefix_needed
	 * @return string
	 */
	public function create_tidy_name( $directory, $prefix_needed = false ) {
		$tidied_name = ( ! empty( $directory ) ) ? str_replace( 'p-', '', substr( $directory, strpos( $directory, '/' ) + 1 ) ) : 'root';
		return ( $prefix_needed ) ? 'wfcm_' . $tidied_name : $tidied_name;
	}

	/**
	 * Determine if any BG processes are currently running.
	 *
	 * @return int|false Number of jobs.
	 */
	public function get_current_number_of_active_bg_processes() {
		global $wpdb;

		$bg_jobs = $wpdb->get_results(
				"SELECT option_value FROM $wpdb->options
				WHERE option_name LIKE '%_wfcm_scanner_%'"
		);

		return count( $bg_jobs );
	}

	/**
	 * Cancel BG processes.
	 *
	 */
	public function cancel_bg_processes() {
		global $wpdb;
		$wpdb->query("DELETE FROM $wpdb->options WHERE option_name LIKE '%_wfcm_scanner_%'");

		$cron_hook_identifiers = [ 'wfcm_scanner_scan_paths', 'wfcm_scanner_check_changes' ];

		foreach ( $cron_hook_identifiers as $cron_hook_identifier ) {
			wp_clear_scheduled_hook( $wpdb->prefix.$cron_hook_identifier );
		}
	}
}

wfcm_get_monitor();
