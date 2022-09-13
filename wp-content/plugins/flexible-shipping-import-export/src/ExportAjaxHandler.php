<?php
/**
 * Class ExportAjaxHandler.
 *
 * @package WPDesk\FS\TableRate\ImportExport
 */

namespace WPDesk\FS\TableRate\ImportExport;

use Exception;
use FlexibleShippingImportExportVendor\WPDesk\PluginBuilder\Plugin\Hookable;
use WPDesk\FS\TableRate\ImportExport\Tracker\TrackerDataCollector;

/**
 * Class Hooks
 */
class ExportAjaxHandler implements Hookable {
	const AJAX_ACTION = 'flexible_shipping_export_single';

	/**
	 * @return void
	 */
	public function hooks() {
		add_action( 'wp_ajax_' . self::AJAX_ACTION, array( $this, 'handle_ajax_request' ) );
	}

	/**
	 * Preparing data to export.
	 */
	public function handle_ajax_request() {
		check_ajax_referer( self::AJAX_ACTION, 'security' );

		$zone_id      = filter_input( INPUT_GET, 'zone_id', FILTER_VALIDATE_INT );
		$type         = filter_input( INPUT_GET, 'type', FILTER_DEFAULT, array( 'options' => array( 'default' => 'csv' ) ) );
		$instance_ids = array_filter( wp_parse_id_list( filter_input( INPUT_GET, 'instance_ids' ) ) );

		try {
			$exporter = ( new ExportFactory( $type, $zone_id, $instance_ids ) )->get_exporter();

			( new TrackerDataCollector() )->update_export_data( $exporter->get_elements_count() );

			wp_send_json_success(
				array(
					'file_content' => $exporter->get_raw(),
					'mime_type'    => $exporter->get_mime_type(),
					'filename'     => $exporter->get_filename(),
				)
			);
		} catch ( Exception $e ) {
			wp_send_json_error( array( 'message' => $e->getMessage() ) );
		}
	}
}
