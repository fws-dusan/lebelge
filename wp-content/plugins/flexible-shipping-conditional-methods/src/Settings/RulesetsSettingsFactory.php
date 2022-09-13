<?php
/**
 * Class RulesetsSettingsFactory
 *
 * @package WPDesk\FS\ConditionalMethods\Settings
 */

namespace WPDesk\FS\ConditionalMethods\Settings;

/**
 * Can create rulesets settings.
 */
class RulesetsSettingsFactory {

	const ENABLED = 'enabled';
	const RULESETS_ORDER = 'rulesets_order';

	/**
	 * @var string
	 */
	private $option_name;

	/**
	 * RulesetsSettingsFactory constructor.
	 *
	 * @param string $option_name .
	 */
	public function __construct( $option_name ) {
		$this->option_name = $option_name;
	}

	/**
	 * @return RulesetSettings
	 */
	public function create_settings_from_option() {
		$raw_settings = get_option( $this->option_name, array() );
		$raw_settings = is_array( $raw_settings ) ? $raw_settings : array();

		if ( ! isset( $raw_settings[ self::RULESETS_ORDER ] ) || ! is_array( $raw_settings[ self::RULESETS_ORDER ] ) ) {
			$raw_settings[ self::RULESETS_ORDER ] = array();
		}

		return new RulesetSettings(
			( isset( $raw_settings[ self::ENABLED ] ) ? $raw_settings[ self::ENABLED ] : 'no' ) === 'yes',
			$raw_settings[ self::RULESETS_ORDER ]
		);
	}

}
