<?php
/**
 * Class ActionsFactory
 *
 * @package WPDesk\FS\ConditionalMethods\Actions
 */

namespace WPDesk\FS\ConditionalMethods\Actions;

use WPDesk\FS\ConditionalMethods\WooCommerceShippingMethods;

/**
 * Can create actions.
 */
class ActionsFactory {

	/**
	 * @return array<string, AbstractAction>
	 */
	public function create_actions() {
		$disable_shipping_methods   = new DisableShippingMethods(
			// Translators: shipping method.
			new WooCommerceShippingMethods( __( 'All &quot;%1$s&quot; methods', 'flexible-shipping-conditional-methods' ) )
		);
		$enable_shipping_methods    = new EnableShippingMethods();
		$hide_paid_shipping_methods = new HidePaidShippingMethods();

		$actions = array(
			$enable_shipping_methods->get_option_id()    => $enable_shipping_methods,
			$disable_shipping_methods->get_option_id()   => $disable_shipping_methods,
			$hide_paid_shipping_methods->get_option_id() => $hide_paid_shipping_methods,
		);

		return apply_filters( 'flexible-shipping-conditional-shipping/actions', $actions );
	}
}
