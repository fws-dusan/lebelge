<?php
/**
 * WFCM Uninstall
 *
 * Uninstalling WFCM deletes monitoring data and options.
 *
 * @package wfcm
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

wp_clear_scheduled_hook( 'wfcm_monitor_file_changes' );

if ( get_option( 'wfcm_delete-data', false ) ) {
	global $wpdb;
	$table_name    = $wpdb->prefix . 'wfcm_file_events';

	// Delete wfcm options.
	$wpdb->query( "DELETE FROM $wpdb->options WHERE ( option_name LIKE 'wfcm-%' or option_name LIKE 'wfcm_%' )" );
	// Delete wfcm transients.
	$wpdb->query( "DELETE FROM $wpdb->options WHERE ( option_name LIKE '_transient_wfcm%' OR option_name LIKE '_transient_timeout_wfcm%' )" );

	// Delete wfcm_file_event posts + data.
	$wpdb->query( "DROP TABLE IF EXISTS $table_name" );
}
