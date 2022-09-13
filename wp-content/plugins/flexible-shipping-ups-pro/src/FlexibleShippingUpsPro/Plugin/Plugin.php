<?php
/**
 * Plugin main class.
 *
 * @package WPDesk\FlexibleShippingUpsPro\Plugin
 */

namespace WPDesk\FlexibleShippingUpsPro\Plugin;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;
use UpsProVendor\WPDesk\AbstractShipping\Settings\SettingsValuesAsArray;
use UpsProVendor\WPDesk\Logger\WPDeskLoggerFactory;
use UpsProVendor\WPDesk\Notice\AjaxHandler;
use UpsProVendor\WPDesk\Persistence\Adapter\WooCommerce\WooCommerceSessionContainer;
use UpsProVendor\WPDesk\PluginBuilder\Plugin\AbstractPlugin;
use UpsProVendor\WPDesk\PluginBuilder\Plugin\HookableCollection;
use UpsProVendor\WPDesk\PluginBuilder\Plugin\HookableParent;
use UpsProVendor\WPDesk\PluginBuilder\Plugin\TemplateLoad;
use UpsProVendor\WPDesk\Tracker\Deactivation\TrackerFactory;
use UpsProVendor\WPDesk\WooCommerce\CurrencySwitchers;
use UpsProVendor\WPDesk\WooCommerceShipping\ActivePayments;
use UpsProVendor\WPDesk\WooCommerceShipping\CollectionPoints\CachedCollectionPointsProvider;
use UpsProVendor\WPDesk\WooCommerceShipping\CustomFields\ApiStatus\FieldApiStatusAjax;
use UpsProVendor\WPDesk\WooCommerceShipping\EstimatedDelivery\EstimatedDeliveryDatesDisplay;
use UpsProVendor\WPDesk\UpsProShippingService\UpsProShippingService;
use UpsProVendor\WPDesk\UpsShippingService\UpsApi\UpsAccessPoints;
use UpsProVendor\WPDesk\UpsShippingService\UpsServices;
use UpsProVendor\WPDesk\UpsShippingService\UpsSettingsDefinition;
use UpsProVendor\WPDesk\View\Renderer\Renderer;
use UpsProVendor\WPDesk\View\Renderer\SimplePhpRenderer;
use UpsProVendor\WPDesk\View\Resolver\ChainResolver;
use UpsProVendor\WPDesk\View\Resolver\DirResolver;
use UpsProVendor\WPDesk\View\Resolver\WPThemeResolver;
use UpsProVendor\WPDesk\WooCommerceShipping\Assets;
use UpsProVendor\WPDesk\WooCommerceShipping\CollectionPoints\CheckoutHandler;
use UpsProVendor\WPDesk\WooCommerceShipping\PluginShippingDecisions;
use UpsProVendor\WPDesk\WooCommerceShipping\ShippingMethod\RateMethod\CollectionPoint\CollectionPointRateMethod;
use UpsProVendor\WPDesk\WooCommerceShipping\ShopSettings;
use UpsProVendor\WPDesk\WooCommerceShipping\Ups\MetaDataInterpreters\PackedPackagesAdminMetaDataInterpreter;
use UpsProVendor\WPDesk\WooCommerceShipping\Ups\Tracker;
use UpsProVendor\WPDesk\WooCommerceShipping\Ups\UpsAdminOrderMetaDataDisplay;
use UpsProVendor\WPDesk\WooCommerceShipping\Ups\UpsFrontOrderMetaDataDisplay;
use UpsProVendor\WPDesk\WooCommerceShipping\Ups\UpsShippingMethod;
use UpsProVendor\WPDesk\WooCommerceShippingPro\CustomFields\ShippingBoxes;
use UpsProVendor\WPDesk_Plugin_Info;
use UpsProVendor\WPDesk\UpsShippingService\UpsShippingService;
use WPDesk\FlexibleShippingUpsPro\ShippingMethod\UpsProShippingMethod;
use WPDesk\FlexibleShippingUpsPro\Tracker\ProTracker;

/**
 * Main plugin class. The most important flow decisions are made here.
 *
 * @package WPDesk\FlexibleShippingUps
 */
