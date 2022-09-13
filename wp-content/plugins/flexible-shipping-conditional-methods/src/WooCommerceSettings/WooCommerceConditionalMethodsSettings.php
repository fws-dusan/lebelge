<?php
/**
 * Class WooCommerceConditionalMethodsSettings
 *
 * @package WPDesk\FS\ConditionalMethods\WooCommerceSettings
 */

namespace WPDesk\FS\ConditionalMethods\WooCommerceSettings;

use FSConditionalMethodsVendor\WPDesk\PluginBuilder\Plugin\Hookable;
use WPDesk\FS\ConditionalMethods\Actions\ActionsFactory;
use WPDesk\FS\ConditionalMethods\ConditionalForm\WooCommerce\ConditionalFormFieldSettings;
use WPDesk\FS\ConditionalMethods\ConditionalForm\WooCommerce\SettingsField;
use WPDesk\FS\ConditionalMethods\Conditions\ConditionsFactory;
use WPDesk\FS\ConditionalMethods\Settings\RulesetsSettingsFactory;
use WPDesk\FS\ConditionalMethods\Settings\SingleRulesetSettings;
use WPDesk\FS\ConditionalMethods\Settings\SingleRulesetSettingsFactory;

/**
 * Can handle WooCommerce settings for conditional methods.
 */
class WooCommerceConditionalMethodsSettings implements Hookable {

	const SECTION_ID = 'flexible_shipping_conditional_methods';
	const OPTION_NAME = 'woocommerce_flexible_shipping_conditional_methods_ruleset';
	const RULESET_ID = 'ruleset_id';

	/**
	 * @var ConditionalMethodsActionsUrls
	 */
	private $conditional_methods_actions_urls;

	/**
	 * @var SingleRulesetSettingsFactory
	 */
	private $single_conditional_methods_settings_factory;

	/**
	 * @var RulesetsSettingsFactory
	 */
	private $conditional_methods_settings_factory;

	/**
	 * WooCommerceConditionalMethodsSettings constructor.
	 *
	 * @param ConditionalMethodsActionsUrls $conditional_methods_actions_urls            .
	 * @param RulesetsSettingsFactory       $conditional_methods_settings_factory        .
	 * @param SingleRulesetSettingsFactory  $single_conditional_methods_settings_factory .
	 */
	public function __construct(
		ConditionalMethodsActionsUrls $conditional_methods_actions_urls,
		RulesetsSettingsFactory $conditional_methods_settings_factory,
		SingleRulesetSettingsFactory $single_conditional_methods_settings_factory
	) {
		$this->conditional_methods_actions_urls            = $conditional_methods_actions_urls;
		$this->single_conditional_methods_settings_factory = $single_conditional_methods_settings_factory;
		$this->conditional_methods_settings_factory        = $conditional_methods_settings_factory;
	}

	/**
	 * .
	 */
	public function hooks() {
		add_filter( 'woocommerce_get_sections_shipping', array( $this, 'add_section_to_array' ) );
		add_filter( 'woocommerce_get_settings_shipping', array( $this, 'get_section_settings_fields' ), 10, 2 );
		add_action( 'woocommerce_admin_field_flexible_shipping_conditional_methods_rulesets', array( $this, 'output_rulesets_field' ) );
		add_action( 'woocommerce_admin_field_flexible_shipping_conditional_methods_conditional_form', array( $this, 'output_conditional_form' ) );
	}

	/**
	 * @param array<string, string> $sections .
	 *
	 * @return array<string, string>
	 */
	public function add_section_to_array( $sections ) {
		$sections[ self::SECTION_ID ] = __( 'Conditional Shipping Methods', 'flexible-shipping-conditional-methods' );

		return $sections;
	}

	/**
	 * @param array[] $settings        .
	 * @param string  $current_section .
	 *
	 * @return array[]
	 */
	public function get_section_settings_fields( array $settings, $current_section ) {
		if ( self::SECTION_ID === $current_section ) {
			if ( ! $this->is_single_ruleset() ) {
				$settings = $this->get_rulesets_settings_fields();
			} else {
				$settings = $this->get_single_ruleset_settings_fields();
			}
		}

		return $settings;
	}

	/**
	 * @return bool
	 */
	private function is_single_ruleset() {
		return isset( $_GET[ self::RULESET_ID ] );
	}

