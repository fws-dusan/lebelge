<?php
/**
 * Admin File Changes View.
 *
 * @package wfcm
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Admin file changes view class.
 */
class WFCM_Admin_File_Changes {

	/**
	 * Admin messages.
	 *
	 * @var array
	 */
	private static $messages = array();

	/**
	 * Allowed HTML.
	 *
	 * @var array
	 */
	private static $allowed_html = array(
			'a'      => array(
					'href'       => array(),
					'target'     => array(),
					'data-nonce' => array(),
			),
			'strong' => array(),
			'ul'     => array(),
			'li'     => array(),
			'p'      => array(),
	);

	/**
	 * Page tabs.
	 *
	 * @var array
	 */
	private static $tabs = array();

	/**
	 * Page View.
	 */
	public static function output() {
		self::add_messages(); // Add notifications to the view.
		// setup a filter to add counts to the nav tabs at generation time.
		add_filter( 'wfcm_admin_file_changes_page_tabs', 'WFCM_Admin_File_Changes::append_count_for_tabs' );
		self::set_tabs();

		$wp_version        = get_bloginfo( 'version' );
		$wfcm_dependencies = array();
		$datetime_format   = get_option( 'date_format' ) . ' ' . get_option( 'time_format' );
		$last_scan_time    = wfcm_get_setting( 'last-scan-timestamp', false );
		$last_scan_time    = $last_scan_time + ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS );
		$last_scan_time    = date( $datetime_format, $last_scan_time );

		wp_enqueue_style(
				'wfcm-file-changes-styles',
				WFCM_BASE_URL . 'assets/css/dist/build.file-changes.css',
				array(),
				( defined( 'WP_DEBUG' ) && true === WP_DEBUG ) ? filemtime( WFCM_BASE_DIR . 'assets/css/dist/build.file-changes.css' ) : WFCM_VERSION
		);

		// For WordPress versions earlier than 5.0, enqueue react and react-dom from the vendors directory.
		if ( version_compare( $wp_version, '5.0', '<' ) ) {
			wp_enqueue_script(
					'wfcm-react',
					WFCM_BASE_URL . 'assets/js/dist/vendors/react.min.js',
					array(),
					'16.6.3',
					true
			);

			wp_enqueue_script(
					'wfcm-react-dom',
					WFCM_BASE_URL . 'assets/js/dist/vendors/react-dom.min.js',
					array(),
					'16.6.3',
					true
			);

			$wfcm_dependencies = array( 'wfcm-react', 'wfcm-react-dom' );
		} else {
			// Otherwise enqueue WordPress' react library.
			$wfcm_dependencies = array( 'wp-element' );
		}

		wp_register_script(
				'wfcm-file-changes',
				WFCM_BASE_URL . 'assets/js/dist/file-changes.js',
				$wfcm_dependencies,
				( defined( 'WP_DEBUG' ) && true === WP_DEBUG ) ? filemtime( WFCM_BASE_DIR . 'assets/js/dist/file-changes.js' ) : WFCM_VERSION,
				true
		);
		$migrated = wfcm_get_setting( 'sha256-hashing', false );
		if ( ! $migrated ) {
			if ( 'no' === wfcm_get_setting( 'is-initial-scan-0', 'yes' ) ) {
				$sha256_migrated = false;
			} else {
				$sha256_migrated = true;
			}
		} else {
			$sha256_migrated = true;
		}


		$wfcm               = wfcm_get_monitor();
		$currently_scanning = $wfcm->get_current_number_of_active_bg_processes();

