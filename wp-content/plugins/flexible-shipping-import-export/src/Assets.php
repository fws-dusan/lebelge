<?php
/**
 * Class Assets
 *
 * @package WPDesk\FS\TableRate\ImportExport
 */

namespace WPDesk\FS\TableRate\ImportExport;

use FlexibleShippingImportExportVendor\WPDesk\PluginBuilder\Plugin\Hookable;

/**
 * Import/Export assets.
 */
class Assets implements Hookable {

	/**
	 * @var string
	 */
	private $scripts_version;

	/**
	 * @var string
	 */
	private $plugin_assets_url;

	/**
	 * @var string
	 */
	private $plugin_path;

	/**
	 * Assets constructor.
	 *
	 * @param string $scripts_version   .
	 * @param string $plugin_assets_url .
	 * @param string $plugin_path       .
	 */
	public function __construct( $scripts_version, $plugin_assets_url, $plugin_path ) {
		$this->scripts_version   = $scripts_version;
		$this->plugin_assets_url = $plugin_assets_url;
		$this->plugin_path       = $plugin_path;
	}

	/**
	 * .
	 */
	public function hooks() {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_import_export_assets' ) );
	}

	/**
	 * .
	 *
	 * @internal
	 */
	public function enqueue_import_export_assets() {
		if ( isset( $_GET['page'], $_GET['tab'], $_GET['zone_id'] ) && 'wc-settings' === $_GET['page'] && 'shipping' === $_GET['tab'] ) {
			wp_enqueue_script(
				'fs_import_export',
				trailingslashit( $this->plugin_assets_url ) . 'js/import-export.js',
				array( 'wp-i18n' ),
				$this->scripts_version
			);

			wp_localize_script(
				'fs_import_export',
				'fs_import_export',
				array(
					'ajax_url'                      => admin_url( 'admin-ajax.php' ),
					'ajax_url_import'               => admin_url( 'admin-ajax.php?action=' . ImportAjaxHandler::AJAX_ACTION ),
					'ajax_url_export'               => admin_url( 'admin-ajax.php?action=' . ExportAjaxHandler::AJAX_ACTION ),
					'ajax_url_get_shipping_methods' => admin_url( 'admin-ajax.php?action=' . ShippingMethodsAjaxHandler::AJAX_ACTION ),
					'flexible_shipping_assets'      => trailingslashit( $this->plugin_assets_url ),
					'import_nonce'                  => wp_create_nonce( ImportAjaxHandler::AJAX_ACTION ),
					'export_nonce'                  => wp_create_nonce( ExportAjaxHandler::AJAX_ACTION ),
					'get_shipping_methods_nonce'    => wp_create_nonce( ShippingMethodsAjaxHandler::AJAX_ACTION ),
					'import_accepted_mime_type'     => '.csv',
					'export_popup'                  => array(
						'edit_shipping_method_url' => $this->get_shipping_zones_page(),
					),
				)
			);

			wp_set_script_translations( 'fs_import_export', 'flexible-shipping-import-export', $this->plugin_path . '/lang/' );

			wp_enqueue_style(
				'fs_import_export',
				trailingslashit( $this->plugin_assets_url ) . 'css/import-export.css',
				array(),
				$this->scripts_version
			);
		}
	}

	/**
	 * @return string
	 */
	private function get_shipping_zones_page() {
		return add_query_arg(
			array(
				'page' => 'wc-settings',
				'tab'  => 'shipping',
			),
			admin_url( 'admin.php' )
		);
	}

}
