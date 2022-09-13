<?php
/**
 * License key functionality.
 *
 * @package   fonts-plugin-pro
 * @copyright Copyright (c) 2019, Fonts Plugin
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

/**
 * Register the the admin page submenu.
 */
function fpp_license_menu() {
	add_submenu_page( 'fonts-plugin', esc_html__( 'License Key', 'fonts-plugin-pro' ), esc_html__( 'License', 'fonts-plugin-pro' ), 'manage_options', 'fpp_license_page', 'fpp_license_page' );
}
add_action( 'admin_menu', 'fpp_license_menu', 150 );

/**
 * Render the admin page.
 */
function fpp_license_page() {
	$license = get_option( 'fpp_license_key' );
	$status  = get_option( 'fpp_license_status' );
	?>
	<div class="wrap">
		<h2><?php esc_html_e( 'Fonts Plugin License Key', 'fonts-plugin-pro' ); ?></h2>
		<form method="post" action="options.php">

			<?php
			wp_nonce_field( 'fpp_nonce', 'fpp_nonce' );
			settings_fields( 'fpp_license' );
			?>

			<table class="form-table">
				<tbody>
					<tr valign="top">
						<th scope="row" valign="top">
							<?php esc_html_e( 'License Key:', 'fonts-plugin-pro' ); ?>
						</th>
						<td>
							<input id="fpp_license_key" name="fpp_license_key" type="text" class="regular-text" value="<?php echo esc_attr( $license ); ?>" />
						</td>
					</tr>
					<tr valign="top">
						<th scope="row" valign="top">
							<?php esc_html_e( 'License Status:', 'fonts-plugin-pro' ); ?>
						</th>
						<td>
							<?php if ( $status !== false && $status === 'valid' ) : ?>
								<span style="color:green;"><?php esc_html_e( 'Active', 'fonts-plugin-pro' ); ?></span>
								<?php
							else : ?>
								<span style="color:red;"><?php esc_html_e( 'Inactive', 'fonts-plugin-pro' ); ?></span>
							<?php endif; ?>
						</td>
					</tr>
						<tr valign="top">
							<th scope="row" valign="top">
								<?php esc_html_e( 'Action:', 'fonts-plugin-pro' ); ?>
							</th>
							<td>
								<?php if ( $status !== false && $status === 'valid' ) : ?>
									<input type="submit" class="button-secondary" name="fpp_license_deactivate" value="<?php esc_html_e( 'Deactivate License', 'fonts-plugin-pro' ); ?>"/>
									<?php
								else :
									?>
									<input type="submit" class="button-secondary" name="fpp_license_activate" value="<?php esc_html_e( 'Activate License', 'fonts-plugin-pro' ); ?>"/>
								<?php endif; ?>
							</td>
						</tr>
				</tbody>
			</table>

		</form>
		<a href="https://docs.fontsplugin.com/license-key"><?php esc_html_e(' Where do I find my license key?', 'fonts-plugin-pro' ); ?></a>
	<?php
}

/**
 * Register the option to hold the license key.
 */
function fpp_register_option() {
	// Creates our settings in the options table.
	register_setting( 'fpp_license', 'fpp_license_key', 'fpp_sanitize_license' );
}
add_action( 'admin_init', 'fpp_register_option' );

/**
 * Sanitize the license key.
 *
 * @param string $new The license key.
 */
function fpp_sanitize_license( $new ) {
	$old = get_option( 'fpp_license_key' );
	if ( $old && $old !== $new ) {
		delete_option( 'fpp_license_status' ); // New license has been entered, so must reactivate.
	}
	return $new;
}

/**
 * Activate the license.
 */
