<?php
/**
 * Settings View.
 *
 * @package wfcm
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Scan Frequencies.
 */
$frequency_options = wfcm_get_frequency_options();

// Scan hours option.
$scan_hours = wfcm_get_hours_options();

// Scan days option.
$scan_days = wfcm_get_days_options();

// Scan date option.
$scan_date = array(
	'01' => _x( '01', 'a day number in a given month', 'website-file-changes-monitor' ),
	'02' => _x( '02', 'a day number in a given month', 'website-file-changes-monitor' ),
	'03' => _x( '03', 'a day number in a given month', 'website-file-changes-monitor' ),
	'04' => _x( '04', 'a day number in a given month', 'website-file-changes-monitor' ),
	'05' => _x( '05', 'a day number in a given month', 'website-file-changes-monitor' ),
	'06' => _x( '06', 'a day number in a given month', 'website-file-changes-monitor' ),
	'07' => _x( '07', 'a day number in a given month', 'website-file-changes-monitor' ),
	'08' => _x( '08', 'a day number in a given month', 'website-file-changes-monitor' ),
	'09' => _x( '09', 'a day number in a given month', 'website-file-changes-monitor' ),
	'10' => _x( '10', 'a day number in a given month', 'website-file-changes-monitor' ),
	'11' => _x( '11', 'a day number in a given month', 'website-file-changes-monitor' ),
	'12' => _x( '12', 'a day number in a given month', 'website-file-changes-monitor' ),
	'13' => _x( '13', 'a day number in a given month', 'website-file-changes-monitor' ),
	'14' => _x( '14', 'a day number in a given month', 'website-file-changes-monitor' ),
	'15' => _x( '15', 'a day number in a given month', 'website-file-changes-monitor' ),
	'16' => _x( '16', 'a day number in a given month', 'website-file-changes-monitor' ),
	'17' => _x( '17', 'a day number in a given month', 'website-file-changes-monitor' ),
	'18' => _x( '18', 'a day number in a given month', 'website-file-changes-monitor' ),
	'19' => _x( '19', 'a day number in a given month', 'website-file-changes-monitor' ),
	'20' => _x( '20', 'a day number in a given month', 'website-file-changes-monitor' ),
	'21' => _x( '21', 'a day number in a given month', 'website-file-changes-monitor' ),
	'22' => _x( '22', 'a day number in a given month', 'website-file-changes-monitor' ),
	'23' => _x( '23', 'a day number in a given month', 'website-file-changes-monitor' ),
	'24' => _x( '24', 'a day number in a given month', 'website-file-changes-monitor' ),
	'25' => _x( '25', 'a day number in a given month', 'website-file-changes-monitor' ),
	'26' => _x( '26', 'a day number in a given month', 'website-file-changes-monitor' ),
	'27' => _x( '27', 'a day number in a given month', 'website-file-changes-monitor' ),
	'28' => _x( '28', 'a day number in a given month', 'website-file-changes-monitor' ),
	'29' => _x( '29', 'a day number in a given month', 'website-file-changes-monitor' ),
	'30' => _x( '30', 'a day number in a given month', 'website-file-changes-monitor' ),
);

// WP Directories.
$wp_directories = wfcm_get_server_directories( 'display' );

$wp_directories = apply_filters( 'wfcm_file_changes_scan_directories', $wp_directories );

$disabled = ! $settings['enabled'] ? 'disabled' : false;

// get email notice type and convert emails array to string seporated by commas.
$email_notice_type = ( isset( $settings[ WFCM_Settings::NOTIFY_TYPE ] ) && 'custom' === $settings[ WFCM_Settings::NOTIFY_TYPE ] ) ? 'custom' : 'admin';
$email_custom_list = ( isset( $settings[ WFCM_Settings::NOTIFY_ADDRESSES ] ) ) ? $settings[ WFCM_Settings::NOTIFY_ADDRESSES ] : '';
// convert to string FROM an array.
$email_custom_list = ( is_array( $email_custom_list ) ) ? implode( ',', $email_custom_list ) : $email_custom_list;
?>

