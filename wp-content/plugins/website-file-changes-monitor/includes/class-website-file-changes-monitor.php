<?php
/**
 * Website File Changes Monitor.
 *
 * @package wfcm
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Main Plugin Class.
 */
final class Website_File_Changes_Monitor {

	/**
	 * Plugin version.
	 *
	 * @var string
	 */
	public $version = '1.8.1';

	/**
	 * Single instance of the plugin.
	 *
	 * @var Website_File_Changes_Monitor
	 */
	protected static $instance = null;

	/**
	 * Main WP File Changes Monitor Instance.
	 *
	 * Ensures only one instance of WP File Changes Monitor is loaded or can be loaded.
	 *
	 * @return Website_File_Changes_Monitor
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->define_constants();
		$this->includes();
		$this->register_hooks();

		//  initialize background processes
		new WFCM_Background_Event_Deletion();
		new WFCM_Background_Scanner();
		new WFCM_Background_Check_For_Changes();

		do_action( 'website_file_changes_monitor_loaded' );
	}

	/**
	 * Define constants.
	 */
	public function define_constants() {
		$uploads_dir = wp_upload_dir();

		$this->define( 'WFCM_VERSION', $this->version );
		$this->define( 'WFCM_BASE_NAME', plugin_basename( WFCM_PLUGIN_FILE ) );
		$this->define( 'WFCM_BASE_URL', trailingslashit( plugin_dir_url( WFCM_PLUGIN_FILE ) ) );
		$this->define( 'WFCM_BASE_DIR', trailingslashit( plugin_dir_path( WFCM_PLUGIN_FILE ) ) );
		$this->define( 'WFCM_REST_NAMESPACE', 'website-file-changes-monitor/v1' );
		$this->define( 'WFCM_OPT_PREFIX', 'wfcm_' );
		$this->define( 'WFCM_OPT_PREFIX_OLD', 'wfcm-' );
		$this->define( 'WFCM_MIN_PHP_VERSION', '5.5.0' );
		$this->define( 'WFCM_UPLOADS_DIR', trailingslashit( $uploads_dir['basedir'] ) );
		$this->define( 'WFCM_LOGS_DIR', 'wfcm-logs' );
		$this->define( 'WFCM_FILE_EVENTS_TABLE', 'wfcm_file_events' );
	}

	/**
	 * Define constant if not defined already.
	 *
	 * @param string $name  - Constant name.
	 * @param string $value - Constant value.
	 */
	public function define( $name, $value ) {
		if ( ! defined( $name ) ) {
			define( $name, $value );
		}
	}

	/**
	 * Include plugin files.
	 */
	public function includes() {
		require_once WFCM_BASE_DIR . 'includes/class-wfcm-autoloader.php';
		require_once WFCM_BASE_DIR . 'includes/interface-wfcm-hash-comparator.php';
		require_once WFCM_BASE_DIR . 'includes/wfcm-functions.php';
		require_once WFCM_BASE_DIR . 'includes/class-wfcm-post-types.php';
		require_once WFCM_BASE_DIR . 'includes/class-wfcm-monitor.php';
		require_once WFCM_BASE_DIR . 'includes/class-wfcm-rest-api.php';

		// Data stores.
		require_once WFCM_BASE_DIR . 'includes/data-stores/class-wfcm-database-data-store.php';
		require_once WFCM_BASE_DIR . 'includes/data-stores/class-wfcm-events-data.php';

		if ( is_admin() ) {
			require_once WFCM_BASE_DIR . 'includes/admin/class-wfcm-admin.php';
		}

		// plugin, themes and core install/upgrade/delete monitoring
		require_once WFCM_BASE_DIR . 'includes/class-wfcm-plugins.php';
		require_once WFCM_BASE_DIR . 'includes/class-wfcm-themes.php';
		require_once WFCM_BASE_DIR . 'includes/class-wfcm-system.php';
		require_once WFCM_BASE_DIR . 'includes/class-wfcm-upgrader.php';

		// WSAL Events
		require_once WFCM_BASE_DIR . 'includes/wsal-events/sensor-functions.php';

		//  background processes
		require_once WFCM_BASE_DIR . 'includes/bg/class-wfcm-background-event-deletion.php';
		require_once WFCM_BASE_DIR . 'includes/bg/class-wfcm-background-scanner.php';
		require_once WFCM_BASE_DIR . 'includes/bg/class-wfcm-background-check-changes.php';
	}

	/**
	 * Checks if the plugin has just been updated and runs any update routines
	 * before the rest of the plugin is setup. After it's done it updates the
	 * `plugin_version` option with the latest version string.
	 *
	 * @method check_updated
	 * @since  1.4.0
	 */
	public function check_updated() {
		$version_key = WFCM_OPT_PREFIX . 'plugin_version';
		$old_version = get_option( $version_key, '0.0.0' );
		if ( WFCM_VERSION !== $old_version ) {
			require_once WFCM_BASE_DIR . 'includes/update/update-wrapper-interface.php';
			require_once WFCM_BASE_DIR . 'includes/update/abstract-update-wrapper.php';
			require_once WFCM_BASE_DIR . 'includes/class-wfcm-update-runner.php';
			$updater = new WFCM_Update_Runner( $old_version, WFCM_VERSION );
			$updater->run();
			// update the option holding the version string.
			update_option( $version_key, WFCM_VERSION );
		}
	}

	/**
	 * Register Hooks.
	 */
	public function register_hooks() {
		register_activation_hook( WFCM_PLUGIN_FILE, 'wfcm_install' );
		add_action( 'init', array( $this, 'check_updated' ) );
		add_action( 'admin_init', array( $this, 'redirect_on_activation' ) );
		add_action( 'admin_notices', array( $this, 'update_wsal_notice' ) );
	}

	/**
	 * Redirect on activation.
	 */
	 public function redirect_on_activation() {
 		if ( wfcm_get_setting( 'redirect-on-activate', false ) ) {
 			wfcm_delete_setting( 'redirect-on-activate' );

 			// Check for multisite.
 			if ( is_multisite() ) {
 				$redirect_url = add_query_arg( 'page', 'wfcm-file-changes', network_admin_url( 'admin.php' ) );
 			} else {
 				$redirect_url = add_query_arg( 'page', 'wfcm-file-changes', admin_url( 'admin.php' ) );
 			}

 			wp_safe_redirect( $redirect_url );
 			exit();
 		}
 	}

	// Show notice to users of older versions than 4.1.2
	public function update_wsal_notice() {
		if ( defined( 'WSAL_VERSION' ) ) {
			if ( version_compare( WSAL_VERSION , '4.1.2') >= 0 ) {
				delete_site_option( 'wfcm_update_wsal_notice' );
			} else {
				update_site_option( 'wfcm_update_wsal_notice', true );
			}

			if ( get_site_option( 'wfcm_update_wsal_notice' ) ) {
				echo '<div class="notice notice-success">
					<p>'. __( 'The Website File Changes Monitor plugin requires WP Activity Log version 4.1.2. Please upgrade that plugin.', 'website-file-changes-monitor' ) .'</p>
				</div>';
			}
		}
	}

	/**
	 * Error Logger
	 *
	 * Logs given input into debug.log file in debug mode.
	 *
	 * @param mixed $message - Error message.
	 */
	public function error_log( $message ) {
		if ( WP_DEBUG === true ) {
			if ( is_array( $message ) || is_object( $message ) ) {
				error_log( print_r( $message, true ) );
			} else {
				error_log( $message );
			}
		}
	}
}