class Plugin extends AbstractPlugin implements LoggerAwareInterface, HookableCollection {

	use LoggerAwareTrait;
	use HookableParent;
	use TemplateLoad;

	const PRIORITY_BEFORE_SHARED_HELPER = -35;

	/**
	 * Scripts version.
	 *
	 * @var string
	 */
	private $scripts_version = '1';

	/**
	 * Renderer.
	 *
	 * @var Renderer
	 */
	private $renderer;

	/**
	 * Plugin constructor.
	 *
	 * @param WPDesk_Plugin_Info $plugin_info Plugin info.
	 */
	public function __construct( WPDesk_Plugin_Info $plugin_info ) {
		$this->plugin_info = $plugin_info;
		$this->scripts_version = FLEXIBLE_SHIPPING_UPS_PRO_VERSION . '-' . $this->scripts_version;
		parent::__construct( $this->plugin_info );
	}

	/**
	 * Returns true when debug mode is on.
	 *
	 * @return bool
	 */
	private function is_debug_mode() {
		return 'yes' === get_option( 'debug_mode', 'no' );
	}

	/**
	 * Init base variables for plugin
	 */
	public function init_base_variables() {
		$this->plugin_url       = $this->plugin_info->get_plugin_url();
		$this->plugin_path      = $this->plugin_info->get_plugin_dir();
		$this->template_path    = $this->plugin_info->get_text_domain();
		$this->plugin_namespace = $this->plugin_info->get_text_domain();
	}

	/**
	 * Init plugin
	 */
	public function init() {
		parent::init();

		$this->init_renderer();

		$this->add_hookable( new Tracker() );

		$this->add_hookable( new ProTracker() );

		$this->add_hookable( new ActivationDate() );

		$this->add_hookable( new OrderCounter() );

		$this->add_hookable( new RateNotice() );

		$this->add_hookable( new AjaxHandler( trailingslashit( $this->get_plugin()->get_plugin_url() ) . 'vendor_prefixed/wpdesk/wp-notice/assets' ) );

		$this->add_hookable( new SettingsSidebar() );

		$admin_meta_data_interpreter = new UpsAdminOrderMetaDataDisplay();
		$admin_meta_data_interpreter->add_interpreter( new PackedPackagesAdminMetaDataInterpreter() );
		/** Add hidden meta for backward compatibility */
		$admin_meta_data_interpreter->add_hidden_order_item_meta_key( 'ups_delivery_date' );
		$admin_meta_data_interpreter->add_hidden_order_item_meta_key( 'ups_time_in_transit' );
		$admin_meta_data_interpreter->add_hidden_order_item_meta_key( 'ups_days_to_arrival_date' );
		$admin_meta_data_interpreter->init_interpreters();
		$this->add_hookable( $admin_meta_data_interpreter );
		$this->add_hookable( new EstimatedDeliveryDatesDisplay( $this->renderer, UpsShippingService::UNIQUE_ID ) );

		$meta_data_interpreter = new UpsFrontOrderMetaDataDisplay( $this->renderer );
		$meta_data_interpreter->init_interpreters();
		$this->add_hookable( $meta_data_interpreter );

		$this->add_hookable( new CurrencySwitchers\ShippingIntegrations( UpsShippingService::UNIQUE_ID ) );

		$this->add_hookable( new ActivePayments\Integration( UpsShippingService::UNIQUE_ID ) );

		$this->hooks();

		$this->init_ups_services();
	}

	/**
	 * Init UPS services.
	 *
	 * @internal
	 */
	private function init_ups_services() {
		$global_ups_woocommerce_options  = $this->get_global_ups_settings();
		$global_ups_woocommerce_settings = new SettingsValuesAsArray( $global_ups_woocommerce_options );

		$this->setLogger( $this->is_debug_mode() ? ( new WPDeskLoggerFactory() )->createWPDeskLogger() : new NullLogger() );

		$origin_country = $this->get_origin_country_code( $global_ups_woocommerce_options );

		$ups_service = apply_filters(
			'flexible_shipping_ups_pro_shipping_service',
			new UpsProShippingService(
				$this->logger,
				new ShopSettings( UpsProShippingService::UNIQUE_ID ),
				$origin_country
			)
		);

		$api_ajax_status_handler = new FieldApiStatusAjax( $ups_service, $global_ups_woocommerce_settings, $this->logger );
		$api_ajax_status_handler->hooks();

		$plugin_shipping_decisions = new PluginShippingDecisions( $ups_service, $this->logger );
		$plugin_shipping_decisions->set_field_api_status_ajax( $api_ajax_status_handler );

		UpsShippingMethod::set_plugin_shipping_decisions( $plugin_shipping_decisions );

	}