	/**
	 * .
	 *
	 * @return array[]
	 */
	private function get_rulesets_settings_fields() {
		return array(
			array(
				'title' => __( 'General settings', 'flexible-shipping-conditional-methods' ),
				'type'  => 'title',
				'id'    => self::SECTION_ID,
			),
			array(
				'type'  => 'checkbox',
				'id'    => $this->prepare_settings_field_id( 'enabled' ),
				'title' => __( 'Enable/Disable', 'flexible-shipping-conditional-methods' ),
				'desc'  => __( 'Turn on/off conditional displaying or hiding the shipping methods.', 'flexible-shipping-conditional-methods' ),
			),
			array(
				'id'           => $this->prepare_settings_field_id( 'rulesets_order' ),
				'type'         => 'flexible_shipping_conditional_methods_rulesets',
				'title'        => __( 'Rulesets', 'flexible-shipping-conditional-methods' ),
				'desc'         => sprintf(
				// Translators: open strong tag, close strong tag.
					__( 'Please mind that all the Rulesets you define, including their %1$sConditions%2$s and %1$sActions%2$s are triggered %1$safter the shipping cost calculation in the cart%2$s.', 'flexible-shipping-conditional-methods' ),
					'<strong>',
					'</strong>'
				),
				'tooltip_html' => __( 'Define the rules when the specific shipping methods will be displayed and when hidden once the condition is met.', 'flexible-shipping-conditional-methods' ),
			),
			array(
				'type' => 'sectionend',
				'id'   => self::SECTION_ID,
			),
		);
	}

	/**
	 * .
	 *
	 * @return array[]
	 */
	private function get_single_ruleset_settings_fields() {
		$ruleset = $this->get_ruleset( (int) sanitize_key( isset( $_GET[ self::RULESET_ID ] ) ? $_GET[ self::RULESET_ID ] : '' ) );

		return array(
			array(
				'title' => sprintf(
				// Translators: ruleset name.
					__( 'Conditional Shipping Methods > %1$s', 'flexible-shipping-conditional-methods' ),
					$ruleset->get_name()
				),
				'type'  => 'title',
				'id'    => self::SECTION_ID,
			),
			array(
				'type'    => 'checkbox',
				'id'      => $this->prepare_settings_field_id( 'enabled', (string) $ruleset->get_id() ),
				'title'   => __( 'Enabled', 'flexible-shipping-conditional-methods' ),
				'default' => 'yes',
				'desc'    => __( 'Activate this set of rules.', 'flexible-shipping-conditional-methods' ),
			),
			array(
				'type'              => 'text',
				'id'                => $this->prepare_settings_field_id( 'name', (string) $ruleset->get_id() ),
				'title'             => __( 'Ruleset name', 'flexible-shipping-conditional-methods' ),
				'default'           => __( 'New ruleset', 'flexible-shipping-conditional-methods' ),
				'custom_attributes' => array(
					'required'       => 'required',
					'data-admin_url' => admin_url( 'admin.php?page=wc-settings&tab=shipping&section=flexible_shipping_conditional_methods' ),
					'data-link_text' => __( 'Conditional Shipping Methods', 'flexible-shipping-conditional-methods' ),
				),
				'desc_tip'          => __( 'Enter the unique Ruleset name for easy identification.', 'flexible-shipping-conditional-methods' ),
			),
			array(
				'id'                        => $this->prepare_settings_field_id( 'conditions', (string) $ruleset->get_id() ),
				'type'                      => SettingsField::FIELD_TYPE,
				'title'                     => __( 'Conditions', 'flexible-shipping-conditional-methods' ),
				'desc'                      => sprintf(
				// Translators: open strong tag, close strong tag, open link tag, close ling tag.
					__( 'Determine the %1$sConditions%2$s to trigger the further %1$sActions%2$s defined in the table below. Learn more about the %3$sConditions →%4$s', 'flexible-shipping-conditional-methods' ),
					'<strong>',
					'</strong>',
					sprintf( '<a href="%s" target="_blank">', $this->get_ruleset_condition_docs_url() ),
					'</a>'
				),
				'class'                     => 'conditions',
				'settings_variable'         => 'flexible_shipping_conditional_methods_conditional_form_conditions',
				'available_options'         => ( new ConditionsFactory() )->create_conditions(),
				'conditional_form_settings' => new ConditionalFormFieldSettings(
					__( 'Conditions', 'flexible-shipping-conditional-methods' ),
					__( 'Add condition', 'flexible-shipping-conditional-methods' ),
					__( 'Duplicate selected conditions', 'flexible-shipping-conditional-methods' ),
					__( 'Delete selected conditions', 'flexible-shipping-conditional-methods' ),
					__( 'Add first condition', 'flexible-shipping-conditional-methods' ),
					true,
					__( 'Add OR condition', 'flexible-shipping-conditional-methods' ),
					__( 'OR', 'flexible-shipping-conditional-methods' ),
					__( 'When', 'flexible-shipping-conditional-methods' ),
					__( 'and', 'flexible-shipping-conditional-methods' )
				),
			),
			array(
				'id'                        => $this->prepare_settings_field_id( 'actions', (string) $ruleset->get_id() ),
				'type'                      => SettingsField::FIELD_TYPE,
				'title'                     => __( 'Actions', 'flexible-shipping-conditional-methods' ),
				'desc'                      => sprintf(
				// Translators: open strong tag, close strong tag, open link tag, close ling tag.
					__( 'Define the %1$sActions%2$s regarding the shipping methods to be run once the %1$sConditions%2$s from the table above have been met. Learn more about the %3$sActions →%4$s', 'flexible-shipping-conditional-methods' ),
					'<strong>',
					'</strong>',
					sprintf( '<a href="%s" target="_blank">', $this->get_ruleset_action_docs_url() ),
					'</a>'
				),
				'class'                     => 'actions no-option-label',
				'settings_variable'         => 'flexible_shipping_conditional_methods_conditional_form_actions',
				'available_options'         => ( new ActionsFactory() )->create_actions(),
				'conditional_form_settings' => new ConditionalFormFieldSettings(
					__( 'Actions', 'flexible-shipping-conditional-methods' ),
					__( 'Add action', 'flexible-shipping-conditional-methods' ),
					__( 'Duplicate selected actions', 'flexible-shipping-conditional-methods' ),
					__( 'Delete selected actions', 'flexible-shipping-conditional-methods' ),
					__( 'Add first action', 'flexible-shipping-conditional-methods' ),
					false,
					'',
					'',
					'',
					''
				),
			),
			array(
				'type' => 'sectionend',
				'id'   => self::SECTION_ID,
			),
		);
	}

