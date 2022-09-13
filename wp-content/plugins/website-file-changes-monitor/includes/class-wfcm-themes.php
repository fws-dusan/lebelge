<?php
/**
 * WFCM Admin Themes.
 *
 * @package wfcm
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Admin Themes Class.
 *
 * This class monitors the theme install, uninstall, and
 * update events for file changes monitoring.
 */
class WFCM_Themes {

	/**
	 * List of themes already installed.
	 *
	 * @var array
	 */
	private $old_themes = array();

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'admin_init', array( $this, 'on_admin_init' ) );
	}

	/**
	 * @param array $updated_themes
	 */
	public static function process_updated_theme_sites( $updated_themes ) {
		if ( ! empty( $updated_themes ) ) {
			foreach ( $updated_themes as $updated_theme ) {
				wfcm_skip_theme_scan( $updated_theme, 'update' );
			}
		}
	}

	public function on_admin_init() {
		if ( current_user_can( 'install_themes' ) || current_user_can( 'delete_themes' ) || current_user_can( 'update_themes' ) ) {
			$this->set_old_themes();
			add_action( 'shutdown', array( $this, 'monitor_theme_events' ) );
		}
	}

	/**
	 * @param array $themes
	 */
	public static function process_deleted_site_themes( $themes ) {
		if ( empty( $themes ) ) {
			return;
		}

		foreach ( $themes as $theme ) {
			wfcm_skip_theme_scan( $theme->stylesheet, 'uninstall' );
			wfcm_remove_site_theme( $theme->stylesheet );
		}
	}

	/**
	 * @param array $themes An array of theme slugs.
	 */
	public static function process_added_site_themes( $themes ) {
		if ( empty( $themes ) ) {
			return;
		}

		foreach ( $themes as $theme ) {
			wfcm_add_site_theme( $theme );
		}
	}

	/**
	 * Set Old Themes.
	 */
	public function set_old_themes() {
		$this->old_themes = wp_get_themes();
	}

	/**
	 * Monitor Theme Events.
	 */
	public function monitor_theme_events() {
		global $pagenow;

		// Get $_POST action data.
		$action         = isset( $_POST['action'] ) ? sanitize_text_field( wp_unslash( $_POST['action'] ) ) : false; // phpcs:ignore
		$is_themes_page = false;

		if ( 'update.php' === $pagenow ) {
			$action = isset( $_GET['action'] ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : false; // phpcs:ignore
		} elseif ( 'themes.php' === $pagenow ) {
			$action         = isset( $_GET['action'] ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : false; // phpcs:ignore
			$is_themes_page = true;
		}

		$install_actions = array( 'install-theme', 'upload-theme' );
		$update_actions  = array( 'upgrade-theme', 'update-theme', 'update-selected-themes' );

		// Handle theme install event.
		if ( in_array( $action, $install_actions, true ) && current_user_can( 'install_themes' ) ) {
			// Get installed theme.
			$themes = array_diff( wp_get_themes(), $this->old_themes );
			self::process_added_site_themes( array_keys( $themes ) );
		}

		// Handle theme uninstall event.
		if ( ( 'delete-theme' === $action && current_user_can( 'delete_themes' ) ) || ( $is_themes_page && 'delete' === $action && current_user_can( 'delete_themes' ) ) ) {
			self::process_deleted_site_themes( $this->get_removed_themes() );
		}

		// Handle theme update event.
		if ( in_array( $action, $update_actions, true ) && current_user_can( 'update_themes' ) ) {
			$updated_themes = array();

			if ( isset( $_POST['slug'] ) ) { // phpcs:ignore
				$updated_themes[] = sanitize_text_field( wp_unslash( $_POST['slug'] ) ); // phpcs:ignore
			} elseif ( isset( $_GET['theme'] ) ) { // phpcs:ignore
				$updated_themes[] = sanitize_text_field( wp_unslash( $_GET['theme'] ) ); // phpcs:ignore
			} elseif ( isset( $_GET['themes'] ) ) { // phpcs:ignore
				$updated_themes = explode( ',', sanitize_text_field( wp_unslash( $_GET['themes'] ) ) ); // phpcs:ignore
			}

			self::process_updated_theme_sites( $updated_themes );
		}
	}

	/**
	 * Get removed themes.
	 *
	 * @return array of WP_Theme objects
	 */
	protected function get_removed_themes() {
		$removed_themes = $this->old_themes;
		foreach ( $removed_themes as $dir => $theme ) {
			if ( file_exists( $theme->get_template_directory() ) ) {
				unset( $removed_themes[ $dir ] );
			}
		}
		return array_values( $removed_themes );
	}
}

new WFCM_Themes();