		$script_params = array(
				'security'       => wp_create_nonce( 'wp_rest' ),
				'fileEvents'     => array(
						'allowInCore'      => esc_url_raw( rest_url( WFCM_REST_NAMESPACE . WFCM_REST_API::$allow_in_core ) ),
						'delete'           => esc_url_raw( rest_url( WFCM_REST_NAMESPACE . WFCM_REST_API::$events_base ) ),
						'get'              => esc_url_raw( rest_url( WFCM_REST_NAMESPACE . WFCM_REST_API::$events_base ) ),
						'mark_all_read'    => esc_url_raw( rest_url( WFCM_REST_NAMESPACE . WFCM_REST_API::$mark_all_read_base ) ),
						'delete_in_folder' => esc_url_raw( rest_url( WFCM_REST_NAMESPACE . WFCM_REST_API::$mark_read_dir ) ),
				),
				'pageHead'       => __( 'Website File Changes Monitor', 'website-file-changes-monitor' ),
				'pagination'     => array(
						'fileChanges'  => __( 'file changes', 'website-file-changes-monitor' ),
						'firstPage'    => __( 'First page', 'website-file-changes-monitor' ),
						'previousPage' => __( 'Previous page', 'website-file-changes-monitor' ),
						'currentPage'  => __( 'Current page', 'website-file-changes-monitor' ),
						'nextPage'     => __( 'Next page', 'website-file-changes-monitor' ),
						'lastPage'     => __( 'Last page', 'website-file-changes-monitor' ),
				),
				'labels'         => array(
						'addedFiles'    => __( 'Added files', 'website-file-changes-monitor' ),
						'deletedFiles'  => __( 'Deleted files', 'website-file-changes-monitor' ),
						'modifiedFiles' => __( 'Modified files', 'website-file-changes-monitor' ),
				),
				'bulkActions'    => array(
						'screenReader' => __( 'Select bulk action', 'website-file-changes-monitor' ),
						'bulkActions'  => __( 'Bulk Actions', 'website-file-changes-monitor' ),
						'markAsRead'   => __( 'Mark as read', 'website-file-changes-monitor' ),
						'exclude'      => __( 'Exclude', 'website-file-changes-monitor' ),
						'apply'        => __( 'Apply', 'website-file-changes-monitor' ),
				),
				'showItems'      => array(
						'added'    => (int) wfcm_get_setting( 'added-per-page', false ),
						'modified' => (int) wfcm_get_setting( 'modified-per-page', false ),
						'deleted'  => (int) wfcm_get_setting( 'deleted-per-page', false ),
				),
				'table' => array(
						'actions'                      => __( 'Actions', 'website-file-changes-monitor' ),
						'allowDirInCore'               => __( 'Add as allowed directory', 'website-file-changes-monitor' ),
						'allowDirInCoreTooltip'        => __( 'Add all the files in this directory to the list of allowed non-core WordPress files in the website root and core sub directories.', 'website-file-changes-monitor' ),
						'coreFileTooltip'              => __( 'The plugin identified this file in your WordPress core directories. This does not mean it is malicious or something is wrong. However, by default such files are not part of WordPress core. Please check this file. If you are aware about it \'mark it as read\' so the plugin won\'t alert you again about it, unless there are changes in it or it is deleted.', 'website-file-changes-monitor' ),
						'dateTime'                     => __( 'Date', 'website-file-changes-monitor' ),
						'excludeFile'                  => __( 'Exclude file from scans', 'website-file-changes-monitor' ),
						'excludeFileTooltip'           => __( 'Exclude this file from the file integrity scan.', 'website-file-changes-monitor' ),
						'excludeDir'                   => __( 'Exclude directory from scans', 'website-file-changes-monitor' ),
						'excludeDirTooltip'            => __( 'Exclude all the files in this directory from the file integrity scan.', 'website-file-changes-monitor' ),
						'markAsRead'                   => __( 'Mark as read', 'website-file-changes-monitor' ),
						'markAsReadTooltip'            => __( 'Mark this change as read. If you mark an unexpected WordPress core file change as read, that file will be added to the list of allowed files.', 'website-file-changes-monitor' ),
						'name'                         => __( 'Name', 'website-file-changes-monitor' ),
						'noEvents'                     => __( 'No file changes detected!', 'website-file-changes-monitor' ),
						'path'                         => __( 'Path', 'website-file-changes-monitor' ),
						'showListOfFiles'              => __( 'Show list of files', 'website-file-changes-monitor' ),
						'fileListTooltip'              => __( 'See the complete list of file changes grouped under this notice.', 'website-file-changes-monitor' ),
						'type'                         => __( 'Type', 'website-file-changes-monitor' ),
						'canBeChangedInPluginSettings' => __( 'You can always change this from the plugin\'s settings.', 'website-file-changes-monitor' ),
						'yes'                          => __( 'Yes' ),
						'no'                           => __( 'No' ),
						'allowDirInCoreConfirmPopup'   => [
								'title'   => esc_html__( 'Adding an allowed directory', 'website-file-changes-monitor' ),
								'message' => esc_html__( 'When directories are added to the allowed list, the plugin will consider all the files in them as part of your website’s WordPress core. However, it will still scan them during the normal file integrity scans. So if files are added to this directory, or they are modified or deleted, the plugin will notify you about such change. If you do not want to be alerted of such changes about this directory, exclude it from the scan.', 'website-file-changes-monitor' )
						]
				),
				'monitor'        => array(
						'start' => esc_url_raw( rest_url( WFCM_REST_NAMESPACE . WFCM_REST_API::$monitor_base . '/start' ) ),
						'stop'  => esc_url_raw( rest_url( WFCM_REST_NAMESPACE . WFCM_REST_API::$monitor_base . '/stop' ) ),
				),
				'scanModal'      => array(
						'logoSrc'                       => WFCM_BASE_URL . 'assets/img/wfcm-logo.svg',
						'dismiss'                       => wfcm_get_setting( 'dismiss-instant-scan-modal', false ),
						'adminAjax'                     => admin_url( 'admin-ajax.php' ),
						'headingComplete'               => __( 'Generating the files\' fingerprints', 'website-file-changes-monitor' ),
						'scanNow'                       => __( 'Generate the files\' fingerprints', 'website-file-changes-monitor' ),
						'scanNowButton'                 => __( 'Generate fingerprints now', 'website-file-changes-monitor' ),
						'scanDismiss'                   => __( 'Wait for the first scan', 'website-file-changes-monitor' ),
						'scanning'                      => __( 'Scanning...', 'website-file-changes-monitor' ),
						'scanComplete'                  => __( 'Scan complete!', 'website-file-changes-monitor' ),
						'scanFailed'                    => __( 'Scan failed!', 'website-file-changes-monitor' ),
						'ok'                            => __( 'OK', 'website-file-changes-monitor' ),
						'cancel'                        => __( 'Cancel', 'website-file-changes-monitor' ),
						'initialMsg'                    => __( 'The plugin detects file changes by comparing the fingerprints of the files taken at different times. Therefore it first needs to generate the fingerprints of all the files. You can run this process now or wait for it to run when the first file scan is scheduled.', 'website-file-changes-monitor' ),
						'scheduleHelpTxt'               => sprintf(
						/* Translators: 1 - <strong> tag, 2 - a closing </strong> tag. */
								__( '%1$sTip:%2$s You can change the scan schedule and frequency from the plugin settings.', 'website-file-changes-monitor' ),
								'<strong>',
								'</strong>'
						),
						'afterScanMsg'                  => __( 'The first file scan is complete. Now the plugin has the file fingerprints and it will alert you via email when it detect changes.', 'website-file-changes-monitor' ),
						'bgScanMsg'                     => __( 'This process will run in the background. You can continue with the setup of this plugin.', 'website-file-changes-monitor' ),
						'sendTestMail'                  => __( 'Send a test email', 'website-file-changes-monitor' ),
						'emailSentTitle'                => __( 'Test email sent!', 'website-file-changes-monitor' ),
						'emailSending'                  => __( 'Sending...', 'website-file-changes-monitor ' ),
						'sendingFailed'                 => __( 'Failed to send', 'website-file-changes-monitor ' ),
						'emailSent'                     => __( 'Email sent', 'website-file-changes-monitor ' ),
						'emailMsg'                      => __( 'The plugin sends emails when it identifies file changes. Use the <i>Send test email</i> button below to test and confirm the plugin can send emails.', 'website-file-changes-monitor ' )
														   . '<br /><br />'
														   . sprintf(
														   /* Translators: %s Admin email address */
																   __( 'By default the plugin sends emails to the <i>Administrator Email Address</i> (%s) configured in the WordPress settings. You can change this from the plugin settings.', 'website-file-changes-monitor ' ),
																   get_bloginfo( 'admin_email' )
														   ),
						'emailSuccessMsg'               => __( 'Success', 'website-file-changes-monitor' ),
						'exitButton'                    => __( 'Exit', 'website-file-changes-monitor' ),
						'emailSentLine1'                => __( 'If you received the test email everything is setup correctly. You will be notified when the plugin detects file changes.', 'website-file-changes-monitor' ),
						'emailSentLine2'                => __( 'If you have not received an email, please <a href="https://www.wpwhitesecurity.com/support/submit-ticket/" target="_blank">contact us</a> so we can help you troubleshoot the issue.', 'website-file-changes-monitor' ),
						'skip'                          => __( 'Skip', 'website-file-changes-monitor' ),
						'save'                          => __( 'Save', 'website-file-changes-monitor' ),
						'saving'                        => __( 'Saving', 'website-file-changes-monitor' ),
						'frequency'                     => __( 'Frequency', 'website-file-changes-monitor' ),
						'time'                          => __( 'Time', 'website-file-changes-monitor' ),
						'frequencySettingsTitle'        => __( 'Schedule the file changes scans', 'website-file-changes-monitor' ),
						'frequencySettingsMessage'      => __( 'By default, the plugin scans for file changes at 2:00AM every day. You can change both the scan frequency and schedule here or later on from the plugin settings:', 'website-file-changes-monitor' ),
						'frequencySettingsErrorTitle'   => __( 'Scan scheduling failed', 'website-file-changes-monitor' ),
						'frequencySettingsErrorMessage' => __( 'We failed to set the scan frequency and schedule. Don\'t worry, this can be changed in the plugin settings. So please try again later. For now the scan will run at 2:00AM every day.', 'website-file-changes-monitor' ),
						'frequencyOptions'              => wfcm_convert_assoc_array_to_select_options( wfcm_get_frequency_options() ),
						'frequencySettings'             => [
								'frequency' => wfcm_get_setting( 'scan-frequency', 'daily' ),
								'hour'      => wfcm_get_setting( 'scan-hour', '02' ),
								'day'       => wfcm_get_setting( 'scan-day', '1' )
						],
						'hour'                          => __( 'Hour', 'website-file-changes-monitor' ),
						'day'                           => __( 'Day', 'website-file-changes-monitor' ),
						'hoursOptions'                  => wfcm_convert_assoc_array_to_select_options( wfcm_get_hours_options() ),
						'daysOptions'                   => wfcm_convert_assoc_array_to_select_options( wfcm_get_days_options() ),
						'scanHourTitle'                 => __( 'Hour to run the scan time', 'website-file-changes-monitor' ),
						'scanDayTitle'                  => __( 'Day of the week', 'website-file-changes-monitor' ),
						'is_time_format_am_pm'          => wfcm_is_time_format_am_pm(),
						'actionsTitle'                  => __( 'What to do with the plugin\'s findings?', 'website-file-changes-monitor' ),
						'actionsMessage'                => __( 'Once a scan is finished, you can see the file changes the plugin identified. Next to each reported change there are four (sometimes five) icons. The below legend explains what each icon is for.', 'website-file-changes-monitor' ),
						'actionsInfo'                   => array(
								'markAsRead'           => __( 'This is the "Mark as read" icon. Click this icon after you confirm that you know about this file change.', 'website-file-changes-monitor' ),
								'allowDirInCore'       => __( 'This is the "Add as allowed directory" icon. Click this icon to add all the non-WordPress core files in a directory to the list of allowed files in the website\'s root and core sub directories.', 'website-file-changes-monitor' ),
								'excludeFile'          => __( 'This is the "Exclude file" icon. Click this icon to exclude a file from the file integrity scans.', 'website-file-changes-monitor' ),
								'excludeDir'           => __( 'This is the "Exclude directory" icon. Click this icon to exclude all the files in a directory from the file integrity scans.', 'website-file-changes-monitor' ),
								'fileList'             => __( 'This is the "More information / details" icon. When the WordPress core is updated, or a plugin or theme is installed, updated or deleted, for ease of use the plugin groups all the file changes under one change. Click this icon next to the change to see the complete list of file changes.', 'website-file-changes-monitor' ),
								'readMoreLinkAllowed'  => '<a href="https://www.wpwhitesecurity.com/support/kb/allowed-files-directories-root-core/" target="_blank">' . __( 'Read more', 'website-file-changes-monitor' ) . '</a>',
								'readMoreLinkExcluded' => '<a href="https://www.wpwhitesecurity.com/support/kb/excluding-files-directories-wordpress-file-changes-scan/" target="_blank">' . __( 'Read more', 'website-file-changes-monitor' ) . '</a>',
						),
						'next'                          => __( 'Next', 'website-file-changes-monitor' ),
				),
				'migrationModal' => array(
						'migrated'        => $sha256_migrated,
						'migrating'       => __( 'Migrating...', 'website-file-changes-monitor' ),
						'modalLine1'      => __( 'In this update we have changed the hashing algorithm from MD5 to sha256. We have changed it because SHA256 is not prone to hash collisions, making it more secure.', 'website-file-changes-monitor' ),
						'modalLine2'      => __( 'Because of this change the plugin needs to rebuild the files signatures. Click Launch Scan to rebuild the file signatures now.', 'webiste-file-changes-monitor' ),
						'oldClearedLine1' => __( 'The upgrade of file signatures was successful.', 'website-file-changes-monitor' ),
						'upgradeButton'   => __( 'Launch Now', 'website-file-changes-monitor' ),
				),
				'instantScan'    => array(
						'scanNow'             => __( 'Launch scan now', 'website-file-changes-monitor' ),
						'scanning'            => __( 'Scan started.', 'website-file-changes-monitor' ),
						'scanFailed'          => __( 'Scan failed', 'website-file-changes-monitor' ),
						'lastScan'            => ( $currently_scanning > 0 ) ? __( 'Currently remaining areas to scan', 'website-file-changes-monitor' ) : __( 'Last scan', 'website-file-changes-monitor' ),
						'scanningSetState'    => ( $currently_scanning > 0 ) ? true : false,
						'scanningInProgress'  => __( 'File scanning underway...', 'website-file-changes-monitor' ),
						'lastScanTime'        =>  ( $currently_scanning > 0 ) ? $currently_scanning : $last_scan_time,
				),
				'markAllRead'    => array(
						'markNow'               => __( 'Mark all as read', 'website-file-changes-monitor' ),
						'running'               => __( 'Marking as read...', 'website-file-changes-monitor' ),
						'markingAllReadFailed'  => __( 'Marking failed', 'website-file-changes-monitor' ),
						'markingAllReadSuccess' => __( 'Marking complete', 'website-file-changes-monitor' ),
						'markReadButtonMain'    => __( 'Only the {{$type}} file changes notifications', 'website-file-changes-monitor' ),
						'markReadButtonAll'     => __( 'All file changes', 'website-file-changes-monitor' ),
						'markReadModalTitle'    => __( 'Mark all {{$type}} file changes notifications as read', 'website-file-changes-monitor' ),
						'markReadModalMsg'      => __( 'Do you want to mark the {{$type}} file changes notifications as read, or all of the file changes notifications reported at this moment?', 'website-file-changes-monitor' ),
				),
				'dateTimeFormat' => $datetime_format,
				'scanErrorModal' => array(
						'heading' => __( 'Instant scan failed', 'website-file-changes-monitor' ),
						/* Translators: Contact us hyperlink */
						'body'    => sprintf( __( 'Oops! Something went wrong with the scan. Please %s for assistance.', 'website-file-changes-monitor' ), '<a href="https://www.wpwhitesecurity.com/support/?utm_source=plugin&utm_medium=referral&utm_campaign=WFCM&utm_content=help+page" target="_blank">' . __( 'contact us', 'website-file-changes-monitor' ) . '</a>' ),
						'dismiss' => __( 'Ok', 'website-file-changes-monitor' ),
				),
		);

