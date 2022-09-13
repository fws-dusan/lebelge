<?php
/**
 * Class RulesetSettings
 *
 * @package WPDesk\FS\ConditionalMethods\Settings
 */

namespace WPDesk\FS\ConditionalMethods\Settings;

/**
 * Ruleset settings.
 */
class RulesetSettings {

	/**
	 * @var bool
	 */
	private $enabled;

	/**
	 * @var int[]
	 */
	private $rulesets_order;

	/**
	 * RulesetSettings constructor.
	 *
	 * @param bool  $enabled .
	 * @param int[] $rulesets_order .
	 */
	public function __construct( $enabled, array $rulesets_order ) {
		$this->enabled        = $enabled;
		$this->rulesets_order = $rulesets_order;
	}

	/**
	 * @return bool
	 */
	public function is_enabled() {
		return $this->enabled;
	}

	/**
	 * @return int[]
	 */
	public function get_rulesets_order() {
		return $this->rulesets_order;
	}



}
