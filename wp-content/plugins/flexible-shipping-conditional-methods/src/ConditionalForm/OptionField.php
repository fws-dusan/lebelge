<?php
/**
 * Interface ConditionField
 *
 * @package WPDesk\FS\ConditionalMethods\ConditionalForm
 */

namespace WPDesk\FS\ConditionalMethods\ConditionalForm;

use FSConditionalMethodsVendor\WPDesk\Forms\Field;

/**
 * Condition Field.
 */
interface OptionField {

	/**
	 * @return string
	 */
	public function get_option_id();

	/**
	 * @return string
	 */
	public function get_name();

	/**
	 * @return string
	 */
	public function get_description();

	/**
	 * @return string
	 */
	public function get_group();

	/**
	 * @return int
	 */
	public function get_priority();

	/**
	 * @return Field[]
	 */
	public function get_fields();

	/**
	 * @param array<string, string|array> $option_settings .
	 *
	 * @return array<string, string|array>
	 */
	public function prepare_settings( $option_settings );

}
