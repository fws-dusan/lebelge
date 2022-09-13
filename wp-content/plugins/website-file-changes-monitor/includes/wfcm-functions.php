<?php
/**
 * WFCM Settings File.
 *
 * @package wfcm
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Get all monitor settings.
 *
 * @return array
 */
function wfcm_get_monitor_settings() {
	if ( class_exists( 'WFCM_Settings' ) ) {
		return WFCM_Settings::get_monitor_settings();
	}
	return array();
}

/**
 * Get plugin setting.
 *
 * @param string $setting - Setting name.
 * @param mixed  $default - Default value.
 * @return mixed
 */
function wfcm_get_setting( $setting, $default = '' ) {
	if ( class_exists( 'WFCM_Settings' ) ) {
		return WFCM_Settings::get_setting( $setting, $default );
	}
	return false;
}

/**
 * Save plugin setting.
 *
 * @param string $setting - Setting name.
 * @param mixed  $value   - Setting value.
 */
function wfcm_save_setting( $setting, $value ) {
	if ( class_exists( 'WFCM_Settings' ) ) {
		WFCM_Settings::save_setting( $setting, $value );
	}
}

/**
 * Delete plugin setting.
 *
 * @param string $setting - Setting name.
 */
function wfcm_delete_setting( $setting ) {
	if ( class_exists( 'WFCM_Settings' ) ) {
		WFCM_Settings::delete_setting( $setting );
	}
}

/**
 * Get site plugin directories.
 *
 * @return array
 */
function wfcm_get_site_plugins() {
	return array_map( 'dirname', array_keys( get_plugins() ) ); // Get plugin directories.
}

/**
 * Get site themes.
 *
 * @return array
 */
function wfcm_get_site_themes() {
	return array_keys( wp_get_themes() ); // Get themes.
}

/**
 * Initial Site Content Setup.
 *
 * Add plugins and themes to site content setting of the plugin.
 */
function wfcm_set_site_content() {
	// Get site plugins options.
	$site_content = wfcm_get_setting( WFCM_Settings::$site_content, false );

	// Initiate the site content option.
	if ( false === $site_content ) {
		// New stdClass object.
		$site_content = new stdClass();

		$plugins               = array_map( 'strtolower', wfcm_get_site_plugins() );
		$site_content->plugins = $plugins;

		foreach ( $plugins as $plugin ) {
			$site_content->skip_plugins[ $plugin ] = 'init';
		}

		$themes               = array_map( 'strtolower', wfcm_get_site_themes() );
		$site_content->themes = $themes;

		foreach ( $themes as $theme ) {
			$site_content->skip_themes[ $theme ] = 'init';
		}

		// Save site content.
		wfcm_save_setting( WFCM_Settings::$site_content, $site_content );
	}
}

/**
 * Add plugin(s) to site content plugins list.
 *
 * @param string $plugin - (Optional) Plugin directory name.
 */
function wfcm_add_site_plugin( $plugin = '' ) {
	WFCM_Settings::set_site_content( 'plugins', $plugin );
}

/**
 * Add theme(s) to site content themes list.
 *
 * @param string $theme - (Optional) Theme name.
 */
function wfcm_add_site_theme( $theme = '' ) {
	WFCM_Settings::set_site_content( 'themes', $theme );
}

/**
 * Remove plugin from site content plugins list.
 *
 * @param string $plugin - Plugin directory.
 */
function wfcm_remove_site_plugin( $plugin ) {
	WFCM_Settings::remove_site_content( 'plugins', $plugin );
}

/**
 * Remove theme from site content themes list.
 *
 * @param string $theme - Theme directory.
 */
function wfcm_remove_site_theme( $theme ) {
	WFCM_Settings::remove_site_content( 'themes', $theme );
}

/**
 * Skip plugin in the next file changes scan.
 *
 * @param string $plugin  - Plugin directory.
 * @param string $context - Context of the change, i.e., update or uninstall.
 */
function wfcm_skip_plugin_scan( $plugin, $context ) {
	WFCM_Settings::set_skip_site_content( 'plugins', $plugin, $context );
}

/**
 * Skip theme in the next file changes scan.
 *
 * @param string $theme   - Theme directory.
 * @param string $context - Context of the change, i.e., update or uninstall.
 */