function fpp_activate_license() {

	// Listen for our activate button to be clicked.
	if ( isset( $_POST['fpp_license_activate'] ) ) {

		// Run a quick security check.
		if ( ! check_admin_referer( 'fpp_nonce', 'fpp_nonce' ) ) {
			return; // get out if we didn't click the Activate button.
		}

		if ( isset( $_POST['fpp_license_key'] ) ) {
			$license = sanitize_text_field( wp_unslash( $_POST['fpp_license_key'] ) );
		}
		update_option( 'fpp_license_key', $license );

		// Data to send in our API request.
		$api_params = array(
			'edd_action' => 'activate_license',
			'license'    => $license,
			'item_id'    => 2191, // The ID of the item in EDD.
			'url'        => home_url(),
		);

		// Call the custom API.
		$response = wp_remote_post(
			'https://fontsplugin.com/',
			array(
				'timeout'   => 30,
				'sslverify' => false,
				'body'      => $api_params,
			)
		);

		// Make sure the response came back okay.
		if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {

			$message = ( is_wp_error( $response ) && ! empty( $response->get_error_message() ) ) ? $response->get_error_message() : esc_html__( 'An error occurred, please try again.' );

		} else {

			$license_data = json_decode( wp_remote_retrieve_body( $response ) );

			if ( false === $license_data->success ) {

				switch ( $license_data->error ) {

					case 'expired':
						$message = sprintf(
							__( 'Your license key expired on %s.', 'fonts-plugin-pro' ),
							date_i18n( get_option( 'date_format' ), strtotime( $license_data->expires, current_time( 'timestamp' ) ) )
						);
						break;

					case 'revoked':
						$message = __( 'Your license key has been disabled.', 'fonts-plugin-pro' );
						break;

					case 'missing':
						$message = __( 'Invalid license.', 'fonts-plugin-pro' );
						break;

					case 'invalid':
					case 'site_inactive':
						$message = __( 'Your license is not active for this URL.', 'fonts-plugin-pro' );
						break;

					case 'item_name_mismatch':
						$message = esc_html__( 'This appears to be an invalid license key for Fonts Plugin Pro.', 'fonts-plugin-pro' );
						break;

					case 'no_activations_left':
						$message = __( 'Your license key has reached its activation limit.', 'fonts-plugin-pro' );
						break;

					default:
						$message = __( 'An error occurred, please try again.', 'fonts-plugin-pro' );
						break;
				}
			}
		}

		// Check if anything passed on a message constituting a failure.
		if ( ! empty( $message ) ) {
			$base_url = admin_url( 'admin.php?page=fpp_license_page' );
			$redirect = add_query_arg(
				array(
					'sl_activation' => 'false',
					'message'       => rawurlencode( $message ),
				),
				$base_url
			);

			wp_redirect( $redirect );
			exit();
		}

		// $license_data->license will be either "valid" or "invalid"
		update_option( 'fpp_license_status', $license_data->license );
		wp_redirect( admin_url( 'admin.php?page=fpp_license_page' ) );
		exit();
	}
}
add_action( 'admin_init', 'fpp_activate_license' );

/**
 * Deactivate the license.
 */
function fpp_deactivate_license() {

	// Listen for our activate button to be clicked.
	if ( isset( $_POST['fpp_license_deactivate'] ) ) {

		// Run a quick security check.
		if ( ! check_admin_referer( 'fpp_nonce', 'fpp_nonce' ) ) {
			return; // get out if we didn't click the Activate button.
		}

		// Retrieve the license from the database.
		$license = trim( get_option( 'fpp_license_key' ) );

		// Data to send in our API request.
		$api_params = array(
			'edd_action' => 'deactivate_license',
			'license'    => $license,
			'item_id'    => 2191, // The ID of the item in EDD.
			'url'        => home_url(),
		);

		// Call the custom API.
		$response = wp_remote_post(
			'https://fontsplugin.com/',
			array(
				'timeout'   => 15,
				'sslverify' => false,
				'body'      => $api_params,
			)
		);

		// Make sure the response came back okay.
		if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {

			$message = ( is_wp_error( $response ) && ! empty( $response->get_error_message() ) ) ? $response->get_error_message() : esc_html__( 'An error occurred, please try again.', 'fonts-plugin-pro' );

		} else {

			$license_data = json_decode( wp_remote_retrieve_body( $response ) );

			update_option( 'fpp_license_key', '' );
			update_option( 'fpp_license_status', 'invalid' );
		}

		// Check if anything passed on a message constituting a failure.
		if ( ! empty( $message ) ) {
			$base_url = admin_url( 'admin.php?page=fpp_license_page' );
			$redirect = add_query_arg(
				array(
					'sl_deactivation' => 'false',
					'message'         => rawurlencode( $message ),
				),
				$base_url
			);

			wp_redirect( $redirect );
			exit();
		}

		wp_redirect( admin_url( 'admin.php?page=fpp_license_page' ) );
		exit();
	}
}
add_action( 'admin_init', 'fpp_deactivate_license' );
