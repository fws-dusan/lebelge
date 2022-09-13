<?php
/**
 * Class ImportExportButton
 *
 * @package WPDesk\FS\TableRate\ImportExport
 */

namespace WPDesk\FS\TableRate\ImportExport;

use FlexibleShippingImportExportVendor\WPDesk\PluginBuilder\Plugin\Hookable;

/**
 * Can add import/export button in shipping zones.
 */
class ImportExportButton implements Hookable {

	/**
	 * .
	 */
	public function hooks() {
		add_action( 'woocommerce_shipping_zone_after_methods_table', array( $this, 'append_import_export_button' ) );
	}

	/**
	 * .
	 *
	 * @param \WC_Shipping_Zone $zone .
	 *
	 * @internal
	 */
	public function append_import_export_button( $zone ) {
		include __DIR__ . '/views/import-export-button-in-shipping-zone.php';
	}

}
