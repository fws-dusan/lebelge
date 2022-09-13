<?php
/**
 * Filtes to alter the core plugin
 *
 * @package   fonts-plugin-pro
 * @copyright Copyright (c) 2019, Danny Cooper
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

/**
 * Add hyperlink control.
 *
 * @param array $elements An array of elements from the olympus-google-fonts plugin.
 */
function fpp_add_hyperlinks( $elements ) {

	$elements['fpp_content_hyperlinks'] = array(
		'label'       => esc_html__( 'Hyperlinks', 'fonts-plugin-pro' ),
		'description' => esc_html__( 'Customize the typography and styling of links.', 'fonts-plugin-pro' ),
		'section'     => 'ogf_advanced__content',
		'priority'    => '15',
		'selectors'   => '.content a, .entry-content a, .post-content a, .page-content a, .post-excerpt a, .entry-summary a, .entry-excerpt a, .excerpt a',
	);
	$elements['fpp_sidebar_hyperlinks'] = array(
		'label'       => esc_html__( 'Hyperlinks', 'fonts-plugin-pro' ),
		'description' => esc_html__( 'Customize the typography and styling of links.', 'fonts-plugin-pro' ),
		'section'     => 'ogf_advanced__sidebar',
		'priority'    => '15',
		'selectors'   => '.widget-area a, .widget a, .sidebar a, #sidebar a',
	);
	$elements['fpp_footer_hyperlinks']  = array(
		'label'       => esc_html__( 'Hyperlinks', 'fonts-plugin-pro' ),
		'description' => esc_html__( 'Customize the typography and styling of links.', 'fonts-plugin-pro' ),
		'section'     => 'ogf_advanced__footer',
		'priority'    => '15',
		'selectors'   => 'footer a, #footer a, .footer a, .site-footer a, .site-info a',
	);
	return $elements;

}
add_filter( 'ogf_elements', 'fpp_add_hyperlinks' );

/**
 * Helper function to build the CSS variables.
 */
function fpp_responsive_styles( $options, $device, $breakpoint ) {

	$return = '@media only screen and (max-width: ' . esc_attr( $breakpoint ) . ') {' . PHP_EOL;

	foreach ( $options as $key => $value ) {
		$font_size   = get_theme_mod( $key . '_font_size_' . $device, false );
		$line_height = get_theme_mod( $key . '_line_height_' . $device, false );

		if ( $font_size || $line_height ) {

			$return .= $value['selectors'] . '{' . PHP_EOL;

				// Return font-size CSS.
				if ( $font_size ) {
					$return .= sprintf(
						'font-size: %s;' . PHP_EOL,
						floatval( $font_size ) . 'px' . ogf_is_forced()
					);
				}

				// Return font line-height CSS.
				if ( $line_height && '0' !== $line_height ) {
					$return .= sprintf(
						'line-height: %s;' . PHP_EOL,
						floatval( $line_height ) . ogf_is_forced()
					);
				}

			$return .= '}' . PHP_EOL;

		}
	}

	$return .= ' }' . PHP_EOL;

	echo wp_kses_post( $return );

}

function fpp_mobile_styles() {
	$mobile_size = apply_filters( 'fpp_mobile_breakpoint', '400px' );
	fpp_responsive_styles( ogf_get_elements(), 'mobile', $mobile_size );
	fpp_responsive_styles( ogf_get_custom_elements(), 'mobile', $mobile_size );
}

function fpp_tablet_styles() {
	$tablet_size = apply_filters( 'fpp_tablet_breakpoint', '800px' );
	fpp_responsive_styles( ogf_get_elements(), 'tablet', $tablet_size );
	fpp_responsive_styles( ogf_get_custom_elements(), 'tablet', $tablet_size );
}

add_action( 'ogf_after_inline_styles', 'fpp_tablet_styles', 10 );
add_action( 'ogf_after_inline_styles', 'fpp_mobile_styles', 20 );

add_filter( 'ogf_typography_control_settings',  'fpp_add_text_decoration_control', 10, 2 );

function fpp_add_text_decoration_control( $array, $id ) {

	$disallowed_controls = [
		'ogf_body',
		'ogf_inputs',
		'ogf_post_page_content',
		'ogf_blockquotes',
		'ogf_sidebar_content',
		'ogf_footer_content',
	];

	if ( in_array( $id, $disallowed_controls ) ) {
		return $array;
	}

	$array['text_decoration'] = $id . '_text_decoration';
	return $array;
}
