<?php
/**
 * Plugin Name:       MonsterInsights - Page Insights Addon
 * Plugin URI:        https://www.monsterinsights.com
 * Description:       Adds individual page insights directly in the WordPress admin.
 * Author:            MonsterInsights Team
 * Author URI:        https://www.monsterinsights.com
 * Version:           1.5.0
 * Requires at least: 4.8.0
 * Requires PHP:      5.5
 * Text Domain:       monsterinsights-page-insights
 * Domain Path:       languages
 *
 * @package monsterinsights-page-insights
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class MonsterInsights_Posts
 */
class MonsterInsights_Page_Insights {

	/**
	 * Holds the class object.
	 *
	 * @since 1.0.0
	 *
	 * @var MonsterInsights_Page_Insights
	 */
	public static $instance;

	/**
	 * Plugin version, used for cache-busting of style and script file references.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $version = '1.5.0';

	/**
	 * The name of the plugin.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $plugin_name = 'MonsterInsights Page Insights';

	/**
	 * Unique plugin slug identifier.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $plugin_slug = 'monsterinsights-page-insights';

	/**
	 * Plugin file.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $file;

	/**
	 * MonsterInsights_Posts constructor.
	 */
	private function __construct() {
		$this->file = __FILE__;

		if ( ! $this->check_compatibility() ) {
			return;
		}

		// Load the plugin textdomain.
		add_action( 'plugins_loaded', array( $this, 'load_plugin_textdomain' ) );

		// Load the updater.
		add_action( 'monsterinsights_updater', array( $this, 'updater' ) );

		// Load the plugin.
		add_action( 'monsterinsights_load_plugins', array( $this, 'init' ), 99 );

		if ( ! defined( 'MONSTERINSIGHTS_PRO_VERSION' ) ) {
			// Make sure plugin is listed in Auto-update Disabled view
			add_filter( 'auto_update_plugin', array( $this, 'disable_auto_update' ), 10, 2 );

			// Display call-to-action to get Pro in order to enable auto-update
			add_filter( 'plugin_auto_update_setting_html', array( $this, 'modify_autoupdater_setting_html' ), 11, 2 );
		}

		$this->install_hooks();
	}

	/**
	 * Returns the singleton instance of the class.
	 *
	 * @since 1.0.0
	 *
	 * @return MonsterInsights_Page_Insights The MonsterInsights_Posts object.
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof MonsterInsights_Page_Insights ) ) {
			self::$instance = new MonsterInsights_Page_Insights();
		}

		return self::$instance;
	}

	/**
	 * Check compatibility with PHP and WP, and display notices if necessary
	 *
	 * @since 1.3.4
	 * @return bool
	 */
	private function check_compatibility() {
		if ( defined( 'MONSTERINSIGHTS_FORCE_ACTIVATION' ) && MONSTERINSIGHTS_FORCE_ACTIVATION ) {
			return true;
		}

		require_once plugin_dir_path( __FILE__ ) . 'includes/class-monsterinsights-page-insights-compatibility-check.php';
		$compatibility = MonsterInsights_Page_Insights_Compatibility_Check::get_instance();
		$compatibility->maybe_display_notice();

		return $compatibility->is_php_compatible() && $compatibility->is_wp_compatible();
	}

	/**
	 * Loads the plugin textdomain for translation.
	 *
	 * @since 1.0.0
	 */
	public function load_plugin_textdomain() {
		load_plugin_textdomain( $this->plugin_slug, false, dirname( plugin_basename( $this->file ) ) . '/languages/' );
	}

	/**
	 * Loads the plugin into WordPress.
	 *
	 * @since 1.0.0
	 */
	public function init() {

		if ( ! defined( 'MONSTERINSIGHTS_PRO_VERSION' ) ) {
			// admin notice, MI not installed.
			add_action( 'admin_notices', array( self::$instance, 'requires_monsterinsights' ) );
			return;
		}

		if ( version_compare( MONSTERINSIGHTS_VERSION, '8.5.1', '<' ) ) {
			// MonsterInsights version not supported.
			add_action( 'admin_notices', array( self::$instance, 'requires_monsterinsights_version' ) );

			return;
		}

		if ( ! defined( 'MONSTERINSIGHTS_PAGE_INSIGHTS_ADDON_PLUGIN_URL' ) ) {
			define( 'MONSTERINSIGHTS_PAGE_INSIGHTS_ADDON_PLUGIN_URL', plugin_dir_url( $this->file ) );
		}

		// Load admin only components.
		if ( is_admin() ) {
			$this->require_admin();
		}

		$this->require_frontend();
	}

