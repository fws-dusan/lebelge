<?php
/**
 * Settings Class File.
 *
 * @package wfcm
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Plugin Settings Class.
 */
class WFCM_Admin_Settings {

	/**
	 * Admin messages.
	 *
	 * @var array
	 */
	private static $messages = array();

	/**
	 * Add admin message.
	 *
	 * @param string $message - Admin message.
	 */
	public static function add_message( $message ) {
		self::$messages[] = $message;
	}

	/**
	 * Show admin message.
	 */
	public static function show_messages() {
		if ( ! empty( self::$messages ) ) {
			foreach ( self::$messages as $message ) {
				echo '<div class="notice notice-success is-dismissible"><p><strong>' . esc_html( $message ) . '</strong></p></div>';
			}
		}
	}

	/**
	 * Initiate settings page.
	 */
	public static function output() {

		wp_enqueue_style(
			'jquery-confirm',
			WFCM_BASE_URL . 'assets/css/dist/vendors/jquery-confirm.min.css',
			array(),
			'3.3.4'
		);

		wp_enqueue_style(
			'wfcm-settings-styles',
			WFCM_BASE_URL . 'assets/css/dist/build.settings.css',
			array(),
			( defined( 'WP_DEBUG' ) && true === WP_DEBUG ) ? filemtime( WFCM_BASE_DIR . 'assets/css/dist/build.settings.css' ) : WFCM_VERSION
		);

		wp_enqueue_script(
			'jquery-confirm',
			WFCM_BASE_URL . 'assets/js/dist/vendors/jquery-confirm.min.js',
			array(),
			'3.3.4',
			true
		);

		wp_register_script(
			'wfcm-settings',
			WFCM_BASE_URL . 'assets/js/dist/settings.js',
			array( 'jquery-confirm' ),
			( defined( 'WP_DEBUG' ) && true === WP_DEBUG ) ? filemtime( WFCM_BASE_DIR . 'assets/js/dist/settings.js' ) : WFCM_VERSION,
			true
		);

		wp_localize_script(
			'wfcm-settings',
			'wfcmSettingsData',
			array(
				'adminAjax'                     => admin_url( 'admin-ajax.php' ),
				'monitor'          => array(
					'start' => esc_url_raw( rest_url( WFCM_REST_NAMESPACE . WFCM_REST_API::$monitor_base . '/start' ) ),
					'stop'  => esc_url_raw( rest_url( WFCM_REST_NAMESPACE . WFCM_REST_API::$monitor_base . '/stop' ) ),
				),
				'restRequestNonce' => wp_create_nonce( 'wp_rest' ),
				'scanButtons'      => array(
					'scanNow'    => esc_html__( 'Scan Now', 'website-file-changes-monitor' ),
					'scanStop'   => esc_html__( 'Stop Scan', 'website-file-changes-monitor' ),
					'scanning'   => esc_html__( 'Scanning...', 'website-file-changes-monitor' ),
					'stopping'   => esc_html__( 'Stopping scan...', 'website-file-changes-monitor' ),
					'scanFailed' => esc_html__( 'Scan Failed!', 'website-file-changes-monitor' ),
				),
				'fileInvalid'      => esc_html__( 'Filename cannot be added because it contains invalid characters.', 'website-file-changes-monitor' ),
				'extensionInvalid' => esc_html__( 'File extension cannot be added because it contains invalid characters.', 'website-file-changes-monitor' ),
				'dirInvalid'       => esc_html__( 'Directory cannot be added because it contains invalid characters.', 'website-file-changes-monitor' ),
				'emailSending'     => esc_html__( 'Sending...', 'website-file-changes-monitor ' ),
				'emailSent'        => esc_html__( 'Email sent', 'website-file-changes-monitor ' ),
				'sendEmail'        => esc_html__( 'Send a test email', 'website-file-changes-monitor ' ),
			)
		);

		wp_enqueue_script( 'wfcm-settings' );

		// Get plugin settings.
		$settings = wfcm_get_monitor_settings();

		// Include the view file.
		require_once trailingslashit( dirname( __FILE__ ) ) . 'views/html-admin-settings.php';
	}

