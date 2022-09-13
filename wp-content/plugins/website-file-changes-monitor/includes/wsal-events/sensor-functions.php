<?php

/**
* Hook into WSAL's action that runs before sensors get loaded.
*/
add_action( 'wsal_before_sensor_load', 'wfcm_load_custom_sensors_and_events_dirs' );

/**
 * Used to hook into the `wsal_before_sensor_load` action to add some filters
 * for including custom sensor and event directories.
 */
function wfcm_load_custom_sensors_and_events_dirs( $sensor ) {
	add_filter( 'wsal_custom_sensors_classes_dirs', 'wfcm_wsal_custom_sensors_path' );
	add_filter( 'wsal_custom_alerts_dirs', 'wfcm_wsal_add_custom_events_path' );
	return $sensor;
}

/**
 * Adds a new path to the sensors directory array which is checked for when the
 * plugin loads the sensors.
 */
function wfcm_wsal_custom_sensors_path( $paths = array() ) {
  $paths   = ( is_array( $paths ) ) ? $paths : array();
	$paths[] = trailingslashit( trailingslashit( dirname( __FILE__ ) ) . 'sensor' );
	return $paths;
}

/**
 * Adds a new path to the custom events directory array which is checked for
 * when the plugin loads all of the events.
 */
function wfcm_wsal_add_custom_events_path( $paths ) {
  $paths   = ( is_array( $paths ) ) ? $paths : array();
	$paths[] = trailingslashit( trailingslashit( dirname( __FILE__ ) ) . 'alerts' );
	return $paths;
}

/**
 * Adds new meta formatting for our plugion
 *
 * @param string $value Meta value.
 * @param string $expression Meta expression including the surrounding percentage chars.
 * @param WSAL_AlertFormatter $alert_formatter Alert formatter class.
 * @param int|null $occurrence_id Occurrence ID. Only present if the event was already written to the database. Default null.
 * 
 * @return string
 * 
 */
function wfcm_wsal_add_custom_meta_format( $value, $expression, $alert_formatter, $occurrence_id  ) {
	$wcfm_modified_page = '';
	$redirect_args      = array(
		'page' => 'wfcm-file-changes',
	);


	if ( '%ReviewChangesLink%' === $expression ) {
		$redirect_args['tab'] = 'modified-files';
		$wcfm_modified_page = wfcm_create_admin_url( $redirect_args );
		return '<a target="_blank" href="' . $wcfm_modified_page . '">' . __( 'Review changes', 'website-file-changes-monitor' ) . '</a>';
	}

	if ( '%ReviewDeletionsLink%' === $expression ) {
		$redirect_args['tab'] = 'deleted-files';
		$wcfm_modified_page = wfcm_create_admin_url( $redirect_args );
		return '<a target="_blank" href="' . $wcfm_modified_page . '">' . __( 'Review Changes', 'website-file-changes-monitor' ) . '</a>';
	}

	if ( '%ReviewAdditionsLink%' === $expression ) {
		$wcfm_modified_page = wfcm_create_admin_url( $redirect_args );
		return '<a target="_blank" href="' . $wcfm_modified_page . '">' . __( 'Review Changes', 'website-file-changes-monitor' ) . '</a>';
	}

	return $value;
}

add_filter( 'wsal_format_custom_meta', 'wfcm_wsal_add_custom_meta_format', 10, 4 );

/**
 * Simple helper function to give us the correct URL based on network status.
 *
 * @param araay] $redirect_args
 * 
 * @return string
 */
function wfcm_create_admin_url( $redirect_args ) {
	if ( ! is_multisite() ) {
		return add_query_arg( $redirect_args, admin_url( 'admin.php' ) );
	} else {
		return add_query_arg( $redirect_args, network_admin_url( 'admin.php' ) );
	}
}

/**
 * Adds new ignored CPT for our plugin
 */
function wfcm_wsal_add_custom_ignored_cpt( $post_types ) {
	$new_post_types = array(
		'wfcm_file_event',
	);

	// combine the two arrays.
	$post_types = array_merge( $post_types, $new_post_types );
	return $post_types;
}

add_filter( 'wsal_ignored_custom_post_types', 'wfcm_wsal_add_custom_ignored_cpt' );
