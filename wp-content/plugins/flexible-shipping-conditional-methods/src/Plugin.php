<?php
/**
 * Plugin main class.
 *
 * @package WPDesk\FS\ConditionalMethods
 */

namespace WPDesk\FS\ConditionalMethods;

use FSConditionalMethodsVendor\WPDesk\Logger\WPDeskLoggerFactory;
use FSConditionalMethodsVendor\WPDesk\PluginBuilder\Plugin\AbstractPlugin;
use FSConditionalMethodsVendor\WPDesk\PluginBuilder\Plugin\HookableCollection;
use FSConditionalMethodsVendor\WPDesk\PluginBuilder\Plugin\HookableParent;
use FSConditionalMethodsVendor\WPDesk\PluginBuilder\Plugin\TemplateLoad;
use FSConditionalMethodsVendor\WPDesk\Tracker\Deactivation\TrackerFactory;
use FSConditionalMethodsVendor\WPDesk\View\Renderer\Renderer;
use FSConditionalMethodsVendor\WPDesk\View\Renderer\SimplePhpRenderer;
use FSConditionalMethodsVendor\WPDesk\View\Resolver\ChainResolver;
use FSConditionalMethodsVendor\WPDesk\View\Resolver\DirResolver;
use FSConditionalMethodsVendor\WPDesk\View\Resolver\WPThemeResolver;
use FSConditionalMethodsVendor\WPDesk_Plugin_Info;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;
use WPDesk\FS\ConditionalMethods\Tracker\TrackerNotices;
use WPDesk\FS\ConditionalMethods\Tracker\UsageDataTracker;

/**
 * Main plugin class. The most important flow decisions are made here.
 *
 * @codeCoverageIgnore
 */
class Plugin extends AbstractPlugin implements LoggerAwareInterface, HookableCollection {

	/**
	 * @var string
	 */
	private $scripts_version = '7';

	/**
	 * Renderer.
	 *
	 * @var Renderer
	 * @phpstan-ignore-next-line
	 */
	private $renderer;

	use LoggerAwareTrait;
	use HookableParent;
	use TemplateLoad;

	/**
	 * Plugin constructor.
	 *
	 * @param WPDesk_Plugin_Info $plugin_info Plugin info.
	 */
	public function __construct( WPDesk_Plugin_Info $plugin_info ) {
		parent::__construct( $plugin_info );
		$this->setLogger( $this->is_debug_mode() ? ( new WPDeskLoggerFactory() )->createWPDeskLogger( 'fs-vendors' ) : new NullLogger() );

		$this->plugin_url       = $this->plugin_info->get_plugin_url();
		$this->plugin_namespace = $this->plugin_info->get_text_domain();
	}

	/**
	 * Returns true when debug mode is on.
	 *
	 * @return bool
	 */
	private function is_debug_mode() {
		// TODO: from FS config?
		return false;
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
		if ( defined( 'FLEXIBLE_SHIPPING_VENDORS_VERSION' ) ) {
			$this->scripts_version = FLEXIBLE_SHIPPING_VENDORS_VERSION . '.' . $this->scripts_version;
		}

		$this->init_renderer();

		parent::init();
	}

	/**
	 * Init hooks.
	 */
	public function hooks() {
		parent::hooks();

		add_action( 'woocommerce_init', array( $this, 'init_hookable_objects' ) );
		add_action( 'plugins_loaded', array( $this, 'create_tracker' ) );

		$this->hooks_on_hookable_objects();
	}

	/**
	 * .
	 *
	 * @return void
	 */
	public function create_tracker() {
		( new TrackerNotices() )->hooks();
		( new UsageDataTracker( $this->get_plugin_file_path() ) )->hooks();
	}

	/**
	 * @return void
	 */
	public function init_hookable_objects() {
		( new HookableObjects( $this->get_plugin_assets_url(), $this->scripts_version, WC()->cart ) )->hooks();
	}


	/**
	 * Init deactivation tracker.
	 *
	 * @return void
	 */
	public function init_deactivation_tracker() {
		$deactivation_tracker = TrackerFactory::createDefaultTracker(
			'flexible-shipping-conditional-methods',
			'flexible-shipping-conditional-methods/flexible-shipping-conditional-methods.php',
			__( 'Flexible Shipping Conditional Methods', 'flexible-shipping-conditional-methods' )
		);
		$deactivation_tracker->hooks();
	}

	/**
	 * Quick links on plugins page.
	 *
	 * @param string[] $links .
	 *
	 * @return string[]
	 */
	public function links_filter( $links ) {
		$docs_link    = get_locale() === 'pl_PL'
			? 'https://www.wpdesk.pl/docs/conditional-shipping-methods-woocommerce/?utm_source=cm&utm_medium=link&utm_campaign=plugin-list-docs'
			: 'https://docs.flexibleshipping.com/article/1005-conditional-shipping-methods-configuration/?utm_source=cm&utm_medium=link&utm_campaign=plugin-list-docs';
		$support_link = get_locale() === 'pl_PL'
			? 'https://www.wpdesk.pl/support/?utm_source=cm&utm_medium=link&utm_campaign=plugin-list-support'
			: 'https://flexibleshipping.com/support/?utm_source=cm&utm_medium=link&utm_campaign=plugin-list-support';

		$external_attributes = ' target="_blank" ';

		$settings_link = admin_url( 'admin.php?page=wc-settings&tab=shipping&section=flexible_shipping_conditional_methods' );

		$plugin_links = array(
			'<a href="' . $settings_link . '">' . __( 'Settings', 'flexible-shipping-conditional-methods' ) . '</a>',
			'<a href="' . $docs_link . '"' . $external_attributes . '>' . __( 'Docs', 'flexible-shipping-conditional-methods' ) . '</a>',
			'<a href="' . $support_link . '"' . $external_attributes . '>' . __( 'Support', 'flexible-shipping-conditional-methods' ) . '</a>',
		);

		return array_merge( $plugin_links, $links );
	}

	/**
	 * Admin enqueue scripts.
	 *
	 * @return void
	 */
	public function admin_enqueue_scripts() {
		parent::admin_enqueue_scripts();
	}

	/**
	 * Init renderer.
	 *
	 * @return void
	 */
	private function init_renderer() {
		$resolver = new ChainResolver();
		$resolver->appendResolver( new WPThemeResolver( $this->get_template_path() ) );
		$resolver->appendResolver( new DirResolver( \trailingslashit( $this->plugin_path ) . 'templates' ) );
		$resolver->appendResolver( new DirResolver( \trailingslashit( $this->plugin_path ) . 'vendor_prefixed/wpdesk/wp-woocommerce-shipping/templates' ) );
		$this->renderer = new SimplePhpRenderer( $resolver );
	}

}
