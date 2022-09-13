<?php

/**
 * Admin AJAX.
 *
 * @package wfcm
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Admin AJAX Handler Class.
 */
class WFCM_Admin_Ajax {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'wp_ajax_wfcm_dismiss_instant_scan_modal', array( $this, 'dismiss_instant_scan_modal' ) );
		add_action( 'wp_ajax_wfcm_sha256_upgrade_flush', array( $this, 'sha256_upgrade_flush' ) );
		add_action( 'wp_ajax_wfcm_send_test_email', array( $this, 'send_test_email' ) );
		add_action( 'wp_ajax_wfcm_exclude_file_from_notice', array( $this, 'exclude_file_from_notice' ) );
		add_action( 'wp_ajax_wfcm_set_scan_frequency', array( $this, 'handle_set_scan_frequency_request' ) );
	}

	/**
	 * Ajax handler to dismiss instant scan modal.
	 */
	public function dismiss_instant_scan_modal() {
		check_admin_referer( 'wp_rest', 'security' );
		wfcm_save_setting( 'dismiss-instant-scan-modal', true );
		die();
	}

	/**
	 * Ajax handler to flush out old file lists so that new fingerprints can be
	 * generated on the next scan.
	 *
	 * @method sha256_upgrade_flush
	 * @since  1.5.0
	 */
	public function sha256_upgrade_flush() {
		check_admin_referer( 'wp_rest', 'security' );
		// only modify options if user has manage_options cap.
		if ( current_user_can( 'manage_options' ) ) {
			// loop through all 7 file list groups and delete them.
			for ( $x = 0; $x <= 6; $x++ ) {
				wfcm_delete_setting( 'is-initial-scan-' . $x );
				wfcm_delete_setting( 'local-files-' . $x );
			}
			wfcm_save_setting( 'sha256-hashing', true );
		}
		die();
	}

	/**
	 * Sends a test email to the configured account.
	 *
	 * @method dismiss_instant_scan_modal
	 * @since  1.5
	 */
	public function send_test_email() {
		check_admin_referer( 'wp_rest', 'security' );

		// get the settings.
		$email_notice_type = wfcm_get_setting( WFCM_Settings::NOTIFY_TYPE, 'admin' );
		$email_custom_list = wfcm_get_setting( WFCM_Settings::NOTIFY_ADDRESSES, array() );
		// convert TO an array from a string.
		$email_custom_list = ( ! is_array( $email_custom_list ) ) ? explode( ',', $email_custom_list ) : $email_custom_list;
		// Set up a subject and body and empty array for results.
		$email_subject = __( 'WFCM Test Mail', 'website-file-changes-monitor' );
		$email_body    = __( 'This is a test email from Website File Changes Monitor running on your site.', 'website-file-changes-monitor' );
		$sent_results  = array();

		/*
		 * Decide where to send email notifications. This uses a custom list of
		 * 1 or more addresses and falls back to admin address if a custom list
		 * is not used.
		 */
		if ( 'custom' === $email_notice_type && ! empty( $email_custom_list ) ) {
			// we have a custom list to use.
			foreach ( $email_custom_list as $email_address ) {
				if ( filter_var( $email_address, FILTER_VALIDATE_EMAIL ) ) {
					$result = WFCM_Email::send( $email_address, $email_subject, $email_body );
					if ( $result ) {
						$sent_results[] = $result;
					}
				}
			}
		} else {
			// sending to admin address.
			$result = WFCM_Email::send( get_bloginfo( 'admin_email' ), esc_html( $email_subject ), esc_html( $email_body ) );
			if ( $result ) {
				$sent_results[] = $result;
			}
		}

		if ( ! empty( $sent_results ) ) {
			wp_send_json_success( $sent_results );
		} else {
			wp_send_json_error(
				array(
					esc_html( 'Error sending test email', 'website-file-changes-monitor' ),
				)
			);
		}
		// should never reach this die.
		die();
	}

	public function exclude_file_from_notice() {
		check_ajax_referer( 'wfcm-exclude-file-nonce' );

		if ( isset( $_POST['file'] ) ) {
			$file = $_POST['file'];
		} else {
			exit;
		}

		$currently_excluded_files = wfcm_get_setting( 'scan-exclude-files' );
		$current_notices          = wfcm_get_setting( 'admin-notices' );
		$current_notices          = $current_notices['filesize-limit'];
		$new_files_to_exclude     = array( basename( $file ) );
		$file                     = str_replace('\\\\', '\\', $file );

		foreach ( $current_notices as $key => $file_path ) {
			if ( $file_path == $file ) {
				unset( $current_notices[$key] );
			}
		}

		$new_notice_files['filesize-limit'] = $current_notices;
		$excluded_files                     = array_unique( array_merge( $currently_excluded_files, $new_files_to_exclude ) );

		// Update settings.
		wfcm_save_setting( 'scan-exclude-files', $excluded_files );
		wfcm_save_setting( 'admin-notices', $new_notice_files );

		// Send response to AJAX call.
		wp_send_json_success(
			array(
				'message' => esc_html__( 'File excluded', 'website-file-changes-montitor' ),
			)
		);
	}

	/**
	 * Handles AJAX request to set the scan frequency settings.
	 *
	 * @since 1.7.0
	 */
	public function handle_set_scan_frequency_request() {
		//	nonce check
		check_admin_referer( 'wp_rest', 'security' );

		//	read the request data from JSON payload
		$data = json_decode( file_get_contents( 'php://input' ), 1 );
		if ( ! is_array( $data ) || empty( $data ) ) {
			wp_send_json_error(
				esc_html( 'Missing data', 'website-file-changes-monitor' )
			);
		}

		//	transform data to the same format used in admin settings
		$transformed_frequency_data = [];
		foreach ( $data as $key => $value ) {
			$transformed_frequency_data[ 'scan-' . $key ] = $value;
		}

		WFCM_Admin_Settings::clear_scan_cron_if_necessary( $transformed_frequency_data );
		foreach ( $transformed_frequency_data as $key => $raw_value ) {
			$value = sanitize_text_field( wp_unslash( $raw_value ) );
			wfcm_save_setting( $key, $value );
		}

		wp_send_json_success( $data );
	}

}

new WFCM_Admin_Ajax();