<div class="wrap wfcm-settings">
	<h1><?php esc_html_e( 'Website File Changes Settings', 'website-file-changes-monitor' ); ?></h1>
	<?php WFCM_Admin_File_Changes::show_messages(); ?>
	<form method="post" action="" enctype="multipart/form-data">
		<h3><?php esc_html_e( 'Which file changes do you want to be notified of?', 'website-file-changes-monitor' ); ?></h3>
		<!-- Type of Changes -->
		<table class="form-table wfcm-table">
			<tr>
				<th><label for="wfcm-file-changes-type"><?php esc_html_e( 'Notify me when files are', 'website-file-changes-monitor' ); ?></label></th>
				<td>
					<fieldset <?php echo esc_attr( $disabled ); ?>>
						<label for="added">
							<input type="checkbox" name="wfcm-settings[scan-type][]" value="added" <?php echo in_array( 'added', $settings['type'], true ) ? 'checked' : false; ?>>
							<span><?php esc_html_e( 'Added', 'website-file-changes-monitor' ); ?></span>
						</label>
						<br>
						<label for="deleted">
							<input type="checkbox" name="wfcm-settings[scan-type][]" value="deleted" <?php echo in_array( 'deleted', $settings['type'], true ) ? 'checked' : false; ?>>
							<span><?php esc_html_e( 'Deleted', 'website-file-changes-monitor' ); ?></span>
						</label>
						<br>
						<label for="modified">
							<input type="checkbox" name="wfcm-settings[scan-type][]" value="modified" <?php echo in_array( 'modified', $settings['type'], true ) ? 'checked' : false; ?>>
							<span><?php esc_html_e( 'Modified', 'website-file-changes-monitor' ); ?></span>
						</label>
					</fieldset>
				</td>
			</tr>
		</table>

		<!-- Email to send changes notices to -->
		<h3><?php esc_html_e( 'Where should we send the file changes notification?', 'website-file-changes-monitor' ); ?></h3>
		<p class="description"><?php esc_html_e( 'By default the plugin sends the email notifications to the administrator email address configured in the WordPress settings. Use the below setting to send the email notification to a different email address. You can specify multiple email addresses by separating them with a comma.', 'website-file-changes-monitor' ); ?></p>
		<table class="form-table wfcm-table">
			<tr>
				<th><label for="wfcm-notification-email-address"><?php esc_html_e( 'Notify this address', 'website-file-changes-monitor' ); ?></label></th>
				<td>
					<fieldset>
						<label for="email-notice-admin">
							<input type="radio" id="email-notice-admin" name="wfcm-settings[<?php echo esc_attr( WFCM_Settings::NOTIFY_TYPE ); ?>]" value="admin"<?php echo ( 'custom' !== $email_notice_type ) ? ' checked' : ''; ?>>
							<span><?php esc_html_e( 'Use admin email address in website settings.', 'website-file-changes-monitor' ); ?></span>
						</label>
						<br />
						<label for="email-notice-custom">
							<input type="radio" id="email-notice-custom" name="wfcm-settings[<?php echo esc_attr( WFCM_Settings::NOTIFY_TYPE ); ?>]" value="custom"<?php echo ( 'custom' === $email_notice_type ) ? ' checked' : ''; ?>>
							<input type="email" id="notice-email-address" name="wfcm-settings[<?php echo esc_attr( WFCM_Settings::NOTIFY_ADDRESSES ); ?>]" multiple pattern="^([\w+-.%]+@[\w-.]+\.[A-Za-z]{2,4},*[\W]*)+$" value="<?php echo esc_attr( $email_custom_list ); ?>">
						</label>
						<br />
						<input type="button" class="button button-primary" id="wfcm-send-test-email" value="<?php esc_attr_e( 'Send a test email', 'website-file-changes-monitor' ); ?>"/>
						<div id="wfcm-test-email-response" class="hidden">
							<?php /* Translators: Contact us hyperlink */ ?>
							<p><?php echo sprintf( esc_html__( 'Oops! Email sending failed. Please %s for assistance.', 'website-file-changes-monitor' ), '<a href="https://www.wpwhitesecurity.com/support/?utm_source=plugin&utm_medium=referral&utm_campaign=WFCM&utm_content=help+page" target="_blank">' . esc_html__( 'contact us', 'website-file-changes-monitor' ) . '</a>' ); ?></p>
						</div>
					</fieldset>
				</td>
			</tr>
		</table>

		<!-- Scan results email -->
		<h3><?php esc_html_e( 'How many file changes should the plugin report in the email?', 'website-file-changes-monitor' ); ?></h3>
		<p class="description"><?php esc_html_e( 'To avoid long emails, by default the plugin only reports up to 10 changes per file type change. You can increase or decrease the number of reported file changes in the email from the below setting:', 'website-file-changes-monitor' ); ?></p>
		<table class="form-table wfcm-table">
			<tr>
				<th><label for="wfcm-settings-email-changes-limit"><?php esc_html_e( 'Number of file changes', 'website-file-changes-monitor' ); ?></label></th>
				<td>
					<fieldset <?php echo esc_attr( $disabled ); ?>>
						<input type="number" name="wfcm-settings[email-changes-limit]" min="5" max="1000" value="<?php echo esc_attr( $settings['email-changes-limit'] ); ?>" />
					</fieldset>
				</td>
			</tr>
		</table>

		<!-- Enable/Disable empty notification email -->
		<h3><?php esc_html_e( 'When should the plugin send you an email?', 'website-file-changes-monitor' ); ?></h3>
		<p class="description"><?php esc_html_e( 'Use the settings below to specify when the plugin should send you an email.', 'website-file-changes-monitor' ); ?></p>
		<table class="form-table wfcm-table">
			<tr>
				<th><label for="wfcm-send-email-upon-changes"><?php esc_html_e( 'Email settings', 'website-file-changes-monitor' ); ?></label></th>
				<td>
					<fieldset>
						<label for="wfcm-send-email-upon-changes">
							<input id="wfcm-send-email-upon-changes" type="checkbox" name="wfcm-settings[send-email-upon-changes]" value="yes" <?php checked( 'yes', $settings['send-email-upon-changes'], true ); ?>>
							<?php esc_html_e( 'Send me an email when file changes are detected', 'website-file-changes-monitor' ); ?>
						</label>
					</fieldset>
					<fieldset>
						<label for="wfcm-empty-email-allowed">
							<input id="wfcm-empty-email-allowed" type="checkbox" name="wfcm-settings[empty-email-allowed]" value="yes" <?php checked( 'yes', $settings['empty-email-allowed'], true ); ?>>
							<?php esc_html_e( 'Send me an email even when a scan finishes and no file changes are detected', 'website-file-changes-monitor' ); ?>
						</label>
					</fieldset>
				</td>
			</tr>
		</table>

		<!-- Scan times -->
		<h3><?php esc_html_e( 'When should the plugin scan your website for file changes?', 'website-file-changes-monitor' ); ?></h3>

		<table class="form-table wfcm-table">
			<tr>
				<th><label for="wfcm-settings-frequency"><?php esc_html_e( 'Scan frequency', 'website-file-changes-monitor' ); ?></label></th>
				<td>
					<fieldset <?php echo esc_attr( $disabled ); ?>>
						<select name="wfcm-settings[scan-frequency]">
							<?php foreach ( $frequency_options as $value => $html ) : ?>
								<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $value, $settings['frequency'] ); ?>><?php echo esc_html( $html ); ?></option>
							<?php endforeach; ?>
						</select>
					</fieldset>
				</td>
			</tr>
			<tr id="scan-time-row">
				<th><label for="wfcm-settings-scan-hour"><?php esc_html_e( 'Scan time', 'website-file-changes-monitor' ); ?></label></th>
				<td>
					<fieldset <?php echo esc_attr( $disabled ); ?>>
						<label>
							<?php
							$use_am_pm_select  = wfcm_is_time_format_am_pm();
							$selected_hour     = intval( $settings['hour'] );
							$selected_day_part = $selected_hour >= 12 ? 'PM' : 'AM';
							if ( $use_am_pm_select && 'PM' === $selected_day_part ) {
								$selected_hour -= 12;
							}
							$selected_hour = str_pad( $selected_hour, 2, '0', STR_PAD_LEFT );
							if ( $use_am_pm_select ) {
								$scan_hours = array_slice( $scan_hours, 0, 12, true );
							}
							?>
							<select name="wfcm-settings[scan-hour]">
								<?php foreach ( $scan_hours as $value => $html ) : ?>
									<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $value, $selected_hour ); ?>><?php echo esc_html( $html ); ?></option>
								<?php endforeach; ?>
							</select>
							<?php if ( $use_am_pm_select ): ?>
								<select name="wfcm-settings[scan-hour-am]">
									<?php foreach ( ['AM', 'PM'] as $value ) : ?>
										<option value="<?php echo esc_attr( strtolower($value) ); ?>" <?php selected( $value, $selected_day_part ); ?>><?php echo esc_html( $value ); ?></option>
									<?php endforeach; ?>
								</select>
							<?php endif; ?>
							<br />
							<span class="description"><?php esc_html_e( 'Hour', 'website-file-changes-monitor' ); ?></span>
						</label>

						<label>
							<select name="wfcm-settings[scan-day]">
								<?php foreach ( $scan_days as $value => $html ) : ?>
									<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $value, $settings['day'] ); ?>><?php echo esc_html( $html ); ?></option>
								<?php endforeach; ?>
							</select>
							<br />
							<span class="description"><?php esc_html_e( 'Day', 'website-file-changes-monitor' ); ?></span>
						</label>

						<label>
							<select name="wfcm-settings[scan-date]">
								<?php foreach ( $scan_date as $value => $html ) : ?>
									<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $value, $settings['date'] ); ?>><?php echo esc_html( $html ); ?></option>
								<?php endforeach; ?>
							</select>
							<br />
							<span class="description"><?php esc_html_e( 'Day', 'website-file-changes-monitor' ); ?></span>
						</label>
					</fieldset>
				</td>
			</tr>
		</table>
		<!-- Scan frequency -->

		<h3><?php esc_html_e( 'Which directories should be scanned for file changes?', 'website-file-changes-monitor' ); ?></h3>
		<p class="description"><?php esc_html_e( 'The plugin will scan all the directories in your WordPress website by default because that is the most secure option. Though if for some reason you do not want the plugin to scan any of these directories you can uncheck them from the below list.', 'website-file-changes-monitor' ); ?></p>
		<table class="form-table wfcm-table">
			<tbody>
				<tr>
					<th><label for="wfcm-settings-directories"><?php esc_html_e( 'Directories to scan', 'website-file-changes-monitor' ); ?></label></th>
					<td>
						<fieldset <?php echo esc_attr( $disabled ); ?>>
							<?php foreach ( $wp_directories as $value => $html ) : ?>
								<label>
									<?php $is_directory_enabled = in_array( $value, $settings['directories'], true ); ?>
									<input name="wfcm-settings[scan-directories][]" type="checkbox" value="<?php echo esc_attr( $value ); ?>" <?php echo $is_directory_enabled ? 'checked' : false; ?><?php if ( 'root' === $value ): ?> onchange="jQuery('.' + jQuery(this).data('toggle-class')).find('input').attr('disabled', !jQuery(this).is(':checked'));" data-toggle-class="js-core-check"/ <?php endif; ?>>
									<?php echo esc_html( $html ); ?>
								</label>
								<br />
							<?php if ( 'root' === $value ): ?>
								<label style="padding-left: 30px;" class="js-core-check">
									<?php $repo_check_enabled_value = $settings['wp-repo-core-checksum-validation-enabled']; ?>
									<input name="wfcm-settings[scan-wp-repo-core-checksum-validation-enabled]" type="checkbox" value="yes" <?php checked( 'yes', $repo_check_enabled_value, true ); ?><?php if ( !$is_directory_enabled ): ?> disabled="disabled"<?php endif; ?> />
									<?php echo esc_html( __('Enable checksum comparison of WordPress core files with the official WordPress repository', 'website-file-changes-monitor') ); ?>
								</label>
								<br />
							<?php endif; ?>
							<?php endforeach; ?>
						</fieldset>
					</td>
				</tr>
			</tbody>
		</table>
		<!-- Scan directories -->

		<h3><?php esc_html_e( 'What is the biggest file size the plugin should scan?', 'website-file-changes-monitor' ); ?></h3>
		<p class="description"><?php esc_html_e( 'By default the plugin does not scan files that are bigger than 5MB. Such files are not common, hence typically not a target.', 'website-file-changes-monitor' ); ?></p>
		<table class="form-table wfcm-table">
			<tr>
				<th><label for="wfcm-settings-file-size"><?php esc_html_e( 'File size limit', 'website-file-changes-monitor' ); ?></label></th>
				<td>
					<fieldset <?php echo esc_attr( $disabled ); ?>>
						<input type="number" name="wfcm-settings[scan-file-size]" min="1" max="100" value="<?php echo esc_attr( $settings['file-size'] ); ?>" /> <?php esc_html_e( 'MB', 'website-file-changes-monitor' ); ?>
					</fieldset>
				</td>
			</tr>
		</table>
		<!-- Maximum File Size -->

		<h3><?php esc_html_e( 'Scan common development folders?', 'website-file-changes-monitor' ); ?></h3>
		<p class="description"><?php esc_html_e( 'Some sites may contain special development folders like ".git", ".github", ".svg" and "node_modules". We do not scan these folders by default however if you would like to scan them check this box. Scanning these directories could take a long time if there are lots of files in these directories additionally these files can change frequently - it may result in many added/modified files notifications.', 'website-file-changes-monitor' ); ?></p>
		<table class="form-table">
			<tr>
				<th><label for="wfcm-debug-logging"><?php esc_html_e( 'Scan common development directories', 'website-file-changes-monitor' ); ?></label></th>
				<td>
					<fieldset>
						<input id="wfcm-debug-logging" type="checkbox" name="wfcm-settings[scan-dev-folders]" value="1" <?php checked( $settings['scan-dev-folders'] ); ?>>
					</fieldset>
				</td>
			</tr>
		</table>

		<h3><?php esc_html_e( 'Do you want to exclude specific files or files with a particular extension from the scan?', 'website-file-changes-monitor' ); ?></h3>
		<p class="description"><?php esc_html_e( 'The plugin will scan everything that is in the WordPress root directory or below, even if the files and directories are not part of WordPress. It is recommended to scan all source code files and only exclude files that cannot be tampered, such as text files, media files etc, most of which are already excluded by default.', 'website-file-changes-monitor' ); ?></p>
		<table class="form-table wfcm-table">
				<th><label for="wfcm-settings-exclude-dirs"><?php esc_html_e( 'Exclude all files in these directories', 'website-file-changes-monitor' ); ?></label></th>
				<td>
					<fieldset <?php echo esc_attr( $disabled ); ?>>
						<div class="wfcm-files-container">
							<div class="item-list exclude-list" id="wfcm-exclude-dirs-list">
								<?php foreach ( $settings['exclude-dirs'] as $dir ) : ?>
									<span>
										<input type="checkbox" name="wfcm-settings[scan-exclude-dirs][]" id="exclude-dirs-<?php echo esc_attr( $dir ); ?>" value="<?php echo esc_attr( $dir ); ?>" checked />
										<label for="exclude-dirs-<?php echo esc_attr( $dir ); ?>"><?php echo esc_html( $dir ); ?></label>
									</span>
								<?php endforeach; ?>
							</div>
							<input class="button remove" data-list-type="exclude" data-object-type="dirs" type="button" value="<?php esc_html_e( 'Remove', 'website-file-changes-monitor' ); ?>" />
						</div>
						<div class="wfcm-files-container">
							<input class="name" type="text">
							<input class="button add" data-list-type="exclude" data-object-type="dirs" type="button" value="<?php esc_html_e( 'Add', 'website-file-changes-monitor' ); ?>" />
						</div>
						<p class="description">
							<?php esc_html_e( 'Specify the name of the directory and the path to it in relation to the website\'s root. For example, if you want to exclude all files in the sub directory dir1/dir2 specify the following:', 'website-file-changes-monitor' ); ?>
							<br>
							<?php echo esc_html( trailingslashit( ABSPATH ) ) . 'dir1/dir2/'; ?>
						</p>
					</fieldset>
				</td>
			</tr>
			<!-- Exclude directories -->
			<tr>
				<th><label for="wfcm-settings-exclude-filenames"><?php esc_html_e( 'Exclude these files', 'website-file-changes-monitor' ); ?></label></th>
				<td>
					<fieldset <?php echo esc_attr( $disabled ); ?>>
						<div class="wfcm-files-container">
							<div class="item-list exclude-list" id="wfcm-exclude-files-list">
								<?php foreach ( $settings['exclude-files'] as $file ) : ?>
									<span>
										<input type="checkbox" name="wfcm-settings[scan-exclude-files][]" id="exclude-files-<?php echo esc_attr( $file ); ?>" value="<?php echo esc_attr( $file ); ?>" checked />
										<label for="exclude-files-<?php echo esc_attr( $file ); ?>"><?php echo esc_html( $file ); ?></label>
									</span>
								<?php endforeach; ?>
							</div>
							<input class="button remove" data-list-type="exclude" data-object-type="files" type="button" value="<?php esc_html_e( 'Remove', 'website-file-changes-monitor' ); ?>" />
						</div>
						<div class="wfcm-files-container">
							<input class="name" type="text">
							<input class="button add" data-list-type="exclude" data-object-type="files" type="button" value="<?php esc_html_e( 'Add', 'website-file-changes-monitor' ); ?>" />
						</div>
						<p class="description"><?php esc_html_e( 'Specify the name and extension of the file(s) you want to exclude. Wildcard not supported. There is no need to specify the path of the file.', 'website-file-changes-monitor' ); ?></p>
					</fieldset>
				</td>
			</tr>
			<!-- Exclude filenames -->
			<tr>
				<th><label for="wfcm-settings-exclude-extensions"><?php esc_html_e( 'Exclude these file types', 'website-file-changes-monitor' ); ?></label></th>
				<td>
					<fieldset <?php echo esc_attr( $disabled ); ?>>
						<div class="wfcm-files-container">
							<div class="item-list exclude-list" id="wfcm-exclude-exts-list">
								<?php foreach ( $settings['exclude-exts'] as $file_type ) : ?>
									<span>
										<input type="checkbox" name="wfcm-settings[scan-exclude-exts][]" id="<?php echo esc_attr( $file_type ); ?>" value="<?php echo esc_attr( $file_type ); ?>" checked />
										<label for="<?php echo esc_attr( $file_type ); ?>"><?php echo esc_html( $file_type ); ?></label>
									</span>
								<?php endforeach; ?>
							</div>
							<input class="button remove" data-list-type="exclude" data-object-type="exts" type="button" value="<?php esc_html_e( 'Remove', 'website-file-changes-monitor' ); ?>" />
						</div>
						<div class="wfcm-files-container">
							<input class="name" type="text">
							<input class="button add" data-list-type="exclude" data-object-type="exts" type="button" value="<?php esc_html_e( 'Add', 'website-file-changes-monitor' ); ?>" />
						</div>
						<p class="description"><?php esc_html_e( 'Specify the extension of the file types you want to exclude. You should exclude any type of logs and backup files that tend to be very big.', 'website-file-changes-monitor' ); ?></p>
					</fieldset>
				</td>
			</tr>
			<!-- Exclude extensions -->
		</table>
		<!-- Exclude directories, files, extensions -->

		<h3><?php esc_html_e( 'Which files are allowed as part of WordPress core (website root directory, wp-admin and wp-includes)?', 'website-file-changes-monitor' ); ?></h3>
		<p class="description"><?php _e( 'All non-WordPress core files on a website are saved in the uploads directory. However, you might have non-WordPress core files in the website root or in the core directories (wp-admin and wp-includes). If you do, add them to the below list so they are marked as legit files. Otherwise, the plugin will notify you about them with each scan. Note that by adding them to this list, it does not mean that the plugin won\'t alert you of any subsequent changes that happen to these files and / or in these directories. If you do not want to be alerted of any changes in these files or directory, exclude them from the scan.', 'website-file-changes-monitor' ); ?> <a href="https://www.wpwhitesecurity.com/support/kb/allowed-files-directories-root-core/?utm_source=plugin&utm_medium=referral&utm_campaign=WFCM&utm_content=settings+pages" target="_blank"><?php esc_html_e( 'More information', 'website-file-changes-monitor' ); ?></a></p>

		<table class="form-table wfcm-table">
			<th><label for="wfcm-settings-allowed-in-core-dirs"><?php esc_html_e( 'Allow all the files in these directories', 'website-file-changes-monitor' ); ?></label></th>
			<td>
				<fieldset <?php echo esc_attr( $disabled ); ?>>
					<div class="wfcm-files-container">
						<div class="item-list allowed-in-core-list" id="wfcm-allowed-in-core-dirs-list">
							<?php foreach ( $settings['allowed-in-core-dirs'] as $dir ) : ?>
								<span>
									<input type="checkbox" name="wfcm-settings[scan-allowed-in-core-dirs][]" id="allowed-in-core-dirs-<?php echo esc_attr( $dir ); ?>" value="<?php echo esc_attr( $dir ); ?>" checked />
									<label for="allowed-in-core-dirs-<?php echo esc_attr( $dir ); ?>"><?php echo esc_html( $dir ); ?></label>
								</span>
							<?php endforeach; ?>
						</div>
						<input class="button remove" data-list-type="allowed-in-core" data-object-type="dirs" type="button" value="<?php esc_html_e( 'Remove', 'website-file-changes-monitor' ); ?>" />
					</div>
					<div class="wfcm-files-container">
						<input class="name" type="text">
						<input class="button add" data-list-type="allowed-in-core" data-object-type="dirs" type="button" value="<?php esc_html_e( 'Add', 'website-file-changes-monitor' ); ?>"
							data-trigger-popup="<?php esc_html_e( 'When directories are added to this list, the plugin will consider them as part of your website\'s WordPress core. Therefore it will scan the files in them during the normal file integrity scans and will alert you if any are modified or deleted. If you do not want to be alerted of such changes about files in this directory, exclude it from the scan.', 'website-file-changes-monitor' ); ?>"
							data-trigger-popup-title="<?php esc_html_e( 'Adding an allowed directory', 'website-file-changes-monitor' ); ?>"
						/>
					</div>
					<p class="description">
						<?php esc_html_e( 'Specify the name of the directory and the path to it in relation to the website\'s root. For example, if you want to allow all files in the sub directory dir1/dir2 specify the following:', 'website-file-changes-monitor' ); ?>
						<br>
						<?php echo esc_html( trailingslashit( ABSPATH ) ) . 'dir1/dir2'; ?>
					</p>
				</fieldset>
			</td>
			</tr>
			<!-- Directories allowed in site root and WP core -->
			<tr>
				<th>
					<label for="wfcm-settings-allowed-in-core-filenames"><?php esc_html_e( 'Allow these files', 'website-file-changes-monitor' ); ?></label>
				</th>
				<td>
					<fieldset <?php echo esc_attr( $disabled ); ?>>
						<div class="wfcm-files-container">
							<div class="item-list allowed-in-core-list" id="wfcm-allowed-in-core-files-list">
								<?php foreach ( $settings['allowed-in-core-files'] as $file ) : ?>
									<span>
										<input type="checkbox" name="wfcm-settings[scan-allowed-in-core-files][]" id="allowed-in-core-files-<?php echo esc_attr( $file ); ?>" value="<?php echo esc_attr( $file ); ?>" checked />
										<label for="allowed-in-core-files-<?php echo esc_attr( $file ); ?>"><?php echo esc_html( $file ); ?></label>
									</span>
								<?php endforeach; ?>
							</div>
							<input class="button remove" data-list-type="allowed-in-core" data-object-type="files" type="button" value="<?php esc_html_e( 'Remove', 'website-file-changes-monitor' ); ?>" />
						</div>
						<div class="wfcm-files-container">
							<input class="name" type="text">
							<input class="button add" data-list-type="allowed-in-core" data-object-type="files" type="button" value="<?php esc_html_e( 'Add', 'website-file-changes-monitor' ); ?>"
								   data-trigger-popup="<?php esc_html_e( 'When files are added to this list, the plugin will consider them as part of your website\'s WordPress core. Therefore it will scan them during the normal file integrity scans and will alert you if they are modified or deleted. If you do not want to be alerted of such changes about this file, exclude it from the scan.', 'website-file-changes-monitor' ); ?>"
								   data-trigger-popup-title="<?php esc_html_e( 'Adding an allowed file', 'website-file-changes-monitor' ); ?>"
							/>
						</div>
						<p class="description"><?php esc_html_e( 'Specify the name and extension of the file(s) you want to allow in the website root and core directories. Wildcard not supported. There is no need to specify the path of the file.', 'website-file-changes-monitor' ); ?></p>
					</fieldset>
				</td>
			</tr>
			<!-- Files allowed in site root and WP core -->
		</table>
		<!-- Directories and files allowed in site root and WP core -->

		<h3><?php esc_html_e( 'Launch an instant file changes scan', 'website-file-changes-monitor' ); ?></h3>
		<p class="description"><?php esc_html_e( 'Click the "Scan now" button to launch an instant file changes scan using the configured settings. You can navigate away from this page during the scan. Note that the instant scan can be more resource intensive than scheduled scans.', 'website-file-changes-monitor' ); ?></p>
		<table class="form-table wfcm-table">
			<tbody>
				<tr>
					<th>
						<label><?php esc_html_e( 'Launch instant scan', 'website-file-changes-monitor' ); ?></label>
					</th>
					<td>
						<fieldset <?php echo esc_attr( $disabled ); ?>>
							<?php if ( 'yes' === $settings['enabled'] && ! wfcm_get_setting( 'scan-in-progress', false ) ) : ?>
								<input type="button" class="button-primary" id="wfcm-scan-start" value="<?php esc_attr_e( 'Scan now', 'website-file-changes-monitor' ); ?>">
								<input type="button" class="button-secondary" id="wfcm-scan-stop" value="<?php esc_attr_e( 'Stop scan', 'website-file-changes-monitor' ); ?>" disabled>
							<?php elseif ( 'no' === $settings['enabled'] && wfcm_get_setting( 'scan-in-progress', false ) ) : ?>
								<input type="button" class="button button-primary" id="wfcm-scan-start" value="<?php esc_attr_e( 'Scan in progress', 'website-file-changes-monitor' ); ?>" disabled>
								<input type="button" class="button button-ui-primary" id="wfcm-scan-stop" value="<?php esc_attr_e( 'Stop scan', 'website-file-changes-monitor' ); ?>">
								<!-- Scan in progress -->
							<?php else : ?>
								<input type="button" class="button button-primary" id="wfcm-scan-start" value="<?php esc_attr_e( 'Scan now', 'website-file-changes-monitor' ); ?>" disabled>
								<input type="button" class="button button-secondary" id="wfcm-scan-stop" value="<?php esc_attr_e( 'Stop scan', 'website-file-changes-monitor' ); ?>" disabled>
							<?php endif; ?>
						</fieldset>
						<div id="wfcm-scan-response" class="hidden">
							<?php /* Translators: Contact us hyperlink */ ?>
							<p><?php echo sprintf( esc_html__( 'Oops! Something went wrong with the scan. Please %s for assistance.', 'website-file-changes-monitor' ), '<a href="https://www.wpwhitesecurity.com/support/?utm_source=plugin&utm_medium=referral&utm_campaign=WFCM&utm_content=help+page" target="_blank">' . esc_html__( 'contact us', 'website-file-changes-monitor' ) . '</a>' ); ?></p>
						</div>
					</td>
				</tr>
			</tbody>
		</table>
		<!-- / Instant Scan -->

		<h3><?php esc_html_e( 'Temporarily disable file scanning', 'website-file-changes-monitor' ); ?></h3>
		<p class="description"><?php esc_html_e( 'Use the below switch to disable file scanning. When you disable and re-enable scanning, the plugin will compare the file scan to those of the last scan before it was disabled.', 'website-file-changes-monitor' ); ?></p>
		<table class="form-table">
			<tr>
				<th><label for="wfcm-file-changes"><?php esc_html_e( 'File scanning', 'website-file-changes-monitor' ); ?></label></th>
				<td>
					<fieldset>
						<?php esc_html_e( 'Off', 'website-file-changes-monitor' ); ?>
						<div class="wfcm-toggle">
							<label for="wfcm-toggle__switch-keep-log">
								<input type="checkbox" id="wfcm-toggle__switch-keep-log" name="wfcm-settings[keep-log]" value="yes" <?php checked( $settings['enabled'], 'yes' ); ?>>
								<span class="wfcm-toggle__switch"></span>
								<span class="wfcm-toggle__toggle"></span>
							</label>
						</div>
						<?php esc_html_e( 'On', 'website-file-changes-monitor' ); ?>
					</fieldset>
				</td>
			</tr>
		</table>
		<!-- Disable File Changes -->

		<h3><?php esc_html_e( 'Debug & uninstall settings', 'website-file-changes-monitor' ); ?></h3>
		<p class="description">
			<?php esc_html_e( 'Enable the debug logging when requested by support. This is used for support.', 'website-file-changes-monitor' ); ?> <?php esc_html_e( 'The debug log file is saved in the /wp-content/uploads/wfcm-logs/ folder on your website.', 'website-file-changes-monitor' ); ?>
		</p>
		<table class="form-table">
			<tr>
				<th><label for="wfcm-debug-logging"><?php esc_html_e( 'Debug logs', 'website-file-changes-monitor' ); ?></label></th>
				<td>
					<fieldset>
						<input id="wfcm-debug-logging" type="checkbox" name="wfcm-settings[debug-logging]" value="1" <?php checked( $settings['debug-logging'] ); ?>>
					</fieldset>
				</td>
			</tr>
		</table>

		<table class="form-table wfcm-settings-danger">
			<tr>
				<th><label for="wfcm-delete-data"><?php esc_html_e( 'Delete plugin data upon uninstall', 'website-file-changes-monitor' ); ?></label></th>
				<td>
					<fieldset>
						<input id="wfcm-delete-data" name="wfcm-settings[delete-data]" type="checkbox" value="1" <?php checked( $settings['delete-data'] ); ?>>
					</fieldset>
				</td>
			</tr>
		</table>
		<!-- Delete plugin data and settings -->

		<?php
		wp_nonce_field( 'wfcm-save-admin-settings' );
		submit_button();
		?>
	</form>
</div>
