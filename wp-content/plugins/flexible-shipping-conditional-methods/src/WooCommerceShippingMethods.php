<?php
/**
 * Class WooCommerceShippingMethods
 *
 * @package WPDesk\FS\ConditionalMethods
 */

namespace WPDesk\FS\ConditionalMethods;

/**
 * Can load WooCommerce shipping methods.
 *
 * @codeCoverageIgnore
 */
class WooCommerceShippingMethods {

	/**
	 * @var string
	 */
	private $shipping_method_format;

	/**
	 * WooCommerceShippingMethods constructor.
	 *
	 * @param string $shipping_method_format .
	 */
	public function __construct( $shipping_method_format = '' ) {
		// Translators: %1$s shipping method name.
		$this->shipping_method_format = $shipping_method_format ? $shipping_method_format : __( 'Any &quot;%1$s&quot; method', 'flexible-shipping-conditional-methods' );
	}


	/**
	 * @return array[]
	 */
	public function prepare_shipping_method_options() {
		$shipping_method_options = array();
		try {
			$options = $this->load_shipping_method_options();
		} catch ( \Exception $e ) {
			$options = array();
		}

		foreach ( $options as $shipping_method => $shipping_methods ) {
			foreach ( $shipping_methods as $instance_id => $label ) {
				$shipping_method_options[] = array(
					'group' => $shipping_method,
					'value' => $instance_id,
					'label' => $label,
				);
			}
		}

		return $shipping_method_options;
	}


	/**
	 * Method copied from WooCommerce
	 * https://github.com/woocommerce/woocommerce/blob/master/includes/gateways/cod/class-wc-gateway-cod.php
	 *
	 * @return array[]
	 * @throws \Exception .
	 */
	private function load_shipping_method_options() {
		$data_store = \WC_Data_Store::load( 'shipping-zone' );
		$raw_zones  = $data_store->get_zones(); // @phpstan-ignore-line

		foreach ( $raw_zones as $raw_zone ) {
			$zones[] = new \WC_Shipping_Zone( $raw_zone );
		}
		$zones[] = new \WC_Shipping_Zone( 0 );

		$options = array();
		/** @var \WC_Shipping_Method $method */
		foreach ( $this->load_shipping_methods() as $method ) {
			$method_title             = $method->get_method_title();
			$options[ $method_title ] = array();

			$options[ $method_title ][ $method->id ] = sprintf( $this->shipping_method_format, $method_title );

			foreach ( $zones as $zone ) {
				$shipping_method_instances = $zone->get_shipping_methods();
				foreach ( $shipping_method_instances as $shipping_method_instance_id => $shipping_method_instance ) {
					if ( $shipping_method_instance->id !== $method->id ) {
						continue;
					}
					$option_id = $shipping_method_instance->get_rate_id();
					// Translators: %1$s shipping method title, %2$s shipping method id.
					$option_instance_title = sprintf( __( '%1$s (#%2$s)', 'flexible-shipping-conditional-methods' ), $shipping_method_instance->get_title(), $shipping_method_instance_id );
					// Translators: %1$s zone name, %2$s shipping method instance name.
					$option_title                           = sprintf( __( '%1$s &ndash; %2$s', 'flexible-shipping-conditional-methods' ), $zone->get_id() ? $zone->get_zone_name() : __( 'Other locations', 'flexible-shipping-conditional-methods' ), $option_instance_title );
					$options[ $method_title ][ $option_id ] = $option_title;
				}
			}
		}

		return $options;
	}

	/**
	 * @return \WC_Shipping_Method[]
	 */
	private function load_shipping_methods() {
		$shipping_methods = WC()->shipping()->load_shipping_methods();

		unset(
			$shipping_methods['paczkomaty_shipping_method'],
			$shipping_methods['enadawca'],
			$shipping_methods['furgonetka'],
			$shipping_methods['fslocations'],
			$shipping_methods['paczka_w_ruchu'],
			$shipping_methods['flexible_shipping_info'],
			$shipping_methods['flexible_shipping'],
			$shipping_methods['dpd'],
			$shipping_methods['dpd_uk']
		);

		return $shipping_methods;
	}

}
