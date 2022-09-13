<?php
/**
 * Class ShippingMethodsAjaxHandler.
 *
 * @package WPDesk\FS\TableRate\ImportExport
 */

namespace WPDesk\FS\TableRate\ImportExport;

use Exception;
use FlexibleShippingImportExportVendor\WPDesk\PluginBuilder\Plugin\Hookable;
use WC_Shipping_Method;
use WC_Shipping_Zone;
use WPDesk\FS\TableRate\ShippingMethod\CommonMethodSettings;
use WPDesk\FS\TableRate\ShippingMethodSingle;

/**
 * Class Hooks
 */
class ShippingMethodsAjaxHandler implements Hookable {
	const AJAX_ACTION = 'flexible_shipping_get_shipping_methods';

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

		$zone_id = filter_input( INPUT_GET, 'zone_id', FILTER_VALIDATE_INT );

		if ( false === $zone_id ) {
			wp_send_json_error( array( 'message' => __( 'Invalid zone', 'flexible-shipping-import-export' ) ) );
		}

		try {
			$shipping_methods = $this->get_shipping_methods_by_zone_id( $zone_id );

			wp_send_json_success(
				array(
					'zone_shipping_methods' => $shipping_methods,
					'popup_description'     => sprintf(
					// Translators: methods count.
						_n(
							'We\'ve detected %1$s Flexible Shipping method configured in this shipping zone.',
							'We\'ve detected %1$s Flexible Shipping methods configured in this shipping zone.',
							count( $shipping_methods ),
							'flexible-shipping-import-export'
						),
						count( $shipping_methods )
					),
				)
			);
		} catch ( Exception $e ) {
			wp_send_json_error( array( 'message' => $e->getMessage() ) );
		}
	}

	/**
	 * @param int $zone_id .
	 *
	 * @return array
	 * @throws Exception .
	 */
	private function get_shipping_methods_by_zone_id( $zone_id ) {
		$shipping_methods = array();
		$zone             = new WC_Shipping_Zone( absint( $zone_id ) );

		/** @var WC_Shipping_Method $shipping_method */
		foreach ( $zone->get_shipping_methods() as $shipping_method ) {
			if ( ! $shipping_method instanceof ShippingMethodSingle ) {
				continue;
			}

			$count_rules = count( $shipping_method->get_method_rules() );

			$shipping_methods[] = array(
				'instance_id'    => $shipping_method->get_instance_id(),
				'method_title'   => $shipping_method->get_instance_option( CommonMethodSettings::METHOD_TITLE ),
				'rules_count'    => $count_rules,
				'formatted_info' => sprintf(
				// Translators: instance ID, rules count.
					_n(
						'Method ID: %1$s, %2$s rule configured',
						'Method ID: %1$s, %2$s rules configured',
						$count_rules,
						'flexible-shipping-import-export'
					),
					$shipping_method->get_instance_id(),
					$count_rules
				),
			);
		}

		return $shipping_methods;
	}
}
