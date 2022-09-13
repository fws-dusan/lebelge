<?php
/**
 * Class Tracker
 *
 * @package WPDesk\FS\TableRate\ImportExport
 */

namespace WPDesk\FS\TableRate\ImportExport\Tracker;

/**
 * Can append data to tracker.
 */
class TrackerDataAppender implements \FlexibleShippingImportExportVendor\WPDesk\PluginBuilder\Plugin\Hookable {

	/**
	 * @inheritDoc
	 */
	public function hooks() {
		add_filter( 'wpdesk_tracker_data', array( $this, 'append_tracker_data' ), 11 );
	}

	/**
	 * @param array $data .
	 *
	 * @return array
	 */
	public function append_tracker_data( $data ) {
		$data['flexible_shipping_import_export'] = $this->prepare_data();

		return $data;
	}

	/**
	 * Prepare data.
	 *
	 * @return array
	 */
	private function prepare_data() {
		$data = array(
			TrackerDataCollector::USED_EXPORT => get_option( TrackerDataCollector::USED_EXPORT, 'no' ),
			TrackerDataCollector::USED_IMPORT => get_option( TrackerDataCollector::USED_IMPORT, 'no' ),
			TrackerDataCollector::COUNT_EXPORT => (int) get_option( TrackerDataCollector::COUNT_EXPORT, 0 ),
			TrackerDataCollector::COUNT_IMPORT => (int) get_option( TrackerDataCollector::COUNT_IMPORT, 0 ),
			TrackerDataCollector::MAX_EXPORT => (int) get_option( TrackerDataCollector::MAX_EXPORT, 0 ),
			TrackerDataCollector::MAX_IMPORT => (int) get_option( TrackerDataCollector::MAX_IMPORT, 0 ),
		);

		return $data;
	}

}