function wfcm_skip_theme_scan( $theme, $context ) {
	WFCM_Settings::set_skip_site_content( 'themes', $theme, $context );
}

/**
 * Returns the instance of file changes montior.
 *
 * @return WFCM_Monitor
 */
function wfcm_get_monitor() {
	return WFCM_Monitor::get_instance();
}

/**
 * Create a new event.
 *
 * @param string $event_type - Event: added, modified, deleted.
 * @param string $file - File.
 * @param string $file_hash - File hash.
 * @param string $origin File event origin.
 */
function wfcm_create_event( $event_type, $file, $file_hash, $origin ) {

	// Create the content object.
	$content = (object) array(
		'file' => $file,
		'hash' => $file_hash,
	);

	$event = new WFCM_Event_File();
	$event->set_event_file_path( $file );  // Set event title.
	$event->set_event_type( $event_type ); // Set event type.
	$event->set_event_content( $content ); // Set event content.
	$event->set_event_origin( $origin );

	if ( 'wp.org' === $origin ) {

		$event_context = esc_html__( 'Core file', 'website-file-changes-monitor' );
		if ( 'added' === $event_type ) {
			$event_context = esc_html__( 'Unexpected file in core directory', 'website-file-changes-monitor' );
		} elseif ( 'modified' === $event_type ) {
			$event_context = esc_html__( 'Core file modification', 'website-file-changes-monitor' );
		}

		$event->set_event_context( $event_context );
	}

	return $event->save();
}

/**
 * Create a new directory event.
 *
 * @param string $event_type    - Event: added, modified, deleted.
 * @param string $directory     - Directory.
 * @param array  $content       - Array of directory contents.
 * @param string $event_context - (Optional) Event context.
 */
function wfcm_create_directory_event( $event_type, $directory, $content, $event_context = '' ) {

	$event = new WFCM_Event_Directory();
	$event->set_event_file_path( $directory );  // Set event title.
	$event->set_event_type( $event_type ); // Set event type.
	$event->set_event_content( $content ); // Set event content.
	$event->set_event_origin( 'local' );

	// Check for content type.
	if ( $event_context ) {
		$event->set_event_context( $event_context );
	}

	$event->save();
}

/**
 * Get events.
 *
 * @param array $args - Array of query arguments.
 *
 * @return array|object
 * @throws Exception
 */
function wfcm_get_events( $args ) {
	$query = WFCM_Database_DB_Data_Store::get_file_events( $args );
	return $query;
}

/**
 * Get event object.
 *
 * @param int|WP_Post $event - ID or WP_Post object of an event.
 *
 * @return WFCM_Event|bool
 * @throws Exception
 */
function wfcm_get_event( $event ) {
	// Get event id.
	if ( is_numeric( $event ) ) {
		$event_id = $event;
	} elseif ( $event instanceof WP_Post ) {
		$event_id = $event->ID;
	} elseif ( ! empty( $event->ID ) ) {
		$event_id = $event->ID;
	} else {
		return false;
	}

	// Get event content type.
	$event = WFCM_Database_DB_Data_Store::get_file_event( $event_id );

	if ( $event ) {
		return $event;
	}

	return false;
}

/**
 * Get events for JS.
 *
 * Returns an array of objects with these properties:
 *   - id: Event id.
 *   - path: Event content path.
 *   - filename: Event file name.
 *   - content: Event details (including hash).
 *   - contentType: Event content type.
 *   - eventContext: Event context.
 *   - checked: Event checked status.
 *   - type: Event type.
 *   - dateTime: Event time.
 *   - origin: Event origin.
 *   - type: Event type.
 *
 * @param WFCM_Event[] $events
 * @return array
 */
