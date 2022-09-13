<?php
/**
 * Trait ShippingMethodLabelsTrait
 *
 * @package WPDesk\FS\TableRate\ImportExport\Labels
 */

namespace WPDesk\FS\TableRate\ImportExport\Export\CSV\Labels;

/**
 * Shipping Method Labels
 */
trait ShippingMethodLabelsTrait {
	/**
	 * @return string
	 */
	private function get_label_shipping_method_id() {
		return 'Shipping Method ID';
	}

	/**
	 * @param string $field .
	 *
	 * @return string
	 */
	private function get_label_shipping_method_field( $field ) {
		return sprintf( 'Shipping Method Field: %s', $field );
	}
}
