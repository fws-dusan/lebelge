<?php
/**
 * Class PreconfiguredScenarios Pro
 *
 * @package WPDesk\FSPro\TableRate\Rule\PreconfiguredScenarios
 */

namespace WPDesk\FSPro\TableRate\Rule\PreconfiguredScenarios;

use FSProVendor\WPDesk\PluginBuilder\Plugin\Hookable;
use WPDesk\FS\TableRate\Rule\PreconfiguredScenarios\PredefinedScenario;

/**
 * Can provide preconfigured scenarios.
 */
class PreconfiguredScenariosPro implements Hookable {

	/**
	 * .
	 */
	public function hooks() {
		add_filter( 'flexible-shipping/method-rules/predefined-scenarios', array( $this, 'append_predefined_scenarios' ) );
	}

	/**
	 * @param PredefinedScenario[] $scenarios .
	 *
	 * @return PredefinedScenario[]
	 */
	public function append_predefined_scenarios( array $scenarios ) {
		$scenarios = $this->add_value_scenarios( $scenarios );

		return $scenarios;
	}

	/**
	 * @param PredefinedScenario[] $scenarios .
	 *
	 * @return PredefinedScenario[]
	 */
	private function add_value_scenarios( array $scenarios ) {
		$pl = get_locale() === 'pl_PL';
		$url = $pl ? 'https://www.wpdesk.pl/docs/procentowy-koszt-wysylki-woocommerce/' : 'https://docs.flexibleshipping.com/article/39-flexible-shipping-cost-as-a-percentage-of-orders-value';
		$scenarios['cart_percentage'] = new PredefinedScenario(
			__( 'Price', 'flexible-shipping-pro' ),
			__( 'Shipping cost as a percentage of order’s value', 'flexible-shipping-pro' ),
			__( 'Shipping cost always equals 15% of cart total price.', 'flexible-shipping-pro' ),
			$url,
			'[{"conditions":[{"condition_id":"value","min":"","max":""}],"cost_per_order":"","additional_costs":[{"additional_cost":"0.0015","per_value":"0.01","based_on":"value"}],"special_action":"none"}]'
		);

		return $scenarios;
	}

}
