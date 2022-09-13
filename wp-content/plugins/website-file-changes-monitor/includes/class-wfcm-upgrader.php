<?php
/**
 * WFCM Upgrader.
 *
 * @package wfcm
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Upgrader Class.
 *
 * This class monitors the plugins, theme and WordPress core install, uninstall, and update events for file changes
 * monitoring.
 *
 */
class WFCM_Upgrader {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'upgrader_process_complete', array( $this, 'on_upgrader_process_complete' ), 10, 2 );
		add_action( 'deleted_plugin', array( $this, 'on_plugin_deleted' ), 10, 2 );
		// no hook for theme deletion available in WP 5.5.3 :(
	}


	/**
	 * Fires immediately after a plugin deletion attempt.
	 *
	 * @param string $plugin_file Path to the plugin file relative to the plugins directory.
	 * @param bool $deleted Whether the plugin deletion was successful.
	 */
	public function on_plugin_deleted( $plugin_file, $deleted ) {
		if ( ! $deleted ) {
			return;
		}

		WFCM_Plugins::process_deleted_site_plugins( [ $plugin_file ] );
	}

	/**
	 * Monitor plugin/theme/core upgrader events.
	 *
	 * @param WP_Upgrader|Theme_Upgrader|Plugin_Upgrader $upgrader WP_Upgrader instance.
	 * @param array $process_data Array of bulk item update data.
	 *
	 * @see WP_Upgrader::run()
	 */
	public function on_upgrader_process_complete( $upgrader, $process_data ) {
		switch ( $process_data['type'] ) {
			case 'plugin':
				$plugins = [];
				if ( ! empty( $process_data['bulk'] ) ) {
					$plugins = $process_data['plugins'];
				} elseif ( 'install' === $process_data['action'] ) {
					$plugin_info = $upgrader->plugin_info();
					$plugins     = $plugin_info ? array( $plugin_info ) : array();
				} else {
					$plugins = array( $process_data['plugin'] );
				}

				$this->handle_plugin_upgrader_result( $plugins, $process_data['action'] );
				break;

			case 'theme':
				$themes = [];
				if ( ! empty( $process_data['bulk'] ) ) {
					$themes = $process_data['themes'];
				} elseif ( 'install' === $process_data['action'] ) {
					$theme_info = $upgrader->theme_info();
					$themes     = $theme_info ? array( $theme_info->get_stylesheet() ) : array();
				} else {
					$themes = array( $process_data['theme'] );
				}
				$this->handle_theme_upgrader_result( $themes, $process_data['action'] );
				break;

			case 'core':
				$this->handle_core_upgrader_result( $process_data['action'] );
				break;
			case 'translation':
			default:
				//  other update process types are not supported
				break;
		}
	}

	/**
	 * @param array $plugins Array of the basename paths of the plugins' main files.
	 * @param string $action Type of action. Default 'update'.
	 */
	private function handle_plugin_upgrader_result( $plugins, $action ) {
		switch ( $action ) {
			case 'install':
				WFCM_Plugins::process_added_site_plugins( $plugins );
				break;
			case 'update':
				WFCM_Plugins::process_updated_site_plugins( $plugins );
				break;
			default:
				break;
		}
	}

	/**
	 * @param array $themes The theme slugs.
	 * @param string $action Type of action. Default 'update'.
	 */
	private function handle_theme_upgrader_result( $themes, $action ) {
		switch ( $action ) {
			case 'install':
				WFCM_Themes::process_added_site_themes( $themes );
				break;
			case 'update':
				WFCM_Themes::process_updated_theme_sites( $themes );
				break;
			default:
				break;
		}
	}

	/**
	 * @param string $action Type of action. Default 'update'.
	 */
	private function handle_core_upgrader_result( $action ) {
		if ( 'update' === $action ) {
			WFCM_System::process_core_update();
		}
	}
}

new WFCM_Upgrader();
