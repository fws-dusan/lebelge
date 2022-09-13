<?php
/**
 * Toggle Customizer Control
 *
 * @package   fonts-plugin-pro
 * @copyright Copyright (c) 2019, Danny Cooper
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

// Exit if WP_Customize_Control does not exsist.
if ( ! class_exists( 'WP_Customize_Control' ) ) {
	return null;
}

/**
 * This class is for the toggle control in the Customizer.
 *
 * @access public
 */
class FPP_Customize_Toggle extends WP_Customize_Control {

	/**
	 * The type of customize control.
	 *
	 * @var string
	 */
	public $type = 'toggle';

	/**
	 * Enqueue scripts and styles.
	 */
	public function enqueue() {
		wp_enqueue_script( 'fpp-toggle-control-scripts', FPP_DIR_URL . '/assets/js/toggle.js', array( 'jquery' ), '1.0.0', true );
	}

	/**
	 * Add custom parameters to pass to the JS via JSON.
	 */
	public function to_json() {
		parent::to_json();
		// The setting value.
		$this->json['id']           = $this->id;
		$this->json['value']        = $this->value();
		$this->json['link']         = $this->get_link();
		$this->json['defaultValue'] = $this->setting->default;
	}

	/**
	 * Don't render the content via PHP.  This control is handled with a JS template.
	 */
	public function render_content() {}

	/**
	 * An Underscore (JS) template for this control's content.
	 *
	 * Class variables for this control class are available in the `data` JS object;
	 * export custom variables by overriding {@see WP_Customize_Control::to_json()}.
	 *
	 * @see    WP_Customize_Control::print_template()
	 */
	protected function content_template() {
		?>
		<label class="toggle">
			<div class="toggle--wrapper">

				<# if ( data.label ) { #>
					<span class="customize-control-title">{{ data.label }}</span>
				<# } #>

				<input id="toggle-{{ data.id }}" type="checkbox" class="toggle--input" value="{{ data.value }}" {{{ data.link }}} <# if ( data.value ) { #> checked="checked" <# } #> />
				<label for="toggle-{{ data.id }}" class="toggle--label"></label>
			</div>

			<# if ( data.description ) { #>
				<span class="description customize-control-description">{{ data.description }}</span>
			<# } #>
		</label>
		<?php
	}
}
