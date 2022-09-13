<?php
/**
 * Class RulesetsField
 *
 * @package WPDesk\FS\ConditionalMethods\WooCommerceSettings
 */

namespace WPDesk\FS\ConditionalMethods\WooCommerceSettings;

use WPDesk\FS\ConditionalMethods\Settings\SingleRulesetSettings;

/**
 * Can render rulesets field.
 */
class RulesetsField {

	/**
	 * @var array[]
	 */
	private $settings;

	/**
	 * @var SingleRulesetSettings[]
	 */
	private $rulesets_settings;

	/**
	 * @var ConditionalMethodsActionsUrls
	 */
	private $conditional_methods_actions_urls;

	/**
	 * PackagesOrderField constructor.
	 *
	 * @param array[]                       $settings                         .
	 * @param SingleRulesetSettings[]       $conditional_methods_settings     .
	 * @param ConditionalMethodsActionsUrls $conditional_methods_actions_urls .
	 */
	public function __construct( array $settings, array $conditional_methods_settings, ConditionalMethodsActionsUrls $conditional_methods_actions_urls ) {
		$this->settings                         = $settings;
		$this->rulesets_settings                = $conditional_methods_settings;
		$this->conditional_methods_actions_urls = $conditional_methods_actions_urls;
	}

	/**
	 * .
	 *
	 * @return string
	 */
	public function render() {
		$field_id              = isset( $this->settings['id'] ) ? $this->settings['id'] : '';
		$title                 = isset( $this->settings['title'] ) ? $this->settings['title'] : '';
		$tooltip_html          = isset( $this->settings['tooltip_html'] ) ? $this->settings['tooltip_html'] : '';
		$desc                  = isset( $this->settings['desc'] ) ? $this->settings['desc'] : '';
		$type                  = isset( $this->settings['type'] ) ? $this->settings['type'] : '';
		$rulesets_settings     = $this->rulesets_settings;
		$add_ruleset_url       = $this->conditional_methods_actions_urls->prepare_add_ruleset_url();
		$rulesets_actions_urls = $this->conditional_methods_actions_urls;

		ob_start();
		include __DIR__ . '/views/html-rulesets-field.php';
		$output = ob_get_clean();

		return trim( is_string( $output ) ? $output : 'Error rendering rulesets field.' );
	}

}
