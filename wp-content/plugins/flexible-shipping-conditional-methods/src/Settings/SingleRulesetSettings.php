<?php
/**
 * Class SingleRulesetSettings
 *
 * @package WPDesk\FS\ConditionalMethods\Settings
 */

namespace WPDesk\FS\ConditionalMethods\Settings;

/**
 * Single Ruleset settings.
 */
class SingleRulesetSettings {

	const NAME = 'name';
	const ENABLED = 'enabled';
	const CONDITIONS = 'conditions';
	const ACTIONS = 'actions';

	/**
	 * @var int
	 */
	private $id;

	/**
	 * @var string
	 */
	private $name;

	/**
	 * @var string
	 */
	private $enabled;

	/**
	 * @var array<string, array>
	 */
	private $conditions;

	/**
	 * @var array[]
	 */
	private $actions;

	/**
	 * @var array<string, string|array>
	 */
	private $raw_settings;

	/**
	 * SingleRulesetSettings constructor.
	 *
	 * @param int                         $id .
	 * @param array<string, string|array> $raw_settings .
	 */
	public function __construct( $id, array $raw_settings ) {
		$this->id           = $id;
		$this->raw_settings = $raw_settings;
		$this->name         = isset( $raw_settings[ self::NAME ] ) && is_string( $raw_settings[ self::NAME ] ) ? $raw_settings[ self::NAME ] : __( 'New Ruleset', 'flexible-shipping-conditional-methods' );
		$this->enabled      = isset( $raw_settings[ self::ENABLED ] ) && is_string( $raw_settings[ self::ENABLED ] ) ? $raw_settings[ self::ENABLED ] : 'yes';
		$this->conditions   = isset( $raw_settings[ self::CONDITIONS ] ) && is_array( $raw_settings[ self::CONDITIONS ] ) ? $raw_settings[ self::CONDITIONS ] : array();
		$this->actions      = isset( $raw_settings[ self::ACTIONS ] ) && is_array( $raw_settings[ self::ACTIONS ] ) ? $raw_settings[ self::ACTIONS ] : array();
	}

	/**
	 * @return int
	 */
	public function get_id() {
		return $this->id;
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
	public function get_enabled() {
		return $this->enabled;
	}

	/**
	 * @return array<string, array>
	 */
	public function get_conditions() {
		return $this->conditions;
	}

	/**
	 * @return array[]
	 */
	public function get_actions() {
		return $this->actions;
	}

	/**
	 * @return array<string, string|array>
	 */
	public function get_raw_settings() {
		return $this->raw_settings;
	}

}
