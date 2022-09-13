<?php
/**
 * WFCM Settings.
 *
 * @package wfcm
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * WFCM Settings Class.
 */
class WFCM_Settings {

	const NOTIFY_TYPE      = 'notify_type';
	const NOTIFY_ADDRESSES = 'notify_addresses';

	/**
	 * Array of settings.
	 *
	 * @var array
	 */
	private static $settings = array();

	/**
	 * Site Content.
	 *
	 * Site content setting keeps track of plugins, themes, and
	 * other necessary information required during file changes
	 * monitoring scan.
	 *
	 * @var string
	 */
	public static $site_content = 'site-content';

	/**
	 * Return plugin setting.
	 *
	 * @param string $setting - Setting name.
	 * @param mixed  $default - Default setting value.
	 * @return mixed
	 */
	public static function get_setting( $setting, $default = false ) {
		if ( ! isset( self::$settings[ $setting ] ) ) {
			self::$settings[ $setting ] = get_option( WFCM_OPT_PREFIX . $setting, $default );
		}

		return self::$settings[ $setting ];
	}

	/**
	 * Save plugin setting.
	 *
	 * @param string $setting - Setting name.
	 * @param mixed  $value   - Setting value.
	 */
	public static function save_setting( $setting, $value ) {
		update_option( WFCM_OPT_PREFIX . $setting, $value, false );
		self::$settings[ $setting ] = $value;
		delete_transient( 'wfcm_options' );
	}

	/**
	 * Remove plugin setting.
	 *
	 * @param string $setting - Setting name.
	 */
	public static function delete_setting( $setting ) {
		delete_option( WFCM_OPT_PREFIX . $setting );
		unset( self::$settings[ $setting ] );
	}

	/**
	 * Return the plugin settings.
	 *
	 * @return array
	 */
	public static function get_monitor_settings() {
		$default_dirs  = array_keys( wfcm_get_server_directories( 'display' ) );
		$content_dir   = trailingslashit( WP_CONTENT_DIR );
		$default_excluded_dirs = array( $content_dir . 'cache', $content_dir . 'upgrade' );
		$default_excluded_extensions = array( 'jpg', 'jpeg', 'png', 'bmp', 'pdf', 'txt', 'log', 'mo', 'po', 'mp3', 'wav', 'gif', 'ico', 'jpe', 'psd', 'raw', 'svg', 'tif', 'tiff', 'aif', 'flac', 'm4a', 'oga', 'ogg', 'ra', 'wma', 'asf', 'avi', 'mkv', 'mov', 'mp4', 'mpe', 'mpeg', 'mpg', 'ogv', 'qt', 'rm', 'vob', 'webm', 'wm', 'wmv', 'json' );

		return array(
			'enabled'                                  => self::get_setting( 'keep-log', 'yes' ),
			'debug-logging'                            => self::get_setting( 'debug-logging', false ),
			'delete-data'                              => self::get_setting( 'delete-data', false ),
			'type'                                     => self::get_setting( 'scan-type', WFCM_REST_API::get_event_types() ),
			'frequency'                                => self::get_setting( 'scan-frequency', 'daily' ),
			'hour'                                     => self::get_setting( 'scan-hour', '02' ),
			'day'                                      => self::get_setting( 'scan-day', '1' ),
			'date'                                     => self::get_setting( 'scan-date', '01' ),
			'directories'                              => self::get_setting( 'scan-directories', $default_dirs ),
			'wp-repo-core-checksum-validation-enabled' => self::get_setting( 'scan-wp-repo-core-checksum-validation-enabled', 'yes' ),
			'file-size'                                => self::get_setting( 'scan-file-size', 5 ),
			'exclude-dirs'                             => self::get_setting( 'scan-exclude-dirs', $default_excluded_dirs ),
			'exclude-files'                            => self::get_setting( 'scan-exclude-files', array( 'wp-config-sample.php' ) ),
			'exclude-exts'                             => self::get_setting( 'scan-exclude-exts', $default_excluded_extensions ),
			'scan-dev-folders'                         => self::get_setting( 'scan-dev-folders', false ),
			self::NOTIFY_TYPE                          => self::get_setting( self::NOTIFY_TYPE, 'admin' ),
			self::NOTIFY_ADDRESSES                     => self::get_setting( self::NOTIFY_ADDRESSES, array() ),
			'email-changes-limit'                      => self::get_setting( 'email-changes-limit', 10 ),
			'send-email-upon-changes'                  => self::get_setting( 'send-email-upon-changes', 'yes' ),
			'empty-email-allowed'                      => self::get_setting( 'empty-email-allowed', 'no' ),
			'allowed-in-core-dirs'                     => self::get_setting( 'scan-allowed-in-core-dirs', [] ),
			'allowed-in-core-files'                    => self::get_setting( 'scan-allowed-in-core-files', array(
				'wp-config.php',
				'.htaccess',
				'bing.xml',
				'sitemap.xml',
				'readme.html',
				'.ftpquota',
				'robots.txt',
				'license.txt',
			) )
		);
	}

	/**
	 * Add plugin(s) or theme(s) to site content setting.
	 *
	 * @param string $type    - Type of content i.e. `plugin` or `theme`.
	 * @param string $content - Name of the content. It can be a plugin or a theme.
	 */
	public static function set_site_content( $type, $content ) {
		// Get site content.
		$site_content = self::get_setting( self::$site_content, false );

		// Site content skip array according to $type.
		$skip_type = "skip_$type";

		// Check if the type is not empty.
		if ( $content ) {
			$content = strtolower( $content );

			if ( isset( $site_content->$type ) && is_array( $site_content->$type ) && ! in_array( $content, $site_content->$type, true ) ) {
				array_push( $site_content->$type, $content );
			}

			if ( isset( $site_content->$skip_type ) && is_array( $site_content->$skip_type ) && ! isset( $site_content->$skip_type[ $content ] ) ) {
				$site_content->$skip_type[ $content ] = 'install';
			}

			self::save_setting( self::$site_content, $site_content );
		}
	}

	/**
	 * Remove plugin(s) or theme(s) to site content setting.
	 *
	 * @param string $type    - Type of content i.e. `plugins` or `themes`.
	 * @param string $content - Name of the content. It can be a plugin or a theme.
	 */
	public static function remove_site_content( $type, $content ) {
		// Get site content.
		$site_content = self::get_setting( self::$site_content, false );

		if ( false !== $site_content && $type && isset( $site_content->$type ) && in_array( $content, $site_content->$type, true ) ) {
			// Get array key of the content.
			$key = array_search( $content, $site_content->$type, true );

			// If the key is found then remove it from the array and save it.
			if ( false !== $key ) {
				unset( $site_content->$type[ $key ] );
				self::save_setting( self::$site_content, $site_content );
			}
		}
	}

	/**
	 * Set Skip Site Content.
	 *
	 * This content will be skipped during the next file changes scan.
	 *
	 * @param string $type    - Skip type.
	 * @param string $content - Skip content.
	 * @param string $context - Context of the change, i.e, update or uninstall.
	 */
	public static function set_skip_site_content( $type, $content, $context ) {
		$site_content = self::get_setting( self::$site_content, false );
		$skip_type    = "skip_$type";

		if ( false !== $site_content && $content && isset( $site_content->$skip_type ) ) {
			$site_content->$skip_type[ $content ] = $context;
			self::save_setting( self::$site_content, $site_content );
		}
	}
}
