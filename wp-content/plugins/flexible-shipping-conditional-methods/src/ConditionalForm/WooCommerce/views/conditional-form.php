<?php
/**
 * @package WPDesk\FS\ConditionalMethods\ConditionalForm\WooCommerce
 */

use WPDesk\FS\ConditionalMethods\ConditionalForm\OptionField;
use WPDesk\FS\ConditionalMethods\ConditionalForm\WooCommerce\ConditionalFormFieldSettings;

/**
 * @var string                       $settings_field_id
 * @var string                       $settings_field_name
 * @var string                       $settings_variable
 * @var string                       $settings_field_title
 * @var string                       $settings_field_class
 * @var array[]                      $settings
 * @var OptionField[]                $available_options
 * @var ConditionalFormFieldSettings $conditional_form_settings
 * @var string                       $desc
 *
 * @package WPDesk\FS\ConditionalMethods\ConditionalForm\WooCommerce
 */

$field_settings = json_encode(
	array(
		'settings'          => $settings,
		'available_options' => $available_options,
		'field_settings'    => $conditional_form_settings,
	),
	JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT
);

?>
<tr valign="top" class="flexible-shipping-conditional-form">
	<th class="forminp" colspan="2">
		<label for="<?php echo esc_attr( $settings_field_name ); ?>"><?php echo wp_kses_post( $settings_field_title ); ?></label>
	</th>
</tr>

<tr valign="top" class="flexible-shipping-conditional-form-settings">
	<td colspan="2" style="padding:0;" class="">
		<script type="text/javascript">
			var <?php echo esc_attr( $settings_variable ); ?> = <?php echo $field_settings;	// phpcs:ignore. ?>;

			document.addEventListener( "DOMContentLoaded", function ( event ) {
				document.querySelector( '#mainform button[name="save"]' ).addEventListener( "click", function ( event ) {
					if ( null === document.getElementById( '<?php echo esc_attr( $settings_field_id ); ?>[control_field]' ) ) {
						event.preventDefault();
						alert( '<?php echo esc_attr( __( 'Missing settings table - settings cannot be saved!', 'flexible-shipping-conditional-methods' ) ); ?>' );
					}
				} );
			} );
		</script>

		<p class="description"><?php echo wp_kses_post( $desc ); ?></p><br />

		<div class="flexible-shipping-conditional-form-settings-div <?php echo esc_attr( $settings_field_class ); ?>" id="<?php echo esc_attr( $settings_field_id ); ?>"
			 data-settings-field-name="<?php echo esc_attr( $settings_field_name ); ?>"
			 data-settings-var="<?php echo esc_attr( $settings_variable ); ?>"
		>
			<div id="<?php echo esc_attr( $settings_field_id ); ?>-notice" class="notice notice-error inline hidden">
				<?php echo wpautop( wp_kses_post( __( 'This is where the settings table should be displayed. If it\'s not, it is usually caused by the conflict with the other plugins you are currently using, JavaScript error or the caching issue. Clear your browser\'s cache or deactivate the plugins which may be interfering.', 'flexible-shipping-conditional-methods' ) ) ); // phpcs:ignore. ?>
			</div>
			<script type="text/javascript">
				setTimeout( function() {
						let element = document.getElementById( "<?php echo esc_attr( $settings_field_id ); ?>-notice" );
						if ( element ) {
							element.classList.remove("hidden");
						}
					},
					1000
				);
			 </script>
		</div>
	</td>
</tr>
