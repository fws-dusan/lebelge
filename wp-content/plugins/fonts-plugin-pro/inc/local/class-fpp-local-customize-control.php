<?php
/**
 * Build the customizer controls for Local Hosting options.
 *
 * @package   fonts-plugin-pro
 * @copyright Copyright (c) 2019, Fonts Plugin
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

/**
 * FPP_Local_Customize_Control Class.
 */
class FPP_Local_Customize_Control {

	/**
	 * The constructor.
	 */
	public function __construct() {
		add_action( 'customize_register', array( $this, 'register_section' ) );
		add_action( 'customize_register', array( $this, 'register_setting' ) );
	}

	/**
	 * Register the Customizer section.
	 *
	 * @param WP_Customize_Manager $wp_customize the Customizer object.
	 */
	public function register_section( $wp_customize ) {
		$wp_customize->add_section(
			'fpp_local_hosting',
			array(
				'title'       => __( 'Local Hosting', 'fonts-plugin-pro' ),
				'description' => __( 'Optimize the delivery of font files for improved performance and user-privacy.', 'fonts-plugin-pro' ),
				'priority'    => '4',
				'panel'       => 'ogf_google_fonts',
			)
		);
	}

	/**
	 * Register the Customizer setting.
	 *
	 * @param WP_Customize_Manager $wp_customize the Customizer object.
	 */
	public function register_setting( $wp_customize ) {
		// Add custom control.
		require FPP_DIR_PATH . '/inc/controls/class-fpp-customize-toggle.php';

		// Register the custom control type.
		$wp_customize->register_control_type( 'FPP_Customize_Toggle' );

		$site_url = site_url( '', 'https' );
		$url      = preg_replace( '(^https?://)', '', $site_url );

		// Add an option to disable the logo.
		$wp_customize->add_setting(
			'fpp_host_locally',
			array(
				'default'           => false,
				'transport'         => 'postMessage',
				'sanitize_callback' => 'fpp_sanitize_checkbox',
			)
		);

		$wp_customize->add_control(
			new FPP_Customize_Toggle(
				$wp_customize,
				'fpp_host_locally',
				array(
					'label'       => esc_html__( 'Host Fonts Locally', 'fonts-plugin-pro' ),
					'description' => esc_html__( 'Fonts will be served from ' . $url . ' instead of fonts.googleapis.com', 'fonts-plugin-pro' ),
					'section'     => 'fpp_local_hosting',
					'type'        => 'toggle',
					'settings'    => 'fpp_host_locally',
				)
			)
		);

		$wp_customize->add_setting(
			'fpp_use_woff2',
			array(
				'default'           => false,
				'transport'         => 'postMessage',
				'sanitize_callback' => 'fpp_sanitize_checkbox',
			)
		);

		$wp_customize->add_control(
			new FPP_Customize_Toggle(
				$wp_customize,
				'fpp_use_woff2',
				array(
					'label'       => esc_html__( 'Use WOFF2 File Format', 'fonts-plugin-pro' ),
					'description' => esc_html__( 'Use the optimized WOFF2 format instead of WOFF. 30%+ faster.', 'fonts-plugin-pro' ),
					'section'     => 'fpp_local_hosting',
					'type'        => 'toggle',
					'settings'    => 'fpp_use_woff2',
				)
			)
		);

		$wp_customize->add_setting(
			'fpp_preloading',
			array(
				'default'           => false,
				'transport'         => 'postMessage',
				'sanitize_callback' => 'fpp_sanitize_checkbox',
			)
		);

		$wp_customize->add_control(
			new FPP_Customize_Toggle(
				$wp_customize,
				'fpp_preloading',
				array(
					'label'           => esc_html__( 'Enable Preloading', 'fonts-plugin-pro' ),
					'description'     => esc_html__( 'Add preload resource hints.', 'fonts-plugin-pro' ),
					'section'         => 'fpp_local_hosting',
					'type'            => 'toggle',
					'settings'        => 'fpp_preloading',
					'active_callback' => 'fpp_local_hosting_is_active',
				)
			)
		);
	}
}

$local_customize_control = new FPP_Local_Customize_Control();

/**
 * Checkbox sanitization callback example.
 *
 * Sanitization callback for 'checkbox' type controls. This callback sanitizes `$checked`
 * as a boolean value, either TRUE or FALSE.
 *
 * @param bool $checked Whether the checkbox is checked.
 * @return bool Whether the checkbox is checked.
 */
function fpp_sanitize_checkbox( $checked ) {
	// Boolean check.
	return ( ( isset( $checked ) && true === $checked ) ? true : false );
}

/**
 * Check if WOFF or WOFF2 font format is used.
 */
function fpp_local_hosting_is_active() {
	return ! get_theme_mod( 'fpp_use_woff2', false );
}
