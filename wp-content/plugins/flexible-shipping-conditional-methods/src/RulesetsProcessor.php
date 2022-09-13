<?php
/**
 * Class RulesetsProcessor
 *
 * @package WPDesk\FS\ConditionalMethods
 */

namespace WPDesk\FS\ConditionalMethods;

use FSConditionalMethodsVendor\WPDesk\PluginBuilder\Plugin\Hookable;
use WPDesk\FS\ConditionalMethods\Actions\Action;
use WPDesk\FS\ConditionalMethods\Actions\ActionsFactory;
use WPDesk\FS\ConditionalMethods\Conditions\Condition;
use WPDesk\FS\ConditionalMethods\Conditions\ConditionsFactory;
use WPDesk\FS\ConditionalMethods\Settings\RulesetsSettingsFactory;
use WPDesk\FS\ConditionalMethods\Settings\SingleRulesetSettingsFactory;

/**
 * Can process Rulesets.
 */
class RulesetsProcessor implements Hookable {

	/**
	 * @var \WC_Cart|null
	 */
	private $cart;

	/**
	 * @var SingleRulesetSettingsFactory
	 */
	private $single_conditional_methods_settings_factory;

	/**
	 * @var RulesetsSettingsFactory
	 */
	private $rulesets_settings_factory;

	/**
	 * RulesetsProcessor constructor.
	 *
	 * @param \WC_Cart|null                $cart .
	 * @param RulesetsSettingsFactory      $rulesets_settings_factory .
	 * @param SingleRulesetSettingsFactory $single_conditional_methods_settings_factory .
	 */
	public function __construct( $cart, RulesetsSettingsFactory $rulesets_settings_factory, SingleRulesetSettingsFactory $single_conditional_methods_settings_factory ) {
		$this->cart = $cart;
		$this->single_conditional_methods_settings_factory = $single_conditional_methods_settings_factory;
		$this->rulesets_settings_factory = $rulesets_settings_factory;
	}

	/**
	 * .
	 */
	public function hooks() {
		add_filter( 'woocommerce_shipping_packages', array( $this, 'process_packages' ) );
	}

	/**
	 * @param array[] $packages .
	 *
	 * @return array[]
	 */
	public function process_packages( $packages ) {
		$conditional_shipping_settings = $this->rulesets_settings_factory->create_settings_from_option();
		$ruleset_settings = $this->get_ruleset_settings();

		if ( $conditional_shipping_settings->is_enabled() && is_array( $packages ) ) {
			$conditions_matcher = new ConditionsMatcher( $this->get_available_conditions() );
			$actions_executor = new ActionsExecutor( $this->get_available_actions() );
			foreach ( $packages as $package_key => $package ) {
				if ( isset( $package['rates'] ) && is_array( $package['rates'] ) ) {
					$packages[ $package_key ] = $this->process_package( $package, $packages, $ruleset_settings, $conditions_matcher, $actions_executor );
				}
			}
		}

		return $packages;
	}

	/**
	 * @return Condition[]
	 */
	private function get_available_conditions() {
		return ( new ConditionsFactory() )->create_conditions();
	}

	/**
	 * @return Action[]
	 */
	private function get_available_actions() {
		return ( new ActionsFactory() )->create_actions();
	}

	/**
	 * @param array[]                          $package .
	 * @param array[]                          $all_packages .
	 * @param Settings\SingleRulesetSettings[] $ruleset_settings .
	 * @param ConditionsMatcher                $conditions_matcher .
	 * @param ActionsExecutor                  $actions_executor .
	 *
	 * @return array[]
	 */
	private function process_package( array $package, array $all_packages, array $ruleset_settings, ConditionsMatcher $conditions_matcher, ActionsExecutor $actions_executor ) {
		if ( $this->cart ) {
			foreach ( $ruleset_settings as $single_ruleset_settings ) {
				if ( 'yes' === $single_ruleset_settings->get_enabled() && $conditions_matcher->are_conditions_matched( $single_ruleset_settings->get_conditions(), $this->cart, $package, $all_packages ) ) {
					$package = $actions_executor->execute_actions( $single_ruleset_settings->get_actions(), $package );
				}
			}
		}

		return $package;
	}

	/**
	 * @return Settings\SingleRulesetSettings[]
	 */
	private function get_ruleset_settings() {

		return $this->single_conditional_methods_settings_factory->create_from_rulesets_order( $this->rulesets_settings_factory->create_settings_from_option()->get_rulesets_order() );
	}

}
