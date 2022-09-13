<?php
/**
 * Plugin main class.
 *
 * @package WPDesk\FS\TableRate\ImportExport
 */

namespace WPDesk\FS\TableRate\ImportExport;

use FlexibleShippingImportExportVendor\WPDesk\Logger\WPDeskLoggerFactory;
use FlexibleShippingImportExportVendor\WPDesk\PluginBuilder\Plugin\AbstractPlugin;
use FlexibleShippingImportExportVendor\WPDesk\PluginBuilder\Plugin\HookableCollection;
use FlexibleShippingImportExportVendor\WPDesk\PluginBuilder\Plugin\HookableParent;
use FlexibleShippingImportExportVendor\WPDesk\PluginBuilder\Plugin\TemplateLoad;
use FlexibleShippingImportExportVendor\WPDesk\Tracker\Deactivation\TrackerFactory;
use FlexibleShippingImportExportVendor\WPDesk\View\Renderer\Renderer;
use FlexibleShippingImportExportVendor\WPDesk\View\Renderer\SimplePhpRenderer;
use FlexibleShippingImportExportVendor\WPDesk\View\Resolver\ChainResolver;
use FlexibleShippingImportExportVendor\WPDesk\View\Resolver\DirResolver;
use FlexibleShippingImportExportVendor\WPDesk\View\Resolver\WPThemeResolver;
use FlexibleShippingImportExportVendor\WPDesk_Plugin_Info;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;
use WPDesk\FS\TableRate\ImportExport\Tracker\TrackerDataAppender;

/**
 * Main plugin class. The most important flow decisions are made here.
 */
class Plugin extends AbstractPlugin implements LoggerAwareInterface, HookableCollection {

	/**
	 * @var string
	 */
	private $scripts_version = '3';

	/**
	 * Renderer.
	 *
	 * @var Renderer
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
		$this->setLogger( $this->is_debug_mode() ? ( new WPDeskLoggerFactory() )->createWPDeskLogger( 'fs-import-export' ) : new NullLogger() );

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
		if ( defined( 'FLEXIBLE_SHIPPING_IMPORT_EXPORT_VERSION' ) ) {
			$this->scripts_version = FLEXIBLE_SHIPPING_IMPORT_EXPORT_VERSION . '-' . $this->scripts_version;
		}

		require_once __DIR__ . '/../vendor_prefixed/league/csv/src/functions.php';
		$this->init_renderer();

		// Exporter.
		$this->add_hookable( new ExportAjaxHandler() );
		$this->add_hookable( new ShippingMethodsAjaxHandler() );

		$this->add_hookable( new ImportExportButton() );
		$this->add_hookable( new Assets( $this->scripts_version, $this->get_plugin_assets_url(), $this->plugin_path ) );
		$this->add_hookable( new ImportAjaxHandler() );

		$this->add_hookable( new TrackerDataAppender() );

		parent::init();
	}

	/**
	 * Init hooks.
	 */
	public function hooks() {
		parent::hooks();

		$this->hooks_on_hookable_objects();
	}

	/**
	 * Init deactivation tracker.
	 */
	public function init_deactivation_tracker() {
		$deactivation_tracker = TrackerFactory::createDefaultTracker(
			'flexible-shipping-import-export',
			'flexible-shipping-import-export/flexible-shipping-import-export.php',
			__( 'Flexible Shipping Import/Export for WooCommerce', 'flexible-shipping-import-export' )
		);
		$deactivation_tracker->hooks();
	}

	/**
	 * Quick links on plugins page.
	 *
	 * @param array $links .
	 *
	 * @return array
	 */
	public function links_filter( $links ) {
		$docs_link    = get_locale() === 'pl_PL' ? 'https://www.wpdesk.pl/docs/flexible-shipping-import-export-woocommerce/?utm_source=flexible-shipping-import-export&utm_medium=quick-link&utm_campaign=docs-quick-link' : 'https://docs.flexibleshipping.com/?utm_source=flexible-shipping-import-export&utm_medium=quick-link&utm_campaign=docs-quick-link';
		$support_link = get_locale() === 'pl_PL' ? 'https://www.wpdesk.pl/support/?utm_source=fsie&utm_medium=link&utm_campaign=plugin-list-support' : 'https://flexibleshipping.com/support/?utm_source=fsie&utm_medium=link&utm_campaign=plugin-list-support';

		$external_attributes = ' target="_blank" ';

		$plugin_links = array(
			'<a href="' . $docs_link . '"' . $external_attributes . '>' . __( 'Docs', 'flexible-shipping-import-export' ) . '</a>',
			'<a href="' . $support_link . '"' . $external_attributes . '>' . __( 'Support', 'flexible-shipping-import-export' ) . '</a>',
		);

		return array_merge( $plugin_links, $links );
	}

	/**
	 * Admin enqueue scripts.
	 */
	public function admin_enqueue_scripts() {
		parent::admin_enqueue_scripts();
	}

	/**
	 * Init renderer.
	 */
	private function init_renderer() {
		$resolver = new ChainResolver();
		$resolver->appendResolver( new WPThemeResolver( $this->get_template_path() ) );
		$resolver->appendResolver( new DirResolver( \trailingslashit( $this->plugin_path ) . 'templates' ) );
		$resolver->appendResolver( new DirResolver( \trailingslashit( $this->plugin_path ) . 'vendor_prefixed/wpdesk/wp-woocommerce-shipping/templates' ) );
		$this->renderer = new SimplePhpRenderer( $resolver );
	}

}
