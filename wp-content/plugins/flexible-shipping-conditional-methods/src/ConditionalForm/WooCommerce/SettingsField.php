<?php
/**
 * Class SettingsField
 *
 * @package WPDesk\FS\ConditionalMethods\ConditionalForm\WooCommerce
 */

namespace WPDesk\FS\ConditionalMethods\ConditionalForm\WooCommerce;

use WPDesk\FS\ConditionalMethods\ConditionalForm\OptionField;

/**
 * Settings Field.
 */
class SettingsField {

	const FIELD_TYPE = 'flexible_shipping_conditional_methods_conditional_form';

	/**
	 * @var string
	 */
	protected static $assets_url;

	/**
	 * @var string
	 */
	private $settings_field_id;

	/**
	 * @var string
	 */
	private $settings_field_name;

	/**
	 * @var string
	 */
	private $settings_variable;

	/**
	 * @var string
	 */
	private $settings_field_title;

	/**
	 * @var string
	 */
	private $settings_field_class;

	/**
	 * @var OptionField[]
	 */
	private $available_options;

	/**
	 * @var array[]
	 */
	private $value;

	/**
	 * @var ConditionalFormFieldSettings
	 */
	private $conditional_form_settings;

	/**
	 * @var string
	 */
	private $desc;

	/**
	 * RulesSettings constructor.
	 *
	 * @param string                       $settings_field_id         .
	 * @param string                       $settings_field_name       .
	 * @param string                       $settings_variable         .
	 * @param string                       $settings_field_title      .
	 * @param string                       $settings_field_class      .
	 * @param array[]                      $value                     .
	 * @param array<string, OptionField>   $available_options         .
	 * @param ConditionalFormFieldSettings $conditional_form_settings .
	 * @param string                       $desc                      .
	 */
	public function __construct( $settings_field_id, $settings_field_name, $settings_variable, $settings_field_title, $settings_field_class, array $value, array $available_options, ConditionalFormFieldSettings $conditional_form_settings, $desc ) {
		$this->settings_field_id         = $settings_field_id;
		$this->settings_field_name       = $settings_field_name;
		$this->settings_variable         = $settings_variable;
		$this->value                     = $value;
		$this->settings_field_title      = $settings_field_title;
		$this->settings_field_class      = $settings_field_class;
		$this->available_options         = $available_options;
		$this->value                     = $value;
		$this->conditional_form_settings = $conditional_form_settings;
		$this->desc                      = $desc;
	}

	/**
	 * Render settings.
	 *
	 * @return string
	 */
	public function render() {
		ob_start();
		$settings_field_id         = $this->settings_field_id;
		$settings_field_name       = $this->settings_field_name;
		$settings_field_title      = $this->settings_field_title;
		$settings_field_class      = $this->settings_field_class;
		$settings_variable         = $this->settings_variable;
		$settings                  = $this->prepare_settings( $this->value );
		$available_options         = array_values( $this->available_options );
		$conditional_form_settings = $this->conditional_form_settings;
		$desc                      = $this->desc;

		include __DIR__ . '/views/conditional-form.php';

		$out = ob_get_clean();

		return $out ? $out : '';
	}

	/**
	 * @param array[] $settings .
	 *
	 * @return array[]
	 */
	private function prepare_settings( array $settings ) {
		$prepared_settings = array();
		foreach ( $settings as $single_setting_row ) {
			$prepared_settings[] = 'option' === $single_setting_row['type'] ? $this->prepare_settings_for_fields( $single_setting_row ) : $single_setting_row;
		}

		return $prepared_settings;
	}

	/**
	 * @param array<string, string|array> $single_settings_row .
	 *
	 * @return array<string, string|array>
	 */
	private function prepare_settings_for_fields( $single_settings_row ) {
		$option_id = is_string( $single_settings_row['option_id'] ) ? $single_settings_row['option_id'] : '';
		if ( isset( $this->available_options[ $option_id ] ) ) {

			return $this->available_options[ $option_id ]->prepare_settings( $single_settings_row );
		}

		return $single_settings_row;
	}

}