function wfcm_get_events_for_js( $events ) {
	$js_events = array();
	
	if ( ! empty( $events ) && is_array( $events ) ) {
		$datetime_format = str_replace( array( '.$$$', '&\n\b\s\p;A' ), '', wfcm_get_datetime_format() );

		foreach ( $events as $event ) {
			$content_type    = $event->get_event_content_type();
			$event_context   = $event->get_event_context();
			$event_date      = $event->get_event_date();
			$date_obj        = \DateTime::createFromFormat( 'Y-m-d H:i:s', $event_date );
			$date_str        = $date_obj ? $date_obj->format( $datetime_format ) : '';

			$js_events[] = (object) array(
				'id'           => $event->get_event_id(),
				'path'         => trailingslashit( dirname( $event->get_event_file_path() ) ),
				'filename'     => basename( $event->get_event_file_path() ),
				'content'      => unserialize( $event->get_event_content() ),
				'contentType'  => ucwords( $content_type ),
				'eventContext' => $event_context,
				'checked'      => false,
				'dateTime'     => $date_str,
				'origin'       => $event->get_event_origin(),
				'type'         => $event->get_event_type()
			);
		}
	}

	return $js_events;
}

/**
 * Install WFCM.
 *
 * Install routine that executes on every plugin update.
 */
