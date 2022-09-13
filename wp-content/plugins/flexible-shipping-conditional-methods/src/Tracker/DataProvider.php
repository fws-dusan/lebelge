<?php
/**
 * Class DataProvider
 *
 * @package WPDesk\FS\ConditionalMethods\Tracker
 */

namespace WPDesk\FS\ConditionalMethods\Tracker;

use WPDesk\FS\ConditionalMethods\Settings\RulesetsSettingsFactory;
use WPDesk\FS\ConditionalMethods\Settings\SingleRulesetSettings;
use WPDesk\FS\ConditionalMethods\Settings\SingleRulesetSettingsFactory;
use WPDesk\FS\ConditionalMethods\WooCommerceSettings\WooCommerceConditionalMethodsSettings;

/**
 * Can provide data for tracker.
 */
class DataProvider implements \WPDesk_Tracker_Data_Provider {

	/**
	 * @return array<string, int|array>
	 */
	public function get_data() {
		$rulesets = $this->get_rulesets();

		return array(
			'flexible_shipping_conditional_methods' => array(
				'rulesets_count'        => count( $rulesets ),
				'conditions_count'      => $this->get_conditions_count( $rulesets ),
				'condition_types_count' => $this->get_condition_types_count( $rulesets ),
				'actions_count'         => $this->get_actions_count( $rulesets ),
				'sources_count'         => $this->get_sources_count( $rulesets ),
				'matches_count'         => $this->get_matches_count( $rulesets ),
			),
		);
	}

	/**
	 * @return SingleRulesetSettings[]
	 *
	 * @codeCoverageIgnore
	 */
	protected function get_rulesets() {
		$rulesets_settings_factory = new RulesetsSettingsFactory( WooCommerceConditionalMethodsSettings::OPTION_NAME );
		$single_ruleset_factory = new SingleRulesetSettingsFactory();
		return $single_ruleset_factory->create_from_rulesets_order( $rulesets_settings_factory->create_settings_from_option()->get_rulesets_order() );
	}

	/**
	 * @param SingleRulesetSettings[] $rulesets .
	 *
	 * @return array<string, int>
	 */
	private function get_conditions_count( array $rulesets ) {
		$conditions_count = array();
		foreach ( $rulesets as $ruleset ) {
			$conditions_count = $this->get_element_count( $conditions_count, $ruleset->get_conditions(), 'option_id' );
		}

		return $conditions_count;
	}

	/**
	 * @param SingleRulesetSettings[] $rulesets .
	 *
	 * @return array<string, int>
	 */
	private function get_condition_types_count( array $rulesets ) {
		$conditions_count = array();
		foreach ( $rulesets as $ruleset ) {
			$conditions_count = $this->get_element_count( $conditions_count, $ruleset->get_conditions(), 'type' );
		}
		foreach ( $conditions_count as $key => $count ) {
			if ( 'option' === $key ) {
				unset( $conditions_count[ $key ] );
				$conditions_count['and'] = $count;
			}
		}

		return $conditions_count;
	}

	/**
	 * @param SingleRulesetSettings[] $rulesets .
	 *
	 * @return array<string, int>
	 */
	private function get_actions_count( array $rulesets ) {
		$actions_count = array();
		foreach ( $rulesets as $ruleset ) {
			$actions_count = $this->get_element_count( $actions_count, $ruleset->get_actions(), 'option_id' );
		}

		return $actions_count;
	}

	/**
	 * @param SingleRulesetSettings[] $rulesets .
	 *
	 * @return array<string, int>
	 */
	private function get_sources_count( array $rulesets ) {
		$sources_count = array();
		foreach ( $rulesets as $ruleset ) {
			$sources_count = $this->get_element_count( $sources_count, $ruleset->get_conditions(), 'source' );
		}

		return $sources_count;
	}

	/**
	 * @param SingleRulesetSettings[] $rulesets .
	 *
	 * @return array<string, int>
	 */
	private function get_matches_count( array $rulesets ) {
		$matches_count = array();
		foreach ( $rulesets as $ruleset ) {
			$matches_count = $this->get_element_count( $matches_count, $ruleset->get_conditions(), 'matches' );
		}

		return $matches_count;
	}

	/**
	 * @param array<string, int>          $counters .
	 * @param array<string, string|array> $options .
	 * @param string                      $field .
	 *
	 * @return array<string, int>
	 */
	private function get_element_count( array $counters, array $options, $field ) {
		foreach ( $options as $option ) {
			if ( is_array( $option ) && isset( $option[ $field ] ) ) {
				$value = (string) $option[ $field ];
				if ( ! isset( $counters[ $value ] ) ) {
					$counters[ $value ] = 1;
				} else {
					$counters[ $value ]++;
				}
			}
		}

		return $counters;
	}

}
