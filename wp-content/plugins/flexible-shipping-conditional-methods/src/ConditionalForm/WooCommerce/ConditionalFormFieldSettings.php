<?php
/**
 * Class ConditionalFormFieldSettings
 *
 * @package WPDesk\FS\ConditionalMethods\ConditionalForm\WooCommerce
 */

namespace WPDesk\FS\ConditionalMethods\ConditionalForm\WooCommerce;

/**
 * Can serialize conditional form settings.
 */
class ConditionalFormFieldSettings implements \JsonSerializable {

	/**
	 * @var string
	 */
	private $table_header_label;

	/**
	 * @var string
	 */
	private $button_add_label;

	/**
	 * @var string
	 */
	private $button_duplicate_selected_label;

	/**
	 * @var string
	 */
	private $button_delete_selected_label;

	/**
	 * @var string
	 */
	private $add_first_setting_label;

	/**
	 * @var bool
	 */
	private $or_condition_available;

	/**
	 * @var string
	 */
	private $or_condition_button_label;

	/**
	 * @var string
	 */
	private $or_condition_row_label;

	/**
	 * @var string
	 */
	private $first_option_label;

	/**
	 * @var string
	 */
	private $next_option_label;

	/**
	 * ConditionalFormFieldSettings constructor.
	 *
	 * @param string $table_header_label .
	 * @param string $button_add_label .
	 * @param string $button_duplicate_selected_label .
	 * @param string $button_delete_selected_label .
	 * @param string $add_first_setting_label .
	 * @param bool   $or_condition_available .
	 * @param string $or_condition_button_label .
	 * @param string $or_condition_row_label .
	 * @param string $first_option_label .
	 * @param string $next_option_label .
	 */
	public function __construct(
		$table_header_label,
		$button_add_label,
		$button_duplicate_selected_label,
		$button_delete_selected_label,
		$add_first_setting_label,
		$or_condition_available = false,
		$or_condition_button_label = '',
		$or_condition_row_label = '',
		$first_option_label = '',
		$next_option_label = ''
	) {
		$this->table_header_label              = $table_header_label;
		$this->button_add_label                = $button_add_label;
		$this->button_duplicate_selected_label = $button_duplicate_selected_label;
		$this->button_delete_selected_label    = $button_delete_selected_label;
		$this->add_first_setting_label         = $add_first_setting_label;
		$this->or_condition_available          = $or_condition_available;
		$this->or_condition_button_label       = $or_condition_button_label;
		$this->or_condition_row_label          = $or_condition_row_label;
		$this->first_option_label              = $first_option_label;
		$this->next_option_label               = $next_option_label;
	}


	/**
	 * @return array<string, string|bool>
	 */
	public function jsonSerialize() {
		return array(
			'table_header_label'              => $this->table_header_label,
			'button_add_label'                => $this->button_add_label,
			'button_duplicate_selected_label' => $this->button_duplicate_selected_label,
			'button_delete_selected_label'    => $this->button_delete_selected_label,
			'add_first_setting_label'         => $this->add_first_setting_label,
			'or_condition_available'          => $this->or_condition_available,
			'or_condition_button_label'       => $this->or_condition_button_label,
			'or_condition_row_label'          => $this->or_condition_row_label,
			'first_option_label'              => $this->first_option_label,
			'next_option_label'               => $this->next_option_label,
		);
	}


}