	/**
	 * @param int $ruleset_id .
	 *
	 * @return SingleRulesetSettings
	 */
	private function get_ruleset( $ruleset_id ) {
		return ( new SingleRulesetSettingsFactory() )->get_ruleset_for_id( $ruleset_id );
	}

	/**
	 * .
	 *
	 * @param string $field_name  .
	 * @param string $name_suffix .
	 *
	 * @return string
	 */
	private function prepare_settings_field_id( $field_name, $name_suffix = '' ) {
		return sprintf( '%1$s%2$s[%3$s]', self::OPTION_NAME, empty( $name_suffix ) ? '' : '_' . $name_suffix, $field_name );
	}

	/**
	 * @param array[] $settings .
	 *
	 * @return void
	 */
	public function output_rulesets_field( $settings ) {
		echo ( new RulesetsField( $settings, $this->get_rulesets_settings(), $this->conditional_methods_actions_urls ) )->render(); // phpcs:ignore.
	}

	/**
	 * @param array<string, string|array> $settings .
	 *
	 * @return void
	 */
	public function output_conditional_form( $settings ) {
		$value                     = isset( $settings['value'] ) ? $settings['value'] : $settings['default'];
		$value                     = is_array( $value ) ? $value : array();
		$available_options         = isset( $settings['available_options'] ) ? $settings['available_options'] : array();
		$conditional_form_settings = $settings['conditional_form_settings'];
		$settings_field            = new SettingsField(
			$settings['id'], // @phpstan-ignore-line
			$settings['id'], // @phpstan-ignore-line
			$settings['settings_variable'], // @phpstan-ignore-line
			$settings['title'], // @phpstan-ignore-line
			$settings['class'], // @phpstan-ignore-line
			$value,
			$available_options, // @phpstan-ignore-line
			// @phpstan-ignore-next-line
			$conditional_form_settings,
			// @phpstan-ignore-next-line
			$settings['desc']
		);
		echo ( $settings_field )->render(); // phpcs:ignore.
	}

	/**
	 * @return SingleRulesetSettings[]
	 */
	private function get_rulesets_settings() {
		return $this->single_conditional_methods_settings_factory->create_from_rulesets_order(
			$this->conditional_methods_settings_factory->create_settings_from_option()->get_rulesets_order()
		);
	}

	/**
	 * @return string
	 */
	private function get_ruleset_condition_docs_url() {
		if ( get_user_locale() === 'pl_PL' ) {
			return 'https://www.wpdesk.pl/docs/conditional-shipping-methods-woocommerce/?utm_source=cm-conditions&utm_medium=link&utm_campaign=cm-settings#warunki';
		}

		return 'https://docs.flexibleshipping.com/article/1005-conditional-shipping-methods-configuration?utm_source=cm-conditions&utm_medium=link&utm_campaign=cm-settings#Conditions';
	}

	/**
	 * @return string
	 */
	private function get_ruleset_action_docs_url() {
		if ( get_user_locale() === 'pl_PL' ) {
			return 'https://www.wpdesk.pl/docs/conditional-shipping-methods-woocommerce/?utm_source=cm-actions&utm_medium=link&utm_campaign=cm-settings#akcje';
		}

		return 'https://docs.flexibleshipping.com/article/1005-conditional-shipping-methods-configuration?utm_source=cm-actions&utm_medium=link&utm_campaign=cm-settings#Actions';
	}
}
