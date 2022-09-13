<?php
/**
 * Class UsageDataTracker
 *
 * @package WPDesk\FS\ConditionalMethods\Tracker
 */

namespace WPDesk\FS\ConditionalMethods\Tracker;

use FSConditionalMethodsVendor\WPDesk\PluginBuilder\Plugin\Hookable;

/**
 * Tracks data about usages.
 *
 * @codeCoverageIgnore
 */
class UsageDataTracker implements Hookable {

	/** @var string */
	private $plugin_file_name;

	/**
	 * UsageDataTracker constructor.
	 *
	 * @param string $plugin_file_name .
	 */
	public function __construct( $plugin_file_name ) {
		$this->plugin_file_name = $plugin_file_name;
	}

	/**
	 * Hooks.
	 */
	public function hooks() {
		$tracker_factory = new \WPDesk_Tracker_Factory();
		/** @var \WPDesk_Tracker_Interface $tracker */
		$tracker = $tracker_factory->create_tracker( $this->plugin_file_name );

		$tracker->add_data_provider( new DataProvider() );
	}

}