	/**
	 * @internal
	 */
	public function init_ups_access_points() {
		$global_ups_woocommerce_options  = $this->get_global_ups_settings();

		$access_points_provider = new UpsAccessPoints(
			$global_ups_woocommerce_options[ UpsSettingsDefinition::ACCESS_KEY ],
			$global_ups_woocommerce_options[ UpsSettingsDefinition::USER_ID ],
			$global_ups_woocommerce_options[ UpsSettingsDefinition::PASSWORD ],
			$this->logger
		);

		WC()->initialize_session();
		$access_points_provider = new CachedCollectionPointsProvider(
			$access_points_provider,
			new WooCommerceSessionContainer( WC()->session ),
			self::class . $this->scripts_version
		);

		$collection_points_checkout_handler = new CheckoutHandler(
			$access_points_provider,
			UpsShippingService::UNIQUE_ID,
			$this->renderer,
			__( 'UPS Access Point', 'flexible-shipping-ups-pro' ),
			__( 'Access point unavailable for selected shipping address!', 'flexible-shipping-ups-pro' ),
			__( 'The closest point based on the billing address or shipping address.', 'flexible-shipping-ups-pro' ),
			true
		);
		$collection_points_checkout_handler->hooks();

		CollectionPointRateMethod::set_collection_points_checkout_handler( $collection_points_checkout_handler );

		$assets = new Assets( $this->get_plugin_url() . 'vendor_prefixed/wpdesk/wp-woocommerce-shipping/assets', 'ups' );
		$assets->hooks();
	}

	/**
	 * Init renderer.
	 */
	private function init_renderer() {
		$resolver = new ChainResolver();
		$resolver->appendResolver( new WPThemeResolver( $this->get_template_path() ) );
		$resolver->appendResolver( new DirResolver( trailingslashit( $this->plugin_path ) . 'templates' ) );
		$resolver->appendResolver( new DirResolver( trailingslashit( $this->plugin_path ) . 'vendor_prefixed/wpdesk/wp-woocommerce-shipping/templates' ) );
		$resolver->appendResolver( new DirResolver( trailingslashit( $this->plugin_path ) . 'vendor_prefixed/wpdesk/wp-ups-shipping-method/templates' ) );
		$this->renderer = new SimplePhpRenderer( $resolver );
	}

	/**
	 * Init hooks.
	 */
	public function hooks() {
		parent::hooks();
		add_filter( 'woocommerce_shipping_methods', array( $this, 'woocommerce_shipping_methods_filter' ), 20, 1 );
		add_action( 'woocommerce_init', array( $this, 'init_ups_countries' ) );
		add_action( 'woocommerce_init', array( $this, 'init_ups_access_points' ) );
		add_action( 'admin_init', array( $this, 'init_deactivation_tracker' ) );
		add_action( 'plugins_loaded', array( $this, 'plugins_loaded_action' ), self::PRIORITY_BEFORE_SHARED_HELPER );
		$this->hooks_on_hookable_objects();
	}

	/**
	 * Plugins loaded hook.
	 */
	public function plugins_loaded_action() {
		apply_filters(
			'wpdesk_tracker_enabled',
			function() {
				return ( ! empty( $_SERVER['SERVER_ADDR'] ) && '127.0.0.1' === $_SERVER['SERVER_ADDR'] );
			}
		);

		$tracker_factory = new \WPDesk_Tracker_Factory();
		$tracker_factory->create_tracker( basename( dirname( __FILE__ ) ) );
	}