function wfcm_install() {
	// Installation errors.
	$errors = false;

	// Check for multisite.
	if ( is_multisite() ) {
		/*
		 * We install and activate the plugin from couple of plugin screens in network admin using AJAX call. Function
		 * is_network_admin returns false during an AJAX request. There is no solution for this in WordPress itself so
		 * we used the workaround below.
		 */
		if (defined('DOING_AJAX') && DOING_AJAX && is_multisite() && preg_match('#^'.network_admin_url().'#i',$_SERVER['HTTP_REFERER'])) {
			define('WP_NETWORK_ADMIN',true);
		}

		if (! is_network_admin() ) {
			if ( is_super_admin() ) {
				$errors = esc_html__( 'The Website File Changes Monitor plugin is a multisite network tool, so it has to be activated at network level.', 'website-file-changes-monitor' );
				$errors .= '<br />';
				$errors .= '<a href="javascript:;" onclick="window.top.location.href=\'' . esc_url( network_admin_url( 'plugins.php' ) ) . '\'">' . esc_html__( 'Redirect me to the network dashboard.', 'website-file-changes-monitor' ) . '</a> ';
			} else {
				$errors = esc_html__( 'The Website File Changes Monitor plugin is a multisite network tool, so it has to be activated at network level.', 'website-file-changes-monitor' );
				$errors .= '<br />';
				$errors .= esc_html__( 'Please contact your multisite administrator.', 'website-file-changes-monitor' );
			}
		}
	}

	if ( $errors ) :
		?>
		<html>
			<head><style>body{margin:0;}.warn-icon-tri{top:7px;left:5px;position:absolute;border-left:16px solid #FFF;border-right:16px solid #FFF;border-bottom:28px solid #C33;height:3px;width:4px}.warn-icon-chr{top:10px;left:18px;position:absolute;color:#FFF;font:26px Georgia}.warn-icon-cir{top:4px;left:0;position:absolute;overflow:hidden;border:6px solid #FFF;border-radius:32px;width:34px;height:34px}.warn-wrap{position:relative;font-size:13px;font-family:sans-serif;padding:6px 48px;line-height:1.4;}</style></head>
			<body><div class="warn-wrap"><div class="warn-icon-tri"></div><div class="warn-icon-chr">!</div><div class="warn-icon-cir"></div><span><?php echo $errors; // @codingStandardsIgnoreLine ?></span></div></body>
		</html>
		<?php
		die( 1 );
	endif;

	// WSAL plugins.
	$wsal_plugins = array( 'wp-security-audit-log/wp-security-audit-log.php', 'wp-security-audit-log-premium/wp-security-audit-log.php', 'WP-Security-Audit-Log-Premium/wp-security-audit-log.php' );

	// Only run this when installing for the first time.
	if ( ! get_option( WFCM_OPT_PREFIX . 'version', false ) ) {
		foreach ( $wsal_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {

				// Get instance of WSAL.
				$wsal = WpSecurityAuditLog::GetInstance();

				// Set excluded post types in WSAL.
				$excluded_cpts   = $wsal->GetGlobalSetting( 'custom-post-types', '' );
				$excluded_cpts   = explode( ',', $excluded_cpts );
				$excluded_cpts[] = 'wfcm_file_event';
				$wsal->settings()->set_excluded_post_types( $excluded_cpts );
			}
		}
	}

	if ( defined( 'WSAL_VERSION' ) && ( version_compare ( '4.1.2', WSAL_VERSION ) ) ) {
		update_site_option( 'wfcm_update_wsal_notice', true );
	}

	update_option( WFCM_OPT_PREFIX . 'version', wfcm_instance()->version );
	wfcm_set_site_content();

	WFCM_Database_DB_Data_Store::create_database_table();

	// Redirect option.
	add_option( WFCM_OPT_PREFIX . 'redirect-on-activate', true );
}

/**
 * Returns site server directories.
 *
 * @param string $context - Context of the directories.
 * @return array
 */
function wfcm_get_server_directories( $context = '' ) {
	$wp_directories = array();

	// Get WP uploads directory.
	$wp_uploads  = wp_upload_dir();
	$uploads_dir = $wp_uploads['basedir'];

	if ( 'display' === $context ) {
		$wp_directories = array(
			'root'           => __( 'WordPress core files (files in the root, WP Admin directory - /wp-admin/ and WP Includes directory - /wp-includes/)', 'website-file-changes-monitor' ),
			get_theme_root() => __( 'Themes directory (/wp-content/themes/)', 'website-file-changes-monitor' ),
			WP_PLUGIN_DIR    => __( 'Plugins directory (/wp-content/plugins/)', 'website-file-changes-monitor' ),
			$uploads_dir     => __( 'Uploads directory (/wp-content/uploads/)', 'website-file-changes-monitor' ),
			WP_CONTENT_DIR   => __( '/wp-content/ directory (other than the plugins, themes & upload directories)', 'website-file-changes-monitor' ),
		);

		if ( is_multisite() ) {
			// Upload directories of subsites.
			$wp_directories[ $uploads_dir . '/sites' ] = __( 'Uploads directory of all sub sites on this network (/wp-content/sites/*)', 'website-file-changes-monitor' );
		}
	} else {
		// Server directories.
		$wp_directories = array(
			'',               // Root directory.
			get_theme_root(), // Themes.
			WP_PLUGIN_DIR,    // Plugins.
			$uploads_dir,     // Uploads.
			WP_CONTENT_DIR,   // wp-content.
		);
	}

	// Prepare directories path.
	foreach ( $wp_directories as $index => $server_dir ) {
		if ( 'display' === $context && false !== strpos( $index, ABSPATH ) ) {
			unset( $wp_directories[ $index ] );
			$index = untrailingslashit( $index );
			$index = wfcm_get_server_directory( $index );
		} else {
			$server_dir = untrailingslashit( $server_dir );
			$server_dir = wfcm_get_server_directory( $server_dir );
		}

		$wp_directories[ $index ] = $server_dir;
	}

	return $wp_directories;
}

/**
 * Returns a WP directory without ABSPATH.
 *
 * @param string $directory - Directory.
 * @return string
 */
function wfcm_get_server_directory( $directory ) {
	return preg_replace( '/^' . preg_quote( ABSPATH, '/' ) . '/', '', $directory );
}

/**
 * Return the datetime format according the selected format
 * of the website.
 *
 * @return string
 */
function wfcm_get_datetime_format() {
	$date_time_format = wfcm_get_date_format() . ' ' . wfcm_get_time_format();
	$wp_time_format   = get_option( 'time_format' ); // WP time format.

	// Check if the time format does not have seconds.
	if ( stripos( $wp_time_format, 's' ) === false ) {
		if ( stripos( $wp_time_format, '.v' ) !== false ) {
			$date_time_format = str_replace( '.v', '', $date_time_format );
		}

		$date_time_format .= ':s'; // Add seconds to time format.
		$date_time_format .= '.$$$'; // Add milliseconds to time format.
	} else {
		// Check if the time format does have milliseconds.
		if ( stripos( $wp_time_format, '.v' ) !== false ) {
			$date_time_format = str_replace( '.v', '.$$$', $date_time_format );
		} else {
			$date_time_format .= '.$$$';
		}
	}

	if ( stripos( $wp_time_format, 'A' ) !== false ) {
		$date_time_format .= '&\n\b\s\p;A';
	}

	return $date_time_format;
}

/**
 * Date Format from WordPress general settings.
 *
 * @return string
 */
function wfcm_get_date_format() {
	$wp_date_format = get_option( 'date_format' );
	$search         = array( 'F', 'M', 'n', 'j', ' ', '/', 'y', 'S', ',', 'l', 'D' );
	$replace        = array( 'm', 'm', 'm', 'd', '-', '-', 'Y', '', '', '', '' );
	return str_replace( $search, $replace, $wp_date_format );
}

/**
 * Time Format from WordPress general settings.
 *
 * @return string
 */
function wfcm_get_time_format() {
	$wp_time_format = get_option( 'time_format' );
	$search         = array( 'a', 'A', 'T', ' ' );
	$replace        = array( '', '', '', '' );
	return str_replace( $search, $replace, $wp_time_format );
}

/**
 * @return bool True is current WordPress time format is an AM/PM
 */
function wfcm_is_time_format_am_pm() {
	return (1 === preg_match('/[aA]$/', get_option( 'time_format' ) ) );
}

/**
 * Send file changes email.
 *
 * @param array $scan_changes_count - Array of changes count.
 *
 * @return bool
 */
function wfcm_send_changes_email( $scan_changes_count ) {
	$result_email = new WFCM_Scan_Results_Email( $scan_changes_count );
	return $result_email->send();
}

/**
 * Write data to log file in the uploads directory.
 *
 * @param string $filename - File name.
 * @param string $content  - Contents of the file.
 * @param bool   $override - (Optional) True if overriding file contents.
 * @return bool
 */
function wfcm_write_to_file( $filename, $content, $override = false ) {
	global $wp_filesystem;
	require_once ABSPATH . 'wp-admin/includes/file.php';
	WP_Filesystem();

	$filepath = WFCM_UPLOADS_DIR . $filename;
	$dir_path = dirname( $filepath );
	$result   = false;

	if ( ! is_dir( $dir_path ) ) {
		wp_mkdir_p( $dir_path );
	}

	if ( ! $wp_filesystem->exists( $filepath ) || $override ) {
		$result = $wp_filesystem->put_contents( $filepath, $content );
	} else {
		$existing_content = $wp_filesystem->get_contents( $filepath );
		$result           = $wp_filesystem->put_contents( $filepath, $existing_content . $content );
	}

	return $result;
}

/**
 * Send file changes scan failure email.
 *
 * @param bool   $send_mail bool if we want to sent mail or not.
 * @param string $error_type a type of error if not 'generic'.
 * @return bool
 */
function wfcm_send_scan_fail_email( $send_mail = true, $error_type = 'generic' ) {
	$home_url = home_url();
	$safe_url = str_replace( array( 'http://', 'https://' ), '', $home_url );
	$subject  = sprintf(
		/* Translators: %s: Home URL */
		esc_html__( 'The file integrity scan on %1$s was halted.', 'website-file-changes-monitor' ),
		$safe_url
	);
	$body = '<p>' . esc_html__( 'Hello,', 'website-file-changes-monitor' ) . '</p>';

	// decide which error message to use.
	if ( 'timeout' === $error_type ) {
		$body .= '<p>' . sprintf(
			/* Translators: %s: Home URL */
			esc_html__( 'The file integrity scan on the website %1$s was halted because it took more than the maximum scan time (3 minutes) to complete.', 'website-file-changes-monitor' ),
			$safe_url
		) . '</p>';
	} else {
		$body .= '<p>' . sprintf(
			/* Translators: %s: Home URL */
			esc_html__( 'The file integrity scan on the website %1$s was halted due to an unexpected issue.', 'website-file-changes-monitor' ),
			$safe_url
		) . '</p>';
	}

	$body .= '<p>' . sprintf(
		/* Translators: Opening and closing link fragments for mailto */
		esc_html__( 'Please contact our support at %1$ssupport@wpwhitesecurity.com%2$s to help you with this issue.', 'website-file-changes-monitor' ),
		'<a href="mailto:support@wpwhitesecurity.com">',
		'</a>'
	) . '</p>';
	$body .= '<p>' . sprintf(
		/* Translators: Opening and closing link fragments */
		esc_html__( 'Email sent by the %1$sWebsite File Changes Monitor plugin%2$s.', 'website-file-changes-monitor' ),
		'<a href="https://wordpress.org/plugins/website-file-changes-monitor/" target="_blank">',
		'</a>'
	) . '</p>';

	if ( $send_mail ) {
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
	}

	return $send_mail;
}

/**
 * Write data to log file.
 *
 * @param string $data     - Data to write to file.
 * @param bool   $override - Set to true if overriding the file.
 * @return bool
 */
function wfcm_write_to_log( $data, $override = false ) {
	if ( ! is_dir( WFCM_UPLOADS_DIR . WFCM_LOGS_DIR ) ) {
		wfcm_create_index_file( WFCM_LOGS_DIR );
		wfcm_create_htaccess_file( WFCM_LOGS_DIR );
	}

	return wfcm_write_to_file( trailingslashit( WFCM_LOGS_DIR ) . 'wfcm-debug.log', $data, $override );
}

/**
 * Create an index.php file, if none exists, in order to
 * avoid directory listing in the specified directory.
 *
 * @param string $dir_path - Directory Path.
 * @return bool
 */
function wfcm_create_index_file( $dir_path ) {
	return wfcm_write_to_file( trailingslashit( $dir_path ) . 'index.php', '<?php // Silence is golden' );
}

/**
 * Create an .htaccess file, if none exists, in order to
 * block access to directory listing in the specified directory.
 *
 * @param string $dir_path - Directory Path.
 * @return bool
 */
function wfcm_create_htaccess_file( $dir_path ) {
	return wfcm_write_to_file( trailingslashit( $dir_path ) . '.htaccess', 'Deny from all' );
}

/**
 * Returns the timestamp for log files.
 *
 * @return string
 */
function wfcm_get_log_timestamp() {
	return '[' . date( 'd-M-Y H:i:s' ) . ' UTC]';
}

/**
 * Returns a list of scan frequency options.
 *
 * Also runs filter <code>wfcm_file_changes_scan_frequency</code>.
 *
 * @return array
 * @since 1.7.0
 */
function wfcm_get_frequency_options() {
	return apply_filters(
			'wfcm_file_changes_scan_frequency',
			array(
					'hourly'  => __( 'Hourly', 'website-file-changes-monitor' ),
					'daily'   => __( 'Daily', 'website-file-changes-monitor' ),
					'weekly'  => __( 'Weekly', 'website-file-changes-monitor' )
			)
	);
}

/**
 * Returns a list of options to display hour of the day in the scan frequency settings.
 *
 * @return array
 * @since 1.7.0
 */
function wfcm_get_hours_options() {
	return array(
		'00' => _x( '00:00', 'a time string representing midnight', 'website-file-changes-monitor' ),
		'01' => _x( '01:00', 'a time string of hour followed by minutes', 'website-file-changes-monitor' ),
		'02' => _x( '02:00', 'a time string of hour followed by minutes', 'website-file-changes-monitor' ),
		'03' => _x( '03:00', 'a time string of hour followed by minutes', 'website-file-changes-monitor' ),
		'04' => _x( '04:00', 'a time string of hour followed by minutes', 'website-file-changes-monitor' ),
		'05' => _x( '05:00', 'a time string of hour followed by minutes', 'website-file-changes-monitor' ),
		'06' => _x( '06:00', 'a time string of hour followed by minutes', 'website-file-changes-monitor' ),
		'07' => _x( '07:00', 'a time string of hour followed by minutes', 'website-file-changes-monitor' ),
		'08' => _x( '08:00', 'a time string of hour followed by minutes', 'website-file-changes-monitor' ),
		'09' => _x( '09:00', 'a time string of hour followed by minutes', 'website-file-changes-monitor' ),
		'10' => _x( '10:00', 'a time string of hour followed by minutes', 'website-file-changes-monitor' ),
		'11' => _x( '11:00', 'a time string of hour followed by minutes', 'website-file-changes-monitor' ),
		'12' => _x( '12:00', 'a time string representing midday', 'website-file-changes-monitor' ),
		'13' => _x( '13:00', 'a time string of hour followed by minutes', 'website-file-changes-monitor' ),
		'14' => _x( '14:00', 'a time string of hour followed by minutes', 'website-file-changes-monitor' ),
		'15' => _x( '15:00', 'a time string of hour followed by minutes', 'website-file-changes-monitor' ),
		'16' => _x( '16:00', 'a time string of hour followed by minutes', 'website-file-changes-monitor' ),
		'17' => _x( '17:00', 'a time string of hour followed by minutes', 'website-file-changes-monitor' ),
		'18' => _x( '18:00', 'a time string of hour followed by minutes', 'website-file-changes-monitor' ),
		'19' => _x( '19:00', 'a time string of hour followed by minutes', 'website-file-changes-monitor' ),
		'20' => _x( '20:00', 'a time string of hour followed by minutes', 'website-file-changes-monitor' ),
		'21' => _x( '21:00', 'a time string of hour followed by minutes', 'website-file-changes-monitor' ),
		'22' => _x( '22:00', 'a time string of hour followed by minutes', 'website-file-changes-monitor' ),
		'23' => _x( '23:00', 'a time string of hour followed by minutes', 'website-file-changes-monitor' )
	);
}

/**
 * Returns a list of options to display days of the week in the scan frequency settings.
 *
 * @return array
 * @since 1.7.0
 */
function wfcm_get_days_options() {
	return array(
			'7' => _x( 'Sunday', 'the last day of the week and last day of the weekend', 'website-file-changes-monitor' ),
			'1' => _x( 'Monday', 'the first day of the week and first day of the work week', 'website-file-changes-monitor' ),
			'2' => _x( 'Tuesday', 'the second day of the week', 'website-file-changes-monitor' ),
			'3' => _x( 'Wednesday', 'the third day of the week', 'website-file-changes-monitor' ),
			'4' => _x( 'Thursday', 'the fourth day of the week', 'website-file-changes-monitor' ),
			'5' => _x( 'Friday', 'the fith day of the week, last day of the work week', 'website-file-changes-monitor' ),
			'6' => _x( 'Saturday', 'the first day of the weekend', 'website-file-changes-monitor' ),
	);
}

function wfcm_convert_assoc_array_to_select_options( $options ) {
	$result = [];
	if ( ! empty( $options ) && is_array( $options ) ) {
		foreach ( $options as $value => $label ) {
			array_push( $result, [
					'value' => $value,
					'label' => $label
			] );
		}
	}
	return $result;
}

/**
 * Retrives all stored entries and returns all parts as a single array for further processing.
 *
 * @param  string $directory Items we wish to grab.
 * @return array             All found files for this directory.
 */
function wfcm_get_all_stored_files( $directory ) {
	global $wpdb;
	$option_name = 'wfcm_'.$directory . '#_%';

	if ( strpos( $option_name, 'comparison' ) !== false ) {
		$sql = $wpdb->prepare(
			"SELECT option_value FROM $wpdb->options
			 WHERE option_name LIKE %s ESCAPE '#'",
			$option_name
		);
	} else {
		$sql = $wpdb->prepare(
			"SELECT option_value FROM $wpdb->options
			WHERE option_name LIKE %s ESCAPE '#'
			AND option_name not like '%comparison%';",
			$option_name
		);
	}

	$bg_jobs = $wpdb->get_results( $sql, ARRAY_A );
	
	$all_files = [];
	
	foreach ( $bg_jobs as $index => $data ) {
		if ( $data[ 'option_value' ] ) {
			$all_files = array_merge( $all_files, unserialize( $data[ 'option_value' ] ) );
		}
	}
	
  return $all_files;
}

/**
 * Retrives all option names which hold files in the wp_options table for a given directory.
 *
 * @param  string $directory Items we wish to grab.
 * @return array             All populated options for this directory.
 */
function wfcm_get_file_storage_option_names( $directory ) {
	global $wpdb;
	$option_name       = 'wfcm_' . $directory . '#_%';

	if ( strpos( $option_name, 'comparison' ) !== false ) {
		$sql = $wpdb->prepare(
			"SELECT option_name FROM $wpdb->options
			 WHERE option_name LIKE %s ESCAPE '#'",
			$option_name
		);		
	} else {
		$sql = $wpdb->prepare(
			"SELECT option_name FROM $wpdb->options
			WHERE option_name LIKE %s ESCAPE '#'
			AND option_name not like '%comparison%';",
			$option_name
		);
	}

	return $wpdb->get_results( $sql, ARRAY_A );
}
