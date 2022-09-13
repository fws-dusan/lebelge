<?php
/**
 * Class SingleRulesetSettingsFactory
 *
 * @package WPDesk\FS\ConditionalMethods\Settings
 */

namespace WPDesk\FS\ConditionalMethods\Settings;

use WPDesk\FS\ConditionalMethods\CustomPostType;
use WPDesk\FS\ConditionalMethods\WooCommerceSettings\WooCommerceConditionalMethodsSettings;

/**
 * Can create single ruleset settings.
 */
class SingleRulesetSettingsFactory {

	const SETTINGS_OPTION_NAME = WooCommerceConditionalMethodsSettings::OPTION_NAME;

	/**
	 * @param int[] $rulesets_order .
	 *
	 * @return SingleRulesetSettings[]
	 */
	public function create_from_rulesets_order( array $rulesets_order ) {
		$rulesets_ids = $this->get_posts_ids_for_rulesets();
		$single_rulesets_settings = array();
		foreach ( $rulesets_order as $ruleset_settings_id ) {
			if ( isset( $rulesets_ids[ (int) $ruleset_settings_id ] ) ) {
				$single_rulesets_settings[ (int) $ruleset_settings_id ] = $this->get_ruleset_for_id( $ruleset_settings_id );
				unset( $rulesets_ids[ (int) $ruleset_settings_id ] );
			}
		}
		foreach ( $rulesets_ids as $ruleset_id => $zero ) {
			$single_rulesets_settings[ (int) $ruleset_id ] = $this->get_ruleset_for_id( $ruleset_id );
		}

		return $single_rulesets_settings;
	}

	/**
	 * @param int $ruleset_id .
	 *
	 * @return SingleRulesetSettings
	 */
	public function get_ruleset_for_id( $ruleset_id ) {
		return new SingleRulesetSettings(
			(int) $ruleset_id,
			get_option( $this->prepare_option_name( (int) $ruleset_id ), array() )
		);
	}

	/**
	 * @param int $ruleset_settings_id .
	 *
	 * @return string
	 */
	public function prepare_option_name( $ruleset_settings_id ) {
		return self::SETTINGS_OPTION_NAME . '_' . $ruleset_settings_id;
	}

	/**
	 * @return int[]
	 *
	 * @codeCoverageIgnore
	 */
	protected function get_posts_ids_for_rulesets() {
		$wp_query = new \WP_Query(
			array(
				'post_type'   => CustomPostType::POST_TYPE,
				'post_status' => 'any',
				'nopaging'    => true,
				'fields'      => 'ids',
			)
		);

		return array_flip( $wp_query->posts ); // @phpstan-ignore-line
	}

}