		wp_localize_script(
				'wfcm-file-changes',
				'wfcmFileChanges',
				$script_params
		);

		wp_enqueue_script( 'wfcm-file-changes' );

		// Display notifications of the view.
		self::show_messages();

		require_once trailingslashit( dirname( __FILE__ ) ) . 'views/html-admin-file-changes.php';
	}

	/**
	 * Add specific page messages.
	 */
	public static function add_messages() {
		// Get file limits message setting.
		$admin_notices = wfcm_get_setting( 'admin-notices', array() );

		if ( ! empty( $admin_notices ) ) {

			// clear scan in progress so user can re-scan.
			wfcm_save_setting( 'scan-in-progress', false );

			if ( isset( $admin_notices['previous-scan-fail-generic'] ) && ! empty( $admin_notices['previous-scan-fail-generic'] ) ) {
				$msg = '<p>' . sprintf(
						/* Translators: 1 - WP White Security support hyperlink. 2 - support link closer */
								__( 'We detected that a previous file integrity scan failed due to an unknown reason. Contact us at %1$ssupport@wpwhitesecurity.com%2$s to help you with this issue.', 'website-file-changes-monitor' ),
								'<a href="mailto:support@wpwhitesecurity.com" target="_blank">',
								'</a>'
						) . '</p>';
				self::add_message( 'previous-scan-fail-generic', 'error', $msg );
			}
			if ( isset( $admin_notices['previous-scan-fail-timeout'] ) && ! empty( $admin_notices['previous-scan-fail-timeout'] ) ) {
				if ( isset( $admin_notices['previous-scan-fail-timeout']['time'] ) ) {
					$datetime_format          = get_option( 'date_format' ) . ' ' . get_option( 'time_format' );
					$timestamp                = $admin_notices['previous-scan-fail-timeout']['time'] + ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS );
					$date_and_time_of_failure = date( $datetime_format, $timestamp ) . ' ';
				} else {
					$date_and_time_of_failure = '';
				}
				$msg = '<p>' . sprintf(
						/* Translators: 1 - WP White Security support hyperlink. 2 - support link closer */
								__( 'The last file integrity scan was halted %3$sbecause it took more than maximum scan time (3 minutes) to complete. Contact us at %1$ssupport@wpwhitesecurity.com%2$s to help you with this issue.', 'website-file-changes-monitor' ),
								'<a href="mailto:support@wpwhitesecurity.com" target="_blank">',
								'</a>',
								$date_and_time_of_failure
						) . '</p>';
				self::add_message( 'previous-scan-fail-timeout', 'error', $msg );

			}

			if ( isset( $admin_notices['hashing-upgrade']['upgrade-needed'] ) && $admin_notices['hashing-upgrade']['upgrade-needed'] ) {
				if ( wfcm_get_setting( 'sha256-hashing', false ) ) {
					unset( $admin_notices['hashing-upgrade'] );
					wfcm_save_setting( 'admin-notices', $admin_notices );
				} else {
					$msg = '<p>' . sprintf(
							/* Translators: link fragments. */
									__( 'The plugin changed to use SHA-256 for hashing. You need to delete the old file fingerprints and run a scan to generate new ones with the new hashing algorithm. You can %1$scontact us%2$s for assistance.', 'website-file-changes-monitor' ),
									'<a href="https://www.wpwhitesecurity.com/support/submit-ticket/" target="_blank">',
									'</a>'
							) . '</p>';
					self::add_message( 'hashing-upgrade', 'error', $msg );
				}
			}

			// display the hashing algo missing notice if it's there and still
			// not finding the hash match in available list.
			if ( isset( $admin_notices['hashing-algorith']['sha256-unavailable'] ) && $admin_notices['hashing-algorith']['sha256-unavailable'] ) {
				if ( ! in_array( WFCM_Monitor::HASHING_ALGORITHM, hash_algos(), true ) ) {
					$msg = '<p>' . sprintf(
							/* Translators: link fragments. */
									__( 'The plugin uses SHA-256 for hashing. It seems that this hashing method is not enabled on your website. Please %1$scontact us%2$s for assistance.', 'website-file-changes-monitor' ),
									'<a href="https://www.wpwhitesecurity.com/support/submit-ticket/" target="_blank">',
									'</a>'
							) . '</p>';
					self::add_message( 'hashing-algorith', 'error', $msg );
				}
			}

			if ( isset( $admin_notices['files-limit'] ) && ! empty( $admin_notices['files-limit'] ) ) {
				// Append strong tag to each directory name.
				$dirs = array_reduce(
						$admin_notices['files-limit'],
						function ( $dirs, $dir ) {
							array_push( $dirs, "<li><strong>$dir</strong></li>" );

							return $dirs;
						},
						array()
				);

				$msg = '<p>' . sprintf(
						/* Translators: %s: WP White Security support hyperlink. */
								__( 'The plugin stopped scanning the below directories because they have more than 200,000 files. Please contact %s for assistance.', 'website-file-changes-monitor' ),
								'<a href="mailto:support@wpwhitesecurity.com" target="_blank">' . __( 'our support', 'website-file-changes-monitor' ) . '</a>'
						) . '</p>';
				$msg .= '<ul>' . implode( '', $dirs ) . '</ul>';

				self::add_message( 'files-limit', 'warning', $msg );
			}

			if ( isset( $admin_notices['filesize-limit'] ) && ! empty( $admin_notices['filesize-limit'] ) ) {
				// Append strong tag to each directory name.
				$files = array_reduce(
						$admin_notices['filesize-limit'],
						function ( $files, $file ) {
							// Create nonce for excluding the file.
							$exclude_nonce = wp_create_nonce( 'wfcm-exclude-file-nonce' );
							array_push( $files, "<li><strong>$file</strong> <a href='#wfcm_exclude_large_file' data-nonce='" . esc_attr( $exclude_nonce ) . "'>" . __( 'Exclude file', 'website-file-changes-monitor' ) . "</a></li>" );

							return $files;
						},
						array()
				);

				$max_file_size = (int) wfcm_get_setting( 'scan-file-size' );

				$msg = '<p>' . sprintf(
						/* Translators: %s: Plugin settings hyperlink. */
								__( 'These files are bigger than %sMB and have not been scanned. To scan them increase the file size scan limit from the %s.', 'website-file-changes-monitor' ),
								$max_file_size,
								'<a href="' . add_query_arg( 'page', 'wfcm-settings', admin_url( 'admin.php' ) ) . '">' . __( 'plugin settings', 'website-file-changes-monitor' ) . '</a>'
						) . '</p>';

				$msg .= '<ul>' . implode( '', $files ) . '</ul>';

				self::add_message( 'filesize-limit', 'warning', $msg );
			}

			if ( isset( $admin_notices['empty-scan'] ) && $admin_notices['empty-scan'] ) {
				// Get last scan timestamp.
				$last_scan = wfcm_get_setting( 'last-scan-timestamp', false );

				if ( $last_scan ) {
					$datetime_format = get_option( 'date_format' ) . ' ' . get_option( 'time_format' );
					$last_scan       = $last_scan + ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS );
					$last_scan       = date( $datetime_format, $last_scan );

					/* Translators: Date and time */
					$msg = '<p>' . sprintf( __( 'There were no file changes detected during the last file scan, which ran on %s.', 'website-file-changes-monitor' ), $last_scan ) . '</p>';
					self::add_message( 'empty-scan', 'info', $msg );
				}
			}

			if ( isset( $admin_notices['wp-repo-core-comms-failed'] ) && $admin_notices['wp-repo-core-comms-failed'] ) {
				$msg = '<p>' . sprintf(
						/* Translators: %s: WP White Security support hyperlink. */
								__( 'During the last scan the plugin failed to connect to the WordPress repository to cross-check the WordPress core files on your website. The plugin will automatically try to connect again during the upcoming scans. If this message keeps on appearing after every scan %s.', 'website-file-changes-monitor' ),
								'<a href="mailto:support@wpwhitesecurity.com" target="_blank">' . __( 'get in touch with us for assistance', 'website-file-changes-monitor' ) . '</a>'
						) . '</p>';

				self::add_message( 'wp-repo-core-comms-failed', 'error', $msg );
			}
		}

		// Add permalink structure notice.
		$permalink_structure = get_option( 'permalink_structure', false );

		if ( ! $permalink_structure ) {
			$msg = '<p>' . sprintf(
					/* Translators: %s: Website permalink settings hyperlink. */
							__( 'It seems that your permalinks are not configured. Please %s for the plugin to display the file changes.', 'website-file-changes-monitor' ),
							'<a href="' . admin_url( 'options-permalink.php' ) . '">' . __( 'configure them', 'website-file-changes-monitor' ) . '</a>'
					) . '</p>';
			self::add_message( 'permalink-notice', 'error', $msg, false );
		}
		
		// Add notice of currently active scan.
		$wfcm         = wfcm_get_monitor();
		$current_scan = $wfcm->get_current_number_of_active_bg_processes();
		if ( $current_scan > 0 ) {
			$msg = '<p>' . __( 'A scan is currently processing in the background.', 'website-file-changes-monitor' ) . '</p>';
			self::add_message( 'scan-running', 'warning', $msg, false );
		}
	}

	/**
	 * Add admin message.
	 *
	 * @param string $key - Message key.
	 * @param string $type - Type of message.
	 * @param string $message - Admin message.
	 * @param bool $dismissible - Notice is dismissible or not.
	 */
	public static function add_message( $key, $type, $message, $dismissible = true ) {
		self::$messages[ $key ] = array(
				'type'        => $type,
				'message'     => $message,
				'dismissible' => $dismissible,
		);
	}

	/**
	 * Set tabs of the page.
	 */
	private static function set_tabs() {
		self::$tabs = apply_filters(
				'wfcm_admin_file_changes_page_tabs',
				array(
						'added-files'    => array(
								'title' => __( 'Added Files', 'website-file-changes-monitor' ),
								'link'  => self::get_page_url(),
						),
						'modified-files' => array(
								'title' => __( 'Modified Files', 'website-file-changes-monitor' ),
								'link'  => add_query_arg( 'tab', 'modified-files', self::get_page_url() ),
						),
						'deleted-files'  => array(
								'title' => __( 'Deleted Files', 'website-file-changes-monitor' ),
								'link'  => add_query_arg( 'tab', 'deleted-files', self::get_page_url() ),
						),
				)
		);
	}

	/**
	 * Return page url.
	 *
	 * @return string
	 */
	public static function get_page_url() {
		$admin_url = is_multisite() ? network_admin_url( 'admin.php' ) : admin_url( 'admin.php' );

		return add_query_arg( 'page', 'wfcm-file-changes', $admin_url );
	}

	/**
	 * Show admin message.
	 */
	public static function show_messages() {
		if ( ! empty( self::$messages ) ) {
			$messages    = apply_filters( 'wfcm_admin_file_changes_messages', self::$messages );

			foreach ( $messages as $key => $notice ) :
				$classes = 'notice notice-' . $notice['type'] . ' wfcm-admin-notice';
				$classes .= $notice['dismissible'] ? ' is-dismissible' : '';
				?>
				<div id="wfcm-admin-notice-<?php echo esc_attr( $key ); ?>" class="<?php echo esc_attr( $classes ); ?>">
					<?php echo wp_kses( $notice['message'], self::$allowed_html ); ?>
				</div>
			<?php
			endforeach;
		}
	}

	/**
	 * Run on a filter to gets counts for the event types of each tab item.
	 *
	 * @method append_count_for_tabs
	 * @param array $tabs An array of tab links for the nav items.
	 *
	 * @return array
	 * @since  1.4.0
	 */
	public static function append_count_for_tabs( $tabs ) {
		foreach ( $tabs as $key => $tab ) {
			$event_type                   = rtrim( $key, '-files' );
			$tabs[ $key ]['unread_count'] = WFCM_Database_DB_Data_Store::get_total_events_count( $event_type );
		}

		return $tabs;
	}

	/**
	 * This runs through a transient cache because the query might be expansive.
	 * We are counting all rows...
	 *
	 * @param string $event_type
	 *
	 * @return int
	 * @since 1.7.0
	 */
	public static function get_unread_count( $event_type ) {
		$count = get_transient( 'wfcm_event_type_tabs_count_' . $event_type );
		if ( false !== $count ) {
			return $count;
		}
		
		$count = WFCM_Database_DB_Data_Store::get_total_events_count( $event_type );
		// allow zero values, sometimes all items are read.
		if ( $count || 0 === $count ) {
			// cache this value so we don't need to count this every refresh.
			set_transient( 'wfcm_event_type_tabs_count_' . $event_type, $count, DAY_IN_SECONDS );
		}

		return $count;
	}

	/**
	 * Get active tab.
	 *
	 * @return string
	 */
	private static function get_active_tab() {
		return isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'added-files'; // phpcs:ignore
	}

	/**
	 * Retrieves the URL for tab showing file changes of specific type.
	 *
	 * @param string $event_type Event type.
	 *
	 * @return string
	 * @since 1.7
	 */
	public static function get_tab_url( $event_type) {
		$key = $event_type . '-files';
		if ( empty ( self::$tabs ) ) {
			self::set_tabs();
		}
		return array_key_exists($key, self::$tabs) ? self::$tabs[$key]['link'] : '';
	}
}
