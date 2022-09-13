<?php
/**
 * Class HidePaidShippingMethods
 *
 * @package WPDesk\FS\ConditionalMethods\Actions
 */

namespace WPDesk\FS\ConditionalMethods\Actions;

/**
 * Hide Paid Shippipping Methods action.
 */
class HidePaidShippingMethods extends AbstractAction {

	/**
	 * HidePaidShippingMethods constructor.
	 */
	public function __construct() {
		parent::__construct(
			'hide_paid_shipping_methods',
			__( 'Hide all paid shipping methods', 'flexible-shipping-conditional-methods' ),
			__( 'Hide every paid shipping method once the previously defined Condition is met.', 'flexible-shipping-conditional-methods' ),
			__( 'Shipping methods', 'flexible-shipping-conditional-methods' ),
			10
		);
	}

	/**
	 * @param array<string, string|array> $action_settings .
	 * @param array[]                     $package         .
	 *
	 * @return array[]
	 */
	public function execute_action( array $action_settings, array $package ) {
		foreach ( $package['rates'] as $rate_key => $rate ) {
			if ( floatval( $rate->get_cost() ) > 0 ) {
				unset( $package['rates'][ $rate_key ] );
			}
		}

		return $package;
	}
}
