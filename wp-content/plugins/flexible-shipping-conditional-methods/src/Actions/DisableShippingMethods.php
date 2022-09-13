<?php
/**
 * Class DisableShippingMethods
 *
 * @package WPDesk\FS\ConditionalMethods\Actions
 */

namespace WPDesk\FS\ConditionalMethods\Actions;

use FSConditionalMethodsVendor\WPDesk\Forms\Field;
use FSConditionalMethodsVendor\WPDesk\Forms\Field\WooSelect;
use WPDesk\FS\ConditionalMethods\WooCommerceShippingMethods;

/**
 * Disable Shipping Methods action.
 */
class DisableShippingMethods extends AbstractAction {

	/**
	 * @var WooCommerceShippingMethods
	 */
	private $woocommerce_shipping_methods;

	/**
	 * DisableShippingMethods constructor.
	 *
	 * @param WooCommerceShippingMethods $woocommerce_shipping_methods .
	 */
	public function __construct( WooCommerceShippingMethods $woocommerce_shipping_methods ) {
		parent::__construct(
			'disable_shipping_methods',
			__( 'Disable only selected shipping methods', 'flexible-shipping-conditional-methods' ),
			__( 'Hide only selected shipping methods once the previously defined Condition is met.', 'flexible-shipping-conditional-methods' ),
			__( 'Shipping methods', 'flexible-shipping-conditional-methods' ),
			10
		);
		$this->woocommerce_shipping_methods = $woocommerce_shipping_methods;
	}

	/**
	 * @return Field[]
	 */
	public function get_fields() {
		$options = $this->woocommerce_shipping_methods->prepare_shipping_method_options();

		return array(
			( new WooSelect() )
				->set_name( $this->get_option_id() )
				->set_multiple()
				->set_options( $options )
				->add_class( 'shipping-method' )
				->set_placeholder( __( 'search shipping method', 'flexible-shipping-conditional-methods' ) )
				->set_label( ' ' ),
		);
	}

	/**
	 * @param array<string, string|array> $action_settings .
	 * @param array[]                     $package         .
	 *
	 * @return array[]
	 */
	public function execute_action( array $action_settings, array $package ) {
		$disable_shipping_methods = isset( $action_settings[ $this->get_option_id() ] ) ? $action_settings[ $this->get_option_id() ] : array();
		$disable_shipping_methods = wp_parse_list( $disable_shipping_methods );

		foreach ( $disable_shipping_methods as $shipping_method ) {
			$package = $this->disable_shipping_method( $shipping_method, $package );
		}

		return $package;
	}

	/**
	 * @param string  $shipping_method .
	 * @param array[] $package         .
	 *
	 * @return array[]
	 */
	private function disable_shipping_method( $shipping_method, array $package ) {
		foreach ( $package['rates'] as $rate_key => $rate ) {
			if ( $rate_key === $shipping_method || strpos( $rate_key, $shipping_method . ':' ) === 0 ) {
				unset( $package['rates'][ $rate_key ] );
			}
		}

		return $package;
	}

}
