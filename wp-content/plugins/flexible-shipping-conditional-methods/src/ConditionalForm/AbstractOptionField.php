<?php
/**
 * Class AbstractOptionField
 *
 * @package WPDesk\FS\ConditionalMethods\ConditionalForm
 */

namespace WPDesk\FS\ConditionalMethods\ConditionalForm;

use FSConditionalMethodsVendor\WPDesk\Forms\Field;
use FSConditionalMethodsVendor\WPDesk\Forms\FieldProvider;
use FSConditionalMethodsVendor\WPDesk\Forms\Renderer\JsonNormalizedRenderer;
use JsonSerializable;

/**
 * Abstract option field.
 */
class AbstractOptionField  implements OptionField, FieldProvider, JsonSerializable {

	/**
	 * @var string
	 */
	protected $option_id;

	/**
	 * @var string
	 */
	protected $name;

	/**
	 * @var string
	 */
	protected $description;

	/**
	 * @var string
	 */
	protected $group;

	/**
	 * @var int
	 */
	protected $priority;

	/**
	 * AbstractConditionField constructor.
	 *
	 * @param string $option_id .
	 * @param string $name .
	 * @param string $description .
	 * @param string $group .
	 * @param int    $priority .
	 */
	public function __construct( $option_id, $name, $description = '', $group = null, $priority = 10 ) {
		$this->option_id   = $option_id;
		$this->name        = $name;
		$this->description = $description;
		$this->group       = $group ? $group : _x( 'General', 'Default Condition Group', 'flexible-shipping-conditional-methods' );
		$this->priority    = $priority;
	}

	/**
	 * @return string
	 */
	public function get_option_id() {
		return $this->option_id;
	}

	/**
	 * @return string
	 */
	public function get_name() {
		return $this->name;
	}

	/**
	 * @return string
	 */
	public function get_description() {
		return $this->description;
	}

	/**
	 * @return int
	 */
	public function get_priority() {
		return $this->priority;
	}

	/**
	 * @return string
	 */
	public function get_group() {
		return $this->group;
	}

	/**
	 * @return Field[]
	 */
	public function get_fields() {
		return array();
	}

	/**
	 * @param array<string, string|array> $option_settings .
	 *
	 * @return array<string, string|array>
	 */
	public function prepare_settings( $option_settings ) {
		return $option_settings;
	}

	/**
	 * @return array<string, int|string|array>
	 */
	public function jsonSerialize() {
		$renderer = new JsonNormalizedRenderer();

		return array(
			'option_id'    => $this->get_option_id(),
			'label'        => $this->get_name(),
			'group'        => $this->get_group(),
			'description'  => $this->get_description(),
			'priority'     => $this->get_priority(),
			'parameters'   => $renderer->render_fields( $this, array() ),
		);
	}

}
