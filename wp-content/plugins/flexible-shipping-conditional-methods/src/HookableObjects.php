<?php
/**
 * Class HookableObjects
 *
 * @package WPDesk\FS\ConditionalMethods
 */

namespace WPDesk\FS\ConditionalMethods;

use FSConditionalMethodsVendor\WPDesk\PluginBuilder\Plugin\HookableCollection;
use FSConditionalMethodsVendor\WPDesk\PluginBuilder\Plugin\HookableParent;
use WPDesk\FS\ConditionalMethods\ConditionalForm\WooCommerce\ConditionalFormFieldAssets;
use WPDesk\FS\ConditionalMethods\ConditionalForm\WooCommerce\SettingsFieldSanitiser;
use WPDesk\FS\ConditionalMethods\Conditions\Product;
use WPDesk\FS\ConditionalMethods\Conditions\ProductTag;
use WPDesk\FS\ConditionalMethods\Conditions\ProductCategory;
use WPDesk\FS\ConditionalMethods\Conditions\ShippingClass;
use WPDesk\FS\ConditionalMethods\Settings\RulesetsSettingsFactory;
use WPDesk\FS\ConditionalMethods\Settings\SingleRulesetSettingsFactory;
use WPDesk\FS\ConditionalMethods\WooCommerceSettings\AddRulesetHandler;
use WPDesk\FS\ConditionalMethods\WooCommerceSettings\ConditionalMethodsActionsUrls;
use WPDesk\FS\ConditionalMethods\WooCommerceSettings\DeleteRulesetHandler;
use WPDesk\FS\ConditionalMethods\WooCommerceSettings\RulesetFieldAssets;
use WPDesk\FS\ConditionalMethods\WooCommerceSettings\WooCommerceConditionalMethodsSettings;

/**
 * Can add hookable objects.
 */
class HookableObjects implements HookableCollection {

	use HookableParent;

	/**
	 * @var string
	 */
	private $assets_url;

	/**
	 * @var string
	 */
	private $scripts_version;

	/**
	 * @var \WC_Cart|null
	 */
	private $cart;

	/**
	 * HookableObjects constructor.
	 *
	 * @param string        $assets_url .
	 * @param string        $scripts_version .
	 * @param \WC_Cart|null $cart .
	 */
	public function __construct( $assets_url, $scripts_version, $cart ) {
		$this->assets_url      = $assets_url;
		$this->scripts_version = $scripts_version;
		$this->cart            = $cart;
	}

	/**
	 * Hooks.
	 */
	public function hooks() {
		$conditional_methods_actions_urls = new ConditionalMethodsActionsUrls();
		$rulesets_settings_factory = new RulesetsSettingsFactory( WooCommerceConditionalMethodsSettings::OPTION_NAME );
		$single_rulesets_settings_factory = new SingleRulesetSettingsFactory();

		$this->add_hookable( new CustomPostType() );
		$this->add_hookable( new WooCommerceConditionalMethodsSettings( $conditional_methods_actions_urls, $rulesets_settings_factory, $single_rulesets_settings_factory ) );
		$this->add_hookable( new SettingsFieldSanitiser() );
		$this->add_hookable( new AddRulesetHandler( $conditional_methods_actions_urls ) );
		$this->add_hookable( new DeleteRulesetHandler( $conditional_methods_actions_urls ) );
		$this->add_hookable( new RulesetFieldAssets( $this->assets_url, $this->scripts_version ) );
		$this->add_hookable( new ConditionalFormFieldAssets( $this->assets_url, $this->scripts_version ) );

		$this->add_hookable( new Product\AjaxHandler() );
		$this->add_hookable( new ProductTag\AjaxHandler() );
		$this->add_hookable( new ShippingClass\AjaxHandler() );
		$this->add_hookable( new ProductCategory\AjaxHandler() );

		$this->add_hookable( new RulesetsProcessor( $this->cart, $rulesets_settings_factory, $single_rulesets_settings_factory ) );

		$this->hooks_on_hookable_objects();
	}

}