	/**
	 * Save settings.
	 */
	public static function save() {
		check_admin_referer( 'wfcm-save-admin-settings' );

		if ( ! isset( $_POST['wfcm-settings']['keep-log'] ) ) {
			$_POST['wfcm-settings']['keep-log'] = false;
		}

		if ( isset( $_POST['wfcm-settings'] ) ) {
			$wfcm_settings = $_POST['wfcm-settings']; // @codingStandardsIgnoreLine

			// This is to handle the empty exclude list case.
			$exclude_settings = array(
				'scan-exclude-dirs'                             => array(),
				'scan-exclude-files'                            => array(),
				'scan-exclude-exts'                             => array(),
				'scan-allowed-in-core-dirs'                     => array(),
				'scan-allowed-in-core-files'                    => array(),
				'delete-data'                                   => false,
				'debug-logging'                                 => false,
				'scan-dev-folders'                              => false,
				'scan-wp-repo-core-checksum-validation-enabled' => 'no',
				'send-email-upon-changes'                       => 'no',
				'empty-email-allowed'                           => 'no'
			);

			$wfcm_settings    = 'yes' === $wfcm_settings['keep-log'] ? wp_parse_args( $wfcm_settings, $exclude_settings ) : $wfcm_settings;

			if ( array_key_exists( 'scan-hour', $wfcm_settings ) ) {
				//  convert hours + AM/PM setting to the correct number of hours
				$hours = intval( $wfcm_settings['scan-hour'] );
				if ( array_key_exists( 'scan-hour-am', $wfcm_settings ) && wfcm_is_time_format_am_pm() ) {
					$day_part = $wfcm_settings['scan-hour-am'];
					if ( 'pm' === $day_part ) {
						$hours += 12;
						unset( $wfcm_settings['scan-hour-am'] );
						$wfcm_settings['scan-hour'] = str_pad( $hours, 2, '0', STR_PAD_LEFT );
					}
				}
			}

			//  sanitize request data
			foreach ( $wfcm_settings as $key => &$value ) {
				if ( WFCM_Settings::NOTIFY_ADDRESSES === $key ) {
					$values = preg_split( '/,/', $value );
					$value  = array();
					foreach ( $values as $email_key => $val ) {
						$value[ $email_key ] = sanitize_email( wp_unslash( $val ) );
					}
				} elseif ( is_array( $value ) ) {
					$value = array_map( 'sanitize_text_field', wp_unslash( $value ) );
				} else {
					$value = sanitize_text_field( wp_unslash( $value ) );
				}

				if ( 'scan-exclude-dirs' === $key ) {
					$value = array_filter( $value, array( __CLASS__, 'filter_exclude_directory' ) );
					$value = array_unique( $value );
				}
			}

			unset($value);

			//  reschedule scan cron in necessary (this relies on having old settings still in the database)
			self::clear_scan_cron_if_necessary( $wfcm_settings );

			//  save settings
			foreach ( $wfcm_settings as $key => $value ) {
				$exclude_settings = array( 'scan-exclude-dirs', 'scan-exclude-exts', 'scan-exclude-files' );

				if ( in_array( $key, $exclude_settings, true ) ) {
					self::set_skip_monitor_content( $key, $value );
				}

				wfcm_save_setting( $key, $value );
			}

			self::add_message( __( 'Your settings have been saved.', 'website-file-changes-monitor' ) );
		}
	}

	/**
	 * Clear the scheduled hook if the scan frequency was changed so it can add itself on the new time/schedule.
	 *
	 * Function relies on having old settings still in the database.
	 *
	 * @param array $new_settings
	 *
	 * @since 1.7.0
	 */
	public static function clear_scan_cron_if_necessary( $new_settings ) {
		if ( self::should_scan_cron_clear( $new_settings ) ) {
			wp_clear_scheduled_hook( WFCM_Monitor::$schedule_hook );
		}
	}

	/**
	 * Check if the scan cron should clear when new set of setting values is saved.
	 *
	 * Function relies on having old settings still in the database.
	 *
	 * @param string $new_settings New settings being stored in the database.
	 *
	 * @return bool True if the scan cron should be cleared. False otherwise.
	 * @since 1.7.0
	 */
	private static function should_scan_cron_clear( $new_settings ) {
		foreach ( [ 'scan-frequency', 'scan-day', 'scan-hour' ] as $setting_name ) {
			if ( array_key_exists( $setting_name, $new_settings ) && $new_settings[ $setting_name ] !== wfcm_get_setting( $setting_name ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Filter excluded directories.
	 *
	 * @param string $directory - Excluded directory.
	 * @return string
	 */
	private static function filter_exclude_directory( $directory ) {
		// Get uploads directory.
		$uploads_dir = wp_upload_dir();

		// Server directories.
		$server_dirs = array(
			untrailingslashit( ABSPATH ), // Root directory.
			ABSPATH . 'wp-admin',         // WordPress Admin.
			ABSPATH . WPINC,              // wp-includes.
			WP_CONTENT_DIR,               // wp-content.
			WP_CONTENT_DIR . '/themes',   // Themes.
			WP_PLUGIN_DIR,                // Plugins.
			$uploads_dir['basedir'],      // Uploads.
		);

		if ( '/' === substr( $directory, -1 ) ) {
			$directory = untrailingslashit( $directory );
		}

		if ( ! in_array( $directory, $server_dirs, true ) ) {
			return $directory;
		}
	}

	/**
	 * Set skip monitor content.
	 *
	 * Set skip content for file changes scan to avoid useless notifications.
	 *
	 * @param string $setting - Setting name.
	 * @param array  $value   - Setting value.
	 */
	private static function set_skip_monitor_content( $setting, $value ) {
		$site_content = new stdClass();
		$site_content = wfcm_get_setting( WFCM_Settings::$site_content, $site_content );

		$stored_setting  = wfcm_get_setting( $setting, array() );
		$removed_content = array_diff( $stored_setting, $value );

		if ( ! empty( $removed_content ) ) {
			$type         = str_replace( 'scan-exclude-', '', $setting );
			$content_type = "skip_$type";

			if ( isset( $site_content->$content_type ) ) {
				$site_content->$content_type = array_merge( $site_content->$content_type, $removed_content );
			} else {
				$site_content->$content_type = $removed_content;
			}

			wfcm_save_setting( WFCM_Settings::$site_content, $site_content );
		}
	}
}