	/**
	 * Loads all admin related files into scope.
	 *
	 * @since 1.0.0
	 */
	public function require_admin() {
		// The caching class.
		require_once plugin_dir_path( $this->file ) . 'includes/class-monsterinsights-page-insights-cache.php';
		// Load the background data fetcher.
		require_once plugin_dir_path( $this->file ) . 'includes/class-monsterinsights-page-insights-background.php';

		// Load the admin interface.
		require_once plugin_dir_path( $this->file ) . 'includes/admin/class-monsterinsights-page-insights-admin.php';
		new MonsterInsights_Page_Insights_Admin();

		// Load the report.
		require_once plugin_dir_path( $this->file ) . 'includes/admin/reports/class-monsterinsights-report-page-insights.php';

	}

	/**
	 * Non-admin includes.
	 */
	public function require_frontend() {

		require_once plugin_dir_path( $this->file ) . 'includes/class-monsterinsights-page-insights-ajax.php';
		new MonsterInsights_Page_Insights_Ajax();
    }

	/**
	 * Initializes the addon updater.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key The user license key.
	 */
	public function updater( $key ) {
		$args = array(
			'plugin_name' => $this->plugin_name,
			'plugin_slug' => $this->plugin_slug,
			'plugin_path' => plugin_basename( __FILE__ ),
			'plugin_url'  => trailingslashit( WP_PLUGIN_URL ) . $this->plugin_slug,
			'remote_url'  => 'https://www.monsterinsights.com/',
			'version'     => $this->version,
			'key'         => $key,
		);

		new MonsterInsights_Updater( $args );
	}

	/**
	 * Display MonsterInsights Pro CTA on Plugins -> autoupdater setting column
	 *
	 * @param string $html
	 * @param string $plugin_file
	 *
	 * @return string
	 */
	public function modify_autoupdater_setting_html( $html, $plugin_file ) {
		if ( plugin_basename( __FILE__ ) === $plugin_file &&
		     // If main plugin (free) happens to be enabled and already takes care of this, then bail
		     ! apply_filters( "monsterinsights_is_autoupdate_setting_html_filtered_${plugin_file}", false )
		) {
			$html = sprintf(
				'<a href="%s">%s</a>',
				'https://www.monsterinsights.com/docs/go-lite-pro/?utm_source=liteplugin&utm_medium=plugins-autoupdate&utm_campaign=upgrade-to-autoupdate&utm_content=monsterinsights-page-insights',
				__( 'Enable the MonsterInsights PRO plugin to manage auto-updates', 'monsterinsights-page-insights' )
			);
		}

		return $html;
	}

	/**
	 * Disable auto-update.
	 *
	 * @param $update
	 * @param $item
	 *
	 * @return bool
	 */
	public function disable_auto_update( $update, $item ) {
		// If this is multisite and is not on the main site, return early.
		if ( is_multisite() && ! is_main_site() ) {
			return $update;
		}

		if ( isset( $item->id ) && plugin_basename( __FILE__ ) === $item->id ) {
			return false;
		}

		return $update;
	}

	/**
	 * Add the install hooks.
	 */
	public function install_hooks() {

		require_once plugin_dir_path( $this->file ) . 'includes/class-monsterinsights-page-insights-install.php';

		register_activation_hook( $this->file, array(
			'MonsterInsights_Page_Insights_Install',
			'handle_install',
		) );

		register_uninstall_hook( $this->file, array(
			'MonsterInsights_Page_Insights_Install',
			'handle_uninstall',
		) );

		// Add tables to new blogs.
		add_action( 'wpmu_new_blog', array( 'MonsterInsights_Page_Insights_Install', 'install_new_blog' ), 10, 6 );
	}

	/**
	 * Output a nag notice if the user does not have MI installed
	 */
	public function requires_monsterinsights() {
		?>
		<div class="error">
			<p><?php esc_html_e( 'Please install MonsterInsights Pro to use the MonsterInsights Page Insights addon', 'monsterinsights-page-insights' ); ?></p>
		</div>
		<?php
	}

	/**
	 * Output a nag notice if the user does not have MI version installed
	 */
	public function requires_monsterinsights_version() {
		?>
		<div class="error">
			<p><?php esc_html_e( 'Please install or update MonsterInsights Pro with version 8.5.1 or newer to use the MonsterInsights Page Insights addon', 'monsterinsights-page-insights' ); ?></p>
		</div>
		<?php
	}
}

$monsterinsights_page_insights = MonsterInsights_Page_Insights::get_instance();