	/**
	 * Init deactivation tracker.
	 */
	public function init_deactivation_tracker() {
		$deactivation_tracker = TrackerFactory::createDefaultTracker(
			'flexible-shipping-ups-pro',
			'flexible-shipping-ups-pro/flexible-shipping-ups-pro.php',
			__( 'Flexible Shipping UPS Pro', 'flexible-shipping-ups-pro' )
		);
		$deactivation_tracker->hooks();
	}


	/**
	 * Init UPS services.
	 */
	public function init_ups_countries() {
		UpsServices::set_eu_countries( WC()->countries->get_european_union_countries() );

	}

	/**
	 * Get global UPS settings.
	 *
	 * @return array
	 */
	private function get_global_ups_settings() {
		return get_option(
			'woocommerce_' . UpsShippingService::UNIQUE_ID . '_settings',
			array(
				UpsSettingsDefinition::ACCESS_KEY    => '',
				UpsSettingsDefinition::PASSWORD      => '',
				UpsSettingsDefinition::USER_ID       => '',
				UpsSettingsDefinition::CUSTOM_ORIGIN => 'no',
			)
		);
	}

	/**
	 * Get origin country code.
	 *
	 * @param array $global_ups_woocommerce_options .
	 *
	 * @return string
	 */
	private function get_origin_country_code( $global_ups_woocommerce_options ) {

		$origin_country_code = '';
		if ( isset( $global_ups_woocommerce_options[ UpsSettingsDefinition::CUSTOM_ORIGIN ] ) && 'yes' === $global_ups_woocommerce_options[ UpsSettingsDefinition::CUSTOM_ORIGIN ] ) {
			$country_state_code  = explode( ':', $global_ups_woocommerce_options[ UpsSettingsDefinition::ORIGIN_COUNTRY ] );
			$origin_country_code = $country_state_code[0];
		} else {
			$woocommerce_default_country = explode( ':', get_option( 'woocommerce_default_country', '' ) );
			if ( ! empty( $woocommerce_default_country[0] ) ) {
				$origin_country_code = $woocommerce_default_country[0];
			}
		}
		return $origin_country_code;
	}

	/**
	 * Adds shipping method to Woocommerce.
	 *
	 * @param array $methods Methods.
	 *
	 * @return array
	 */
	public function woocommerce_shipping_methods_filter( $methods ) {
		$methods['flexible_shipping_ups'] = UpsProShippingMethod::class;
		return $methods;
	}

	/**
	 * Quick links on plugins page.
	 *
	 * @param array $links .
	 *
	 * @return array
	 */
	public function links_filter( $links ) {
		$is_pl        = 'pl_PL' === get_locale();
		$docs_link    = $is_pl ? 'https://www.wpdesk.pl/docs/ups-woocommerce-docs/' : 'https://docs.flexibleshipping.com/category/122-ups/';
		$docs_link   .= '?utm_source=ups-pro&utm_medium=link&utm_campaign=plugin-list-docs';
		$support_link = $is_pl ? 'https://www.wpdesk.pl/support/?utm_source=ups-pro&utm_medium=link&utm_campaign=plugin-list-support/' : 'https://flexibleshipping.com/support/?utm_source=ups-pro&utm_medium=link&utm_campaign=plugin-list-support/';
		$settings_url = admin_url( 'admin.php?page=wc-settings&tab=shipping&section=flexible_shipping_ups' );

		$plugin_links = array(
			'<a href="' . $settings_url . '">' . __( 'Settings', 'flexible-shipping-ups-pro' ) . '</a>',
			'<a href="' . $docs_link . '" target="_blank">' . __( 'Docs', 'flexible-shipping-ups-pro' ) . '</a>',
			'<a href="' . $support_link . '" target="_blank">' . __( 'Support', 'flexible-shipping-ups-pro' ) . '</a>',
		);

		return array_merge( $plugin_links, $links );
	}

	/**
	 * Admin enqueue scripts.
	 */
	public function admin_enqueue_scripts() {
		parent::admin_enqueue_scripts();

		ShippingBoxes::enqueue_scripts( $this->get_plugin_assets_url() );
	}


}
