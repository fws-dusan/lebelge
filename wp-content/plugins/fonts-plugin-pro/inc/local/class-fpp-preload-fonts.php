<?php
/**
 * Handles the local font preloading.
 *
 * @package   fonts-plugin-pro
 * @copyright Copyright (c) 2019, Fonts Plugin
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

/**
 * Add preload resource hints to wp_head().
 */
class FPP_Preload_Fonts {

	/**
	 * The local fonts loader object.
	 *
	 * @var FPP_Host_Google_Fonts_Locally
	 */
	public $loader = null;

	/**
	 * The constructor.
	 *
	 * @param FPP_Host_Google_Fonts_Locally $loader The local fonts loader object.
	 */
	public function __construct( $loader ) {
		$this->loader = $loader;
		add_action( 'wp_head', array( $this, 'init' ), 1 );
	}

	/**
	 * Preload the fonts.
	 */
	public function init() {

		// Early return if preloading is disabled.
		if ( ! get_theme_mod( 'fpp_preloading', false ) ) {
			return;
		}

		// Disable preloading if unicode ranges are supported.
		if ( fpp_get_font_format() === 'woff2' ) {
			return;
		}

		$this->loader->set_font_format( fpp_get_font_format() );

		// Get an array of locally-hosted files.
		$files = $this->get_remote_files_from_css( $this->loader->get_styles() );

		// Convert paths to URLs.
		foreach ( $files as $font ) {
			foreach ( $font as $google_url ) {
					$local_url = str_replace(
						$this->loader->get_base_path(),
						$this->loader->get_base_url(),
						$google_url
					);

				echo '<link rel="preload" as="font" href="' . esc_url( $local_url ) . '" crossorigin>' . PHP_EOL;
			}
		}
	}

	/**
	 * Retrieve an array of remote font files.
	 *
	 * @param array $styles Styles pulled from the remote CSS file.
	 */
	public function get_remote_files_from_css( $styles ) {

		$font_faces = explode( '@font-face', $styles );

		$result = array();

		// Loop all our font-face declarations.
		foreach ( $font_faces as $font_face ) {

			// Make sure we only process styles inside this declaration.
			$style = explode( '}', $font_face )[0];

			// Sanity check.
			if ( false === strpos( $style, 'font-family' ) ) {
				continue;
			}

			// Get an array of our font-families.
			preg_match_all( '/font-family.*?\;/', $style, $matched_font_families );

			// Get an array of our font-files.
			preg_match_all( '/url\(.*?\)/i', $style, $matched_font_files );

			// Get the font-family name.
			$font_family = 'unknown';
			if ( isset( $matched_font_families[0] ) && isset( $matched_font_families[0][0] ) ) {
				$font_family = rtrim( ltrim( $matched_font_families[0][0], 'font-family:' ), ';' );
				$font_family = trim( str_replace( array( "'", ';' ), '', $font_family ) );
				$font_family = sanitize_key( strtolower( str_replace( ' ', '-', $font_family ) ) );
			}

			// Make sure the font-family is set in our array.
			if ( ! isset( $result[ $font_family ] ) ) {
				$result[ $font_family ] = array();
			}

			// Get files for this font-family and add them to the array.
			foreach ( $matched_font_files as $match ) {

				// Sanity check.
				if ( ! isset( $match[0] ) ) {
					continue;
				}

				// Add the file URL.
				$result[ $font_family ][] = rtrim( ltrim( $match[0], 'url(' ), ')' );
			}

			// Make sure we have unique items.
			// We're using array_flip here instead of array_unique for improved performance.
			$result[ $font_family ] = array_flip( array_flip( $result[ $font_family ] ) );
		}
		return $result;
	}

}

$fonts = new OGF_Fonts();

if ( $fonts->has_google_fonts() ) {
	$url     = $fonts->build_url();
	$loader  = new FPP_Host_Google_Fonts_Locally( $url );
	$preload = new FPP_Preload_Fonts( $loader );
}
