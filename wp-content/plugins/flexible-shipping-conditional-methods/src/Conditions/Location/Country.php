<?php
/**
 * Class Country
 *
 * @package WPDesk\FS\ConditionalMethods\Conditions\Location
 */

namespace WPDesk\FS\ConditionalMethods\Conditions\Location;

/**
 * Wrapper for WC_Countries.
 */
class Country {
	/**
	 * @return array<string, string>
	 */
	public function get_allowed_countries() {
		return WC()->countries->get_allowed_countries();
	}
}
