<?php
/**
 * Class EnableShippingMethods
 *
 * @package WPDesk\FS\ConditionalMethods\Actions
 */

namespace WPDesk\FS\ConditionalMethods\Actions;

use FSConditionalMethodsVendor\WPDesk\Forms\Field;
use FSConditionalMethodsVendor\WPDesk\Forms\Field\WooSelect;
use WC_Shipping_Rate;
use WPDesk\FS\ConditionalMethods\WooCommerceShippingMethods;

/**
 * Enable Shipping Methods action.
 */
class EnableShippingMethods extends AbstractAction {

	/**
	 * EnableShippingMethods constructor.
	 */
	public function __construct() {
		parent::__construct(
			'enable_shipping_methods',
			__( 'Enable only selected shipping methods', 'flexible-shipping-conditional-methods' ),
			__( 'Display only selected shipping methods once the previously defined Condition is met.', 'flexible-shipping-conditional-methods' ),
			__( 'Shipping methods', 'flexible-shipping-conditional-methods' ),
			10
		);
	}

	/**
	 * @return Field[]
	 */
	public function get_fields() {
		// Translators: shipping method.
		$options = ( new WooCommerceShippingMethods( __( 'All &quot;%1$s&quot; methods', 'flexible-shipping-conditional-methods' ) ) )->prepare_shipping_method_options();

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
		$enable_shipping_methods = isset( $action_settings[ $this->get_option_id() ] ) ? $action_settings[ $this->get_option_id() ] : array();
		$enable_shipping_methods = wp_parse_list( $enable_shipping_methods );

		$package['rates'] = array_filter(
			$package['rates'],
			function ( $rate ) use ( $enable_shipping_methods ) {
				return $this->is_allowed_shipping_method( $rate, $enable_shipping_methods );
			}
		);

		return $package;
	}

	/**
	 * @param WC_Shipping_Rate $rate                    .
	 * @param string[]         $enable_shipping_methods .
	 *
	 * @return bool
	 */
	private function is_allowed_shipping_method( $rate, $enable_shipping_methods ) {
		return in_array( $rate->get_id(), $enable_shipping_methods, true ) || in_array( $rate->get_method_id(), $enable_shipping_methods, true );
	}
}
